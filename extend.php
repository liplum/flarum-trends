<?php

namespace Liplum\Trends;

use Flarum\Extend;
use Liplum\Trends\Controller\TrendsTodayController;

return [
  (new Extend\Routes('api'))
    ->get(
      '/trends/recent',
      'liplum-trends.recent-trends',
      TrendsTodayController::class
    ),
];
