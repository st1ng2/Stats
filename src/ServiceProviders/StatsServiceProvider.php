<?php

namespace Flute\Modules\Stats\src\ServiceProviders;

use Flute\Core\Support\ModuleServiceProvider;
use Flute\Modules\Stats\src\ServiceProviders\Extensions\AdminExtension;
use Flute\Modules\Stats\src\ServiceProviders\Extensions\LoadDriversExtension;
use Flute\Modules\Stats\src\ServiceProviders\Extensions\ProfileExtension;
use Flute\Modules\Stats\src\ServiceProviders\Extensions\RoutesExtension;

class StatsServiceProvider extends ModuleServiceProvider
{
    public array $extensions = [
        LoadDriversExtension::class,
        RoutesExtension::class,
        AdminExtension::class,
        ProfileExtension::class
    ];

    public function boot(\DI\Container $container): void
    {
        // $this->loadEntities();
        $this->loadTranslations();
    }

    public function register(\DI\Container $container): void
    {
    }
}