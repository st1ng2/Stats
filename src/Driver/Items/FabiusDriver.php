<?php

namespace Flute\Modules\Stats\src\Driver\Items;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Core\Table\TableColumn;
use Flute\Core\Table\TablePreparation;
use Flute\Modules\Stats\src\Contracts\DriverInterface;

class FabiusDriver implements DriverInterface
{
    protected string $ranks = 'default';
    protected string $table = 'base';

    public function __construct(array $config = [])
    {
        $this->ranks = isset($config['ranks']) ? $config['ranks'] : 'default';
        $this->table = isset($config['table']) ? $config['table'] : 'base';
    }

    public function getSupportedMods(): array
    {
        return [730, 240];
    }

    /**
     * Возвращает столбцы для таблицы.
     * 
     * @return array[TableColumn] Массив столбцов таблицы.
     */
    public function getColumns(): array
    {
        return [
            (new TableColumn('avatar', ''))->image()->setOrderable(false),
            (new TableColumn('steamid'))->setVisible(false),
            (new TableColumn('username', __('stats.name')))->setRender('{{KEY}}', "
                function(data, type, full) {
                    let a = make('a');
                    a.setAttribute('href', 'https://steamcommunity.com/profiles/'+full[1]);
                    a.setAttribute('target', '_blank');
                    a.innerHTML = data;
                    return a;
                }
            "),
            (new TableColumn('experience', __('stats.experience')))->setType('text'),
            (new TableColumn('score', __('stats.score')))->setType('text'),
            (new TableColumn('kills', __('stats.kills')))->setType('text'),
            (new TableColumn('deaths', __('stats.deaths')))->setType('text'),
            (new TableColumn('last_active', __('stats.last_active')))->setType('text'),
        ];
    }
    public function getBlocks(): array
    {
        return [
            'experience' => [
                'text' => 'stats.profile.experience',
                'icon' => 'ph-number-circle-five'
            ],
            'score' => [
                'text' => 'stats.profile.score',
                'icon' => 'ph-number-circle-five'
            ],
            'kills' => [
                'text' => 'stats.profile.kills',
                'icon' => 'ph-smiley-x-eyes'
            ],
            'deaths' => [
                'text' => 'stats.profile.deaths',
                'icon' => 'ph-skull'
            ],
            'shoots' => [
                'text' => 'stats.profile.shoots',
                'icon' => 'ph-fire'
            ],
            'hits' => [
                'text' => 'stats.profile.hits',
                'icon' => 'ph-target'
            ],
            'headshots' => [
                'text' => 'stats.profile.headshots',
                'icon' => 'ph-baby'
            ],
            'assists' => [
                'text' => 'stats.profile.assists',
                'icon' => 'ph-handshake'
            ],
            'round_win' => [
                'text' => 'stats.profile.round_win',
                'icon' => 'ph-trophy'
            ],
            'round_lose' => [
                'text' => 'stats.profile.round_lose',
                'icon' => 'ph-thumbs-down'
            ],
        ];
    }

    /**
     * Получает данные для таблицы.
     * 
     * @param Server $server Сущность сервера.
     * @param string $dbname Имя базы данных.
     * @param int $page Номер страницы.
     * @param int $perPage Количество элементов на странице.
     * @param int $draw Счетчик перерисовки.
     * @param array $columns Столбцы для фильтрации.
     * @param array $search Параметры поиска.
     * @param array $order Параметры сортировки.
     * 
     * @return array Ответ с данными.
     */
    public function getData(
        Server $server,
        string $dbname,
        int $page,
        int $perPage,
        int $draw,
        array $columns = [],
        array $search = [],
        array $order = []
    ): array {
        $select = $this->prepareSelectQuery($dbname, $columns, $search, $order);

        $paginator = new \Spiral\Pagination\Paginator($perPage);
        $paginate = $paginator->withPage($page)->paginate($select);

        $result = $select->fetchAll();

        $steamIds = $this->getSteamIds64($result);
        $usersData = steam()->getUsers($steamIds);

        $result = $this->mapUsersDataToResult($result, $usersData);

        return [
            'draw' => $draw,
            'recordsTotal' => $paginate->count(),
            'recordsFiltered' => $paginate->count(),
            'data' => TablePreparation::normalize(
                ['avatar', 'steamid', 'username', 'experience', 'score', 'kills', 'deaths', 'last_active'],
                $result
            )
        ];
    }

    public function getUserStats(int $sid, User $user): array
    {
        $found = false;

        foreach ($user->socialNetworks as $social) {
            if ($social->socialNetwork->key === "Steam") {
                $found = $social->value;
            }
        }

        if (!$found)
            return [];

        try {
            $mode = dbmode()->getServerMode(self::class, $sid);

            $select = dbal()->database($mode->dbname)
                ->table($this->table)
                ->select()
                ->where('steamid', 'like', "%" . substr(steam()->steamid($found)->RenderSteam2(), 10))
                ->fetchAll();

            if (empty($select))
                return [];

            return [
                'server' => $mode->server,
                'stats' => $select[0]
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function prepareSelectQuery(string $dbname, array $columns, array $search, array $order): \Spiral\Database\Query\SelectQuery
    {
        $select = dbal()->database($dbname)->table($this->table)->select();

        foreach ($columns as $column) {
            if ($column['searchable'] == 'true' && $column['search']['value'] != '') {
                $select->where($column['name'], 'like', "%" . $column['search']['value'] . "%");
            }
        }

        if (isset($search['value']) && !empty($search['value'])) {
            // if (strpos($search['value'], 'STEAM_') !== false)
            $select->where('steamid', $search['value']);
            // else
            $select->where('username', 'like', "%" . $search['value'] . "%");
        }

        foreach ($order as $order) {
            $columnIndex = $order['column'];
            $columnName = $columns[$columnIndex]['name'];
            $direction = $order['dir'] === 'asc' ? 'ASC' : 'DESC';

            if ($columns[$columnIndex]['orderable'] == 'true') {
                $select->orderBy($columnName, $direction);
            }
        }

        return $select;
    }

    private function getSteamIds64(array $results): array
    {
        $steamIds64 = [];

        foreach ($results as $result) {
            try {
                // Преобразование Steam ID в 64-битный формат с помощью Steam API
                $steamId64 = steam()->steamid($result['steamid'])->ConvertToUInt64();
                $steamIds64[$result['steamid']] = $steamId64;
            } catch (\InvalidArgumentException $e) {
                logs()->error($e);

                // Убираем чтобы не мешал.
                unset($result);
            }
        }

        return $steamIds64;
    }

    private function mapUsersDataToResult(array $results, array $usersData): array
    {
        // Сопоставление Steam ID 64-битному формату и данных пользователя
        $mappedResults = [];

        foreach ($results as $result) {
            $steamId32 = $result['steamid'];
            if (isset($usersData[$steamId32])) {
                $user = $usersData[$steamId32];
                $result['steamid'] = $usersData[$steamId32]->steamid;
                $result['avatar'] = $user->avatar;
            }

            $mappedResults[] = $result;
        }

        return $mappedResults;
    }

    protected function getRankAsset(int $rank)
    {
        return template()->getTemplateAssets()->rAssetFunction("Modules/Stats/Resources/assets/ranks/{$this->ranks}/{$rank}.webp");
    }

    /**
     * Return driver name
     */
    public function getName(): string
    {
        return "FABIUS";
    }
}