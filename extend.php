<?php

namespace Liplum\Trends;

use Liplum\Trends\Controller\TrendsRecentController;
use Flarum\Extend;

return [
  (new Extend\Routes('api'))
    ->get(
      '/trends/recent',
      'liplum-trends.recent-trends',
      TrendsRecentController::class
    ),

  new Extend\Locales(__DIR__ . '/locale'),

  (new Extend\Settings())
    ->default('liplum-trends.defaultRecentDays', 7)
    ->default('liplum-trends.defaultLimit', 10)
    ->default('liplum-trends.defaultHotSpotHours', 24),
];
