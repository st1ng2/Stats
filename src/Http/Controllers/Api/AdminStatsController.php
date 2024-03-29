<?php

namespace Flute\Modules\Stats\src\Http\Controllers\Api;

use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Support\AbstractController;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\Stats\src\Services\AdminStatsService;
use Symfony\Component\HttpFoundation\Response;

class AdminStatsController extends AbstractController
{
    protected AdminStatsService $adminStatsService;

    public function __construct(AdminStatsService $service)
    {
        $this->adminStatsService = $service;
        HasPermissionMiddleware::permission(['admin', 'admin.servers']);
    }

    public function store(FluteRequest $request): Response
    {
        try {
            $this->validate($request);

            $this->adminStatsService->store(
                $request->mod,
                $request->dbname,
                $request->additional ?? '[]',
                (int) $request->sid
            );

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function delete(FluteRequest $request, $id): Response
    {
        try {
            $this->adminStatsService->delete((int) $id);

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function update(FluteRequest $request, $id): Response
    {
        try {
            $this->validate($request);

            $this->adminStatsService->update(
                (int) $id,
                $request->mod,
                $request->dbname,
                $request->additional ?? '[]',
                (int) $request->sid
            );

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    protected function validate( FluteRequest $request )
    {
        if( empty( $request->input('mod') ) || empty( $request->input('dbname') ) )
            throw new \Exception(__('stats.params_empty'));
    }
}