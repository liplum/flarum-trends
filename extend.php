<?php

namespace Liplum\Trends;

use Liplum\Trends\Controller\TrendsRecentController;
use Flarum\Extend;

return [
  (new Extend\Frontend('admin'))
    ->js(__DIR__ . '/js/dist/admin.js'),

  (new Extend\Routes('api'))
    ->get(
      '/trends/recent',
      'liplum-trends.recent-trends',
      TrendsRecentController::class
    ),

  new Extend\Locales(__DIR__ . '/locale'),

  (new Extend\Settings())
    ->default('liplum-trends.defaultLimit', 10)
    ->default('liplum-trends.commentWeight', 1.0)
    ->default('liplum-trends.participantWeight', 0.8)
    ->default('liplum-trends.viewWeight', 0.5)
    ->default('liplum-trends.decayLambda', 0.001)
    ,
];
