<?php

namespace Poplary\LumenHprose\Middleware;

use Closure;
use stdClass;

abstract class Middleware
{
    /**
     * @param          $name
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
