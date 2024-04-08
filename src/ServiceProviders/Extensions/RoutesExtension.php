<?php

namespace Flute\Modules\Stats\src\ServiceProviders\Extensions;

use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Router\RouteGroup;
use Flute\Modules\Stats\src\Http\Controllers\View\AdminStatsController;
use Flute\Modules\Stats\src\Http\Controllers\View\StatsController;
use Flute\Modules\Stats\src\Http\Controllers\Api\StatsController as Api;
use Flute\Modules\Stats\src\Http\Controllers\Api\AdminStatsController as ApiAdmin;

class RoutesExtension implements \Flute\Core\Contracts\ModuleExtensionInterface
{
    public function register(): void
    {
        router()->group(function (RouteGroup $routeGroup) {
            $routeGroup->get('', [StatsController::class, 'index']);
            $routeGroup->get('/', [StatsController::class, 'index']);

            $routeGroup->get('/get/{sid}', [Api::class, 'getData']);
        }, 'stats');

        router()->group(function (RouteGroup $routeGroup) {
            $routeGroup->middleware(HasPermissionMiddleware::class);

            $routeGroup->group(function (RouteGroup $news) {
                $news->get('list', [AdminStatsController::class, 'list']);
                $news->get('add', [AdminStatsController::class, 'add']);
                $news->get('edit/{id}', [AdminStatsController::class, 'update']);
            }, 'module_stats/');
        
            $routeGroup->group(function (RouteGroup $news) {
                $news->post('add', [ApiAdmin::class, 'store']);
                $news->put('{id}', [ApiAdmin::class, 'update']);
                $news->delete('{id}', [ApiAdmin::class, 'delete']);
            }, 'api/module_stats/');
        }, 'admin/');
    }
}