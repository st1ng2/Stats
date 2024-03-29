<?php

namespace Flute\Modules\Stats\src\ServiceProviders\Extensions;

use Flute\Core\Contracts\ModuleExtensionInterface;
use Flute\Modules\Stats\src\Driver\DriverFactory;
use Flute\Modules\Stats\src\Services\StatsService;

class LoadDriversExtension implements ModuleExtensionInterface
{
    public function register() : void
    {
        app()->getContainer()->set(DriverFactory::class, new DriverFactory);

        app()->getContainer()->get(StatsService::class);
    }
}