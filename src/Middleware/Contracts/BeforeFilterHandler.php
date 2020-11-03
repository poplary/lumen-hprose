<?php

declare(strict_types=1);

namespace Poplary\LumenHprose\Middleware\Contracts;

use Closure;
use stdClass;

/**
 * @see https://github.com/hprose/hprose-php/wiki/12-Hprose-%E4%B8%AD%E9%97%B4%E4%BB%B6
 * Class BeforeFilterHandler.
 */
abstract class BeforeFilterHandler
{
    /**
     * @param mixed    $request
     * @param stdClass $context
     * @param Closure  $next
     *
     * @return mixed
     */
    public function __invoke($request, stdClass $context, Closure $next)
    {
        return $this->handle($request, $context, $next);
    }

    abstract public function handle($request, stdClass $context, Closure $next);
}
