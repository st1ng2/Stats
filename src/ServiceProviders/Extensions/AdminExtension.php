<?php

namespace Flute\Modules\Stats\src\ServiceProviders\Extensions;

use Flute\Core\Admin\Builders\AdminSidebarBuilder;

class AdminExtension implements \Flute\Core\Contracts\ModuleExtensionInterface
{
    public function register(): void
    {
        $this->addSidebar();
    }

    private function addSidebar(): void
    {
        AdminSidebarBuilder::add('additional', [
            'title' => 'stats.admin.title',
            'icon' => 'ph-chart-polar',
            'permission' => 'admin.servers',
            'url' => '/admin/module_stats/list'
        ]);
    }
}