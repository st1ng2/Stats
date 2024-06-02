<?php

namespace Flute\Modules\Stats\src\Driver\Items;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Core\Table\TableBuilder;
use Flute\Core\Table\TableColumn;
use Flute\Core\Table\TablePreparation;
use Flute\Modules\Stats\src\Contracts\DriverInterface;

class K4Driver implements DriverInterface
{
    public function getSupportedMods(): array
    {
        return [730];
    }

    public function getBlocks(): array
    {
        return [
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
            'hits_given' => [
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

    public function setColumns(TableBuilder $tableBuilder)
    {
        $tableBuilder->addColumn((new TableColumn('user_url'))->setVisible(false));
        $tableBuilder->addCombinedColumn('avatar', 'name', __('def.user'), 'user_url', true);

        $tableBuilder->addColumns([
            (new TableColumn('rank', __('stats.rank'))),
            (new TableColumn('points', __('stats.score')))->setType('text')->setDefaultOrder(),
            (new TableColumn('kills', __('stats.kills')))->setType('text'),
            (new TableColumn('deaths', __('stats.deaths')))->setType('text'),
            (new TableColumn('lastseen', __('stats.last_active')))->setType('text'),
        ]);
    }

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
                ['user_url', 'avatar', 'name', '', 'rank', 'points', 'kills', 'deaths', 'lastseen'],
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
            $mode = dbmode()->getServerMode($this->getName(), $sid);

            $select = dbal()->database($mode->dbname)
                ->table('stats')
                ->select()
                ->where('steam', 'like', "%" . substr($steam, 10))
                ->fetchAll();

            if (empty($select))
                return [];

            return [
                'server' => $mode->server,
                'stats' => $select[0]
            ];
        } catch (\Exception $e) {
            if (is_debug())
                throw $e;

            return [];
        }
    }

    private function prepareSelectQuery(string $dbname, array $columns, array $search, array $order): \Spiral\Database\Query\SelectQuery
    {
        $select = dbal()->database($dbname)->table("stats")->select()->columns([
            "stats.*",
            'ranks.points as points',
            'ranks.rank as rank',
        ]);

        $select->innerJoin('ranks')->on(["stats.steam_id" => 'ranks.steam_id']);

        foreach ($columns as $column) {
            if ($column['searchable'] == 'true' && $column['search']['value'] != '') {
                $select->where($column['name'], 'like', "%" . $column['search']['value'] . "%");
            }
        }

        if (isset($search['value']) && !empty($search['value'])) {
            $value = $search['value'];

            $select->where(function ($select) use ($value) {
                $select->where('steam_id', $value)
                    ->orWhere('name', 'like', "%" . $value . "%");
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
                $steamId64 = steam()->steamid($result['steam_id'])->ConvertToUInt64();
                $steamIds64[$result['steam_id']] = $steamId64;
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
            $steamId32 = $result['steam_id'];
            if (isset($usersData[$steamId32])) {
                $user = $usersData[$steamId32];
                $result['steam_id'] = $usersData[$steamId32]->steamid;
                $result['avatar'] = $user->avatar;
            }

            $result['lastseen'] = (new \DateTimeImmutable($result['lastseen']))->format(default_date_format());

            $result['user_url'] = url('profile/search/' . $result['steam_id'])->addParams([
                "else-redirect" => "https://steamcommunity.com/profiles/" . $result['steam_id']
            ])->get();

            $mappedResults[] = $result;
        }

        return $mappedResults;
    }

    public function getName(): string
    {
        return "K4";
    }
}