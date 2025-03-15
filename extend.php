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
];
