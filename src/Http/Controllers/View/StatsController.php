<?php

namespace Flute\Modules\Stats\src\Http\Controllers\View;

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

    public function index( FluteRequest $fluteRequest )
    {
        $sid = (int) $fluteRequest->input("sid", null);

        try {
            $data = $this->stats->generateTable($sid);

            return view('Modules/Stats/Resources/views/index', [
                'stats' => $data,
                'servers' => $this->stats->getServerModes()
            ]);
        } catch (ServerNotFoundException $e) {
            return $this->error(__('stats.server_not_found'), 404);
        }
    }
}