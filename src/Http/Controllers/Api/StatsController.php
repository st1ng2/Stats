<?php

namespace Flute\Modules\Stats\src\Http\Controllers\Api;

use Flute\Core\Support\AbstractController;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\Stats\src\Exceptions\ServerNotFoundException;
use Flute\Modules\Stats\src\Services\StatsService;

class StatsController extends AbstractController
{
    protected StatsService $stats;

    public function __construct(StatsService $stats)
    {
        $this->stats = $stats;
    }

    public function getData(FluteRequest $request, $sid)
    {
        $page = ($request->get("start", 1) + $request->get('length')) / $request->get('length');
        $draw = (int) $request->get("draw", 1);
        $columns = $request->get("columns", []);
        $search = $request->get("search", []);
        $order = $request->get("order", []);

        $length = (int) $request->get('length') > 100 ? : (int) $request->get('length');

        try {
            $data = $this->stats->getData(
                $page,
                $length,
                $draw,
                $columns,
                $search,
                $order,
                $sid,
            );

            return $this->json($data);
        } catch (ServerNotFoundException $e) {
            return $this->error(__('stats.server_not_found'), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        } 
    }
}