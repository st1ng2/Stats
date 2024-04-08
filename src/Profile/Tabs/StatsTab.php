<?php

namespace Flute\Modules\Stats\src\Profile\Tabs;

use Flute\Core\Contracts\ProfileTabInterface;
use Flute\Core\Database\Entities\User;
use Flute\Modules\Stats\src\Services\StatsService;

class StatsTab implements ProfileTabInterface
{
    public function render(User $user)
    {
        $statsService = app(StatsService::class);

        return render(mm('Stats', 'Resources/views/profile/stats'), [
            'blocks' => $statsService->getBlocks(request()->input('sid')),
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