<?php

namespace Flute\Modules\Stats;

class Installer extends \Flute\Core\Support\AbstractModuleInstaller
{
    public function install(\Flute\Core\Modules\ModuleInformation &$module) : bool
    {
        return true;
    }

    public function uninstall(\Flute\Core\Modules\ModuleInformation &$module) : bool
    {
        return true;
    }
}