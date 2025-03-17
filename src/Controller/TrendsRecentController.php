<?php

namespace Liplum\Trends\Controller;

use Carbon\Carbon;
use Flarum\Discussion\DiscussionRepository;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Arr;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;

/**
 * Controller to retrieve trending discussions based on a weighted algorithm.
 *
 * Trending Score Formula:
 *
 * Trending Score = (weight_comment * comment_count) + (weight_participant * participant_count) + (weight_view * view_count) - (time_decay_func)
 *
 * time_decay_func: e^(-lambda * time_difference_in_seconds)
 *
 * Where:
 * - weight_comment: Weight assigned to comment count.
 * - comment_count: Number of comments in the discussion.
 * - weight_participant: Weight assigned to participant count.
 * - participant_count: Number of participants in the discussion.
 * - weight_view: Weight assigned to view count.
 * - view_count: Number of views of the discussion.
 * - lambda: Decay factor that controls the rate of time decay.
 * - time_difference_in_seconds: Time difference between the current time and the created_at time.
 *
 * The trending score is calculated for each discussion, and discussions are then sorted in descending order based on their scores.
 */
class TrendsRecentController implements RequestHandlerInterface
{
  private $settings;
  /**
   * @var DiscussionRepository
   */
  protected $discussions;
  /**
   * @var UrlGenerator
   */
  protected $url;

  /**
   * @param DiscussionRepository $discussions
   */
  public function __construct(
    SettingsRepositoryInterface $settings,
    DiscussionRepository $discussions,
    UrlGenerator $url,
  ) {
    $this->settings = $settings;
    $this->discussions = $discussions;
    $this->url = $url;
  }

  /**
   * Handles the request to retrieve trending discussions.
   *
   * @param ServerRequestInterface $request
   * @return ResponseInterface
   */
  public function handle(ServerRequestInterface $request): ResponseInterface
  {
    // Parse query parameters with default values
    $queryParams = $request->getQueryParams();
    $discussionLimit = $this->getFilteredParam(
      $queryParams,
      'limit',
      $this->getSettings("liplum-trends.defaultLimit", 10),
    );

    // Define weights and decay factor
    $commentWeight = $this->getSettings("liplum-trends.commentWeight", 1.0);
    $participantWeight = $this->getSettings("liplum-trends.participantWeight", 0.8);
    $viewWeight = $this->getSettings("liplum-trends.viewWeight", 0.5);
    $decayLambda = $this->getSettings("liplum-trends.decayLambda", 0.001);

    // Calculate time decay
    $now = Carbon::now();

    $discussions = $this->discussions->query()
      ->whereNull('hidden_at')
      ->where('is_private', 0)
      ->where('is_locked', 0)
      ->selectRaw(
        '*, (? * comment_count) + (? * participant_count) + (? * view_count) - (EXP(-? * TIMESTAMPDIFF(SECOND, created_at, ?))) as trending_score',
        [$commentWeight, $participantWeight, $viewWeight, $decayLambda, $now]
      )
      ->orderByDesc('trending_score')
      ->take($discussionLimit)
      ->get();

    $data = [
      'data' => []
    ];
    foreach ($discussions as $discussion) {
      $lastActivity = $discussion->last_posted_at ?? $discussion->created_at;
      $data['data'][] = [
        'type' => 'discussions',
        'id' => (string) $discussion->id,
        'attributes' => [
          'title' => $discussion->title,
          'commentCount' => $discussion->comment_count,
          'participantCount' => $discussion->participant_count,
          'viewCount' => $discussion->view_count,
          'createdAt' => $discussion->created_at->toIso8601String(),
          'lastActivityAt' => $lastActivity->toIso8601String(),
          'shareUrl' => $this->url->to('forum')->route('discussion', ['id' => $discussion->id]),
          'trendingScore' => $discussion->trending_score,
        ],
        'relationships' => [
          'user' => [
            'data' => [
              'type' => 'users',
              'id' => (string) $discussion->user->id,
              'attributes' => [
                'username' => $discussion->user->username
              ]
            ]
          ]
        ]
      ];
    }

    $response = new Response(
      200,
      ['Content-Type' => 'application/json'],
      json_encode($data, JSON_UNESCAPED_UNICODE),
    );
    return $response;
  }

  private function getSettings(string $key, $default = null)
  {
    return $this->settings->get($key, $default);
  }

  private function getFilteredParam(array $queryParams, string $key, $default)
  {
    return filter_var(
      Arr::get($queryParams, $key, $default),
      FILTER_VALIDATE_INT,
      ['options' => ['default' => $default]]
    );
  }
}
