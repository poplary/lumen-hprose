<?php

namespace Poplary\LumenHprose\Middleware\Contracts;

use Closure;
use stdClass;

/**
 * @see https://github.com/hprose/hprose-php/wiki/12-Hprose-%E4%B8%AD%E9%97%B4%E4%BB%B6
 * Class InvokeHandler.
 */
abstract class InvokeHandler
{
    /**
     * @param mixed    $name
     * @param array    $args
     * @param stdClass $context
     * @param Closure  $next
     *
     * @return mixed
     */
    public function __invoke($name, array &$args, stdClass $context, Closure $next)
    {
        return $this->handle($name, $args, $context, $next);
    }

    abstract public function handle($name, array &$args, stdClass $context, Closure $next);
}
