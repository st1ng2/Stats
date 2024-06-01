<?php

namespace Flute\Modules\Stats\src\ServiceProviders\Extensions;

use Flute\Core\Router\RouteGroup;
use Flute\Modules\Stats\src\Http\Controllers\View\StatsController;
use Flute\Modules\Stats\src\Http\Controllers\Api\StatsController as Api;

class RoutesExtension implements \Flute\Core\Contracts\ModuleExtensionInterface
{
    public function register(): void
    {
        router()->group(function (RouteGroup $routeGroup) {
            $routeGroup->get('', [StatsController::class, 'index']);
            $routeGroup->get('/', [StatsController::class, 'index']);

            $routeGroup->get('/get/{sid}', [Api::class, 'getData']);
        }, 'stats');
    }
}