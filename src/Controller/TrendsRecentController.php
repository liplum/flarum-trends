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

/**
 * Controller to retrieve trending discussions based on recent activity.
 *
 * This controller fetches discussions created within a specified recent timeframe,
 * with a higher weight given to discussions that have received activity within a
 * recent hot spot timeframe.
 */
class TrendsRecentController implements RequestHandlerInterface
{
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
    DiscussionRepository $discussions,
    UrlGenerator $url,
  ) {
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
    $recentDays = Arr::get($queryParams, 'recentDays', 7);
    $discussionLimit = Arr::get($queryParams, 'limit', 10);
    $hotSpotHours = Arr::get($queryParams, 'hotSpotHours', 24);

    // Calculate time thresholds
    $recentThreshold = Carbon::now()->subDays($recentDays);
    $hotSpotThreshold = Carbon::now()->subHours($hotSpotHours);

    // Query trending discussions
    $discussions = $this->discussions->query()
      ->whereNull('hidden_at')
      ->where('is_private', 0)
      ->where('is_locked', 0)
      ->where('created_at', '>=', $recentThreshold)
      ->orderByRaw('CASE WHEN last_posted_at >= ? THEN comment_count * 2 ELSE comment_count END DESC', [$hotSpotThreshold])
      ->take($discussionLimit)
      ->get();

    $data = [
      'data' => []
    ];
    foreach ($discussions as $discussion) {
      // Use created_at if last_posted_at is null
      $lastActivity = $discussion->last_posted_at ?? $discussion->created_at;
      $data['data'][] = [
        'type' => 'discussions',
        'id' => (string) $discussion->id,
        'attributes' => [
          'title' => $discussion->title,
          'commentCount' => $discussion->comment_count,
          'createdAt' => $discussion->created_at->toIso8601String(),
          'lastActivityAt' => $lastActivity->toIso8601String(),
          'shareUrl' => $this->url->to('forum')->route('discussion', ['id' => $discussion->id]),
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
}
