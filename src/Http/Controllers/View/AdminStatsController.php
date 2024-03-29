<?php

namespace Flute\Modules\Stats\src\Http\Controllers\View;

use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Database\Entities\DatabaseConnection;
use Flute\Core\Database\Entities\Server;
use Flute\Core\Support\AbstractController;
use Flute\Core\Table\TableColumn;
use Flute\Modules\Stats\src\Driver\DriverFactory;
use Flute\Modules\Stats\src\Services\AdminStatsService;
use Spiral\Database\Injection\Parameter;
use Symfony\Component\HttpFoundation\Response;

class AdminStatsController extends AbstractController
{
    protected $driverFactory;
    protected $adminStatsService;

    public function __construct(DriverFactory $driverFactory, AdminStatsService $adminStatsService)
    {
        $this->driverFactory = $driverFactory;
        $this->adminStatsService = $adminStatsService;
        HasPermissionMiddleware::permission(['admin', 'admin.servers']);
    }

    public function list(): Response
    {
        $table = table();

        $result = rep(DatabaseConnection::class)->select();

        foreach ($this->driverFactory->getAllDrivers() as $key => $driver) {
            $result->where('mod', $key);
        }

        $result = $result->fetchAll();

        foreach ($result as $key => $row) {
            $result[$key]->mod = basename($row->mod);
            $result[$key]->server = ($result[$key]->server->id . ' - ' . $result[$key]->server->name);
        }

        $table->addColumns([
            (new TableColumn('id'))->setVisible(false),
            (new TableColumn('mod', 'Driver')),
            (new TableColumn('dbname', __('stats.admin.dbname'))),
            (new TableColumn('server', __('stats.admin.server'))),
        ])->withActions('module_stats');

        $table->setData($result);

        return view('Modules/Stats/Resources/views/admin/list', [
            'table' => $table->render()
        ]);
    }

    public function update($id): Response
    {
        try {
            $connection = $this->adminStatsService->find($id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }

        $drivers = $this->getDrivers();

        return view('Modules/Stats/Resources/views/admin/edit', [
            'connection' => $connection,
            'drivers' => $drivers,
            'servers' => $this->getServers()
        ]);
    }

    public function add(): Response
    {
        $drivers = $this->getDrivers();

        return view('Modules/Stats/Resources/views/admin/add', [
            'drivers' => $drivers,
            'servers' => $this->getServers()
        ]);
    }

    protected function getDrivers(): array
    {
        return $this->driverFactory->getAllDrivers();
    }

    protected function getServers(): array
    {
        $servers = rep(Server::class)->select();
        $drivers = rep(DatabaseConnection::class)->select();

        foreach ($this->getDrivers() as $key => $driver) {
            $drivers->where('mod', $key);
        }

        $drivers = $drivers->fetchAll();

        foreach ($drivers as $key => $driver) {
            if ($getDriver = $this->driverFactory->createDriver($driver->mod)) {

                if (!empty($getDriver->getSupportedMods())) {
                    $servers->where('mod', 'IN', new Parameter($getDriver->getSupportedMods()));
                }

            } else {
                unset($drivers[$key]);
            }

            $servers->where('id', '!=', $driver->server->id);
        }

        return $servers->fetchAll();
    }
}