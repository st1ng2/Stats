<?php

namespace Flute\Modules\Stats\src\Profile\Tabs;

use Flute\Core\Contracts\ProfileTabInterface;
use Flute\Core\Database\Entities\User;
use Flute\Modules\Stats\src\Services\StatsService;

class StatsTab implements ProfileTabInterface
{
    protected array $blocks = [
        'value' => [
            'text' => 'stats.profile.value',
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
        // 'playtime' => [
        //     'text' => 'stats.profile.playtime',
        //     'icon' => 'ph-clock'
        // ],
        // 'lastconnect' => [
        //     'text' => 'stats.profile.lastconnect',
        //     'icon' => 'ph-calendar'
        // ],
    ];

    public function render(User $user)
    {
        $statsService = app(StatsService::class);

        profile()->disableMainInfo();

        return render(mm('Stats', 'Resources/views/profile/stats'), [
            'blocks' => $this->blocks,
            'stats' => $this->getStats($user, $statsService),
            'user' => $user,
            'servers' => $statsService->getServerModes()
        ]);
    }

    protected function getStats(User $user, $statsService)
    {
        $result = [];
        $sid = request()->input('sid');

        try {
            $result = $statsService->getUserStats($user, $sid);
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    public function getSidebarInfo()
    {
        return [
            'icon' => 'ph ph-chart-bar',
            'name' => 'stats.profile.head',
        ];
    }

    public function getKey()
    {
        return 'stats';
    }
}