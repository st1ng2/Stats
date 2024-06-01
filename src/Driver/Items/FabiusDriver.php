<?php

namespace Flute\Modules\Stats\src\Driver\Items;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Core\Table\TableBuilder;
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

    public function setColumns(TableBuilder $tableBuilder)
    {
        $tableBuilder->addColumn((new TableColumn('user_url'))->setVisible(false));
        $tableBuilder->addCombinedColumn('avatar', 'username', __('def.user'), 'user_url', true);

        $tableBuilder->addColumns([
            (new TableColumn('experience', __('stats.experience')))->setType('text')->setDefaultOrder(),
            (new TableColumn('score', __('stats.score')))->setType('text'),
            (new TableColumn('kills', __('stats.kills')))->setType('text'),
            (new TableColumn('deaths', __('stats.deaths')))->setType('text'),
            (new TableColumn('kdr', 'KDR'))->setType('text')->setRender('{{KDR}}', "
                function(data, type, full) {
                    let div = make('div');
                    div.innerHTML = Number(data).toFixed(2);
                    return div;
                }
            "),
            (new TableColumn('last_active', __('stats.last_active')))->setType('text'),
        ]);
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
            'assists' => [
                'text' => 'stats.profile.assists',
                'icon' => 'ph-handshake'
            ],
            'deaths' => [
                'text' => 'stats.profile.deaths',
                'icon' => 'ph-skull'
            ],
            'damage' => [
                'text' => 'stats.profile.shoots',
                'icon' => 'ph-fire'
            ],
            'headshot_kills' => [
                'text' => 'stats.profile.headshots',
                'icon' => 'ph-baby'
            ],
            'mvp' => [
                'text' => 'stats.profile.headshots',
                'icon' => 'ph-baby'
            ],
            'kdr' => [
                'text' => 'KDR',
                'icon' => 'ph-baby'
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
                ['user_url', 'avatar', 'steamid', 'username', 'experience', 'score', 'kills', 'deaths', 'kdr', 'last_active'],
                $result
            )
        ];
    }

    public function getUserStats(int $sid, User $user): array
    {
        $steam = $user->getSocialNetwork('Steam') ?? $user->getSocialNetwork('HttpsSteam');

        if (!$steam)
            return [];

        $steam = steam()->steamid($steam->value)->RenderSteam2();

        try {
            $mode = dbmode()->getServerMode(self::class, $sid);

            $select = dbal()->database($mode->dbname)
                ->table($this->table)
                ->select()
                ->where('steamid', 'like', "%" . substr($steam, 10))
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
            $value = $search['value'];

            $select->where(function ($select) use ($value) {
                $select->where('steamid', $value)->orWhere('username', 'like', "%" . $value . "%");
            });
        }

        foreach ($order as $v) {
            $columnIndex = $v['column'];
            $columnName = $columns[$columnIndex]['name'];
            $direction = $v['dir'] === 'asc' ? 'ASC' : 'DESC';

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

            $result['user_url'] = url('profile/search/'.$result['steam'])->addParams([
                "else-redirect" => "https://steamcommunity.com/profiles/".$result['steam']
            ])->get();

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
        return "FabiusRanks";
    }
}