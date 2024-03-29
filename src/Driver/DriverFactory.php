<?php

namespace Flute\Modules\Stats\src\Driver;

use Flute\Modules\Stats\src\Contracts\DriverInterface;

class DriverFactory
{
    /**
     * @var array
     */
    private $registeredDrivers = [];

    /**
     * Register a driver with the factory.
     *
     * @param string $name The name of the driver.
     * @param string $class The class of the driver.
     * 
     * @return void
     */
    public function registerDriver(string $name, string $class): void
    {
        if (!is_subclass_of($class, DriverInterface::class)) {
            throw new \InvalidArgumentException("Class {$class} must implement DriverInterface");
        }

        $this->registeredDrivers[$name] = $class;
    }

    /**
     * Create an instance of a driver.
     *
     * @param string $name The name of the driver.
     * @param array $additional Some additional params
     * 
     * @return DriverInterface
     */
    public function createDriver(string $name, array $additional = []): DriverInterface
    {
        if (!isset($this->registeredDrivers[$name])) {
            throw new \InvalidArgumentException("Driver {$name} is not registered");
        }

        $class = $this->registeredDrivers[$name];
        return new $class($additional);
    }

    /**
     * Get all registered drivers
     * 
     * @return array
     */
    public function getAllDrivers(): array
    {
        return $this->registeredDrivers;
    }
}
