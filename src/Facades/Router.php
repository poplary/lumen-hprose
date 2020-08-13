<?php

namespace Poplary\LumenHprose\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Router.
 *
 * @method static void group(array $attributes, callable $callback)
 * @method static void add(string $name, $action, array $options = [])
 * @method static array getMethods()
 */
class Router extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hprose.router';
    }
}
