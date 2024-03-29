<?php

namespace Flute\Modules\Stats\src\ServiceProviders\Extensions;

use Flute\Modules\Stats\src\Profile\Tabs\StatsTab;

class ProfileExtension implements \Flute\Core\Contracts\ModuleExtensionInterface
{
    public function register(): void
    {
        $this->registerProfile();
    }

    private function registerProfile(): void
    {
        profile()->addTab(new StatsTab);
    }
}