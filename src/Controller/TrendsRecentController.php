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
    $hoursLimit = max($this->getSettings("liplum-trends.daysLimit", 30) * 24, 1);

    // Calculate time decay
    $now = Carbon::now();
    $hoursLimitThreshold = Carbon::now()->subHours($hoursLimit);

    $discussions = $this->discussions->query()
      ->whereNull('hidden_at')
      ->where('is_private', 0)
      ->where('is_locked', 0)
      ->where('created_at', '>=', $hoursLimitThreshold)
      ->selectRaw(
        '*, (? * comment_count) + (? * participant_count) + (? * view_count) * POW(1 - (TIMESTAMPDIFF(HOUR, created_at, ?) / ?), 2) as trending_score',
        [$commentWeight, $participantWeight, $viewWeight, $now, $hoursLimit]
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
