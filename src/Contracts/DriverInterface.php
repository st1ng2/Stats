<?php

namespace Flute\Modules\Stats\src\Contracts;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Core\Table\TableColumn;

interface DriverInterface
{
    /**
     * @var array[TableColumn]
     */
    public function getColumns(): array;
    public function getSupportedMods() : array;

    public function getData(
        Server $server,
        string $dbname,
        int $page,
        int $perPage,
        int $draw,
        array $columuns = [],
        array $search = [],
        array $order = []
    ): array;

    /**
     * Return driver name
     */
    public function getName(): string;
    public function getUserStats( int $sid, User $user ) : array;
}