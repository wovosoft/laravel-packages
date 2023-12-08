<?php

namespace Wovosoft\LaravelLinuxDevEnv\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelLinuxDevEnv extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-linux-dev-env';
    }
}
