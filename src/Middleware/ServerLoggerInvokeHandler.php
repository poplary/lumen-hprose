<?php

namespace Poplary\LumenHprose\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Poplary\LumenHprose\Middleware\Contracts\InvokeHandler;
use stdClass;

/**
 * Class ServerLoggerInvokeHandler.
 */
final class ServerLoggerInvokeHandler extends InvokeHandler
{
    /**
     * 服务端的调用日志记录.
     *
     * @param          $name
     * @param array    $args
     * @param stdClass $context
     * @param Closure  $next
     *
     * @return mixed
     */
    public function handle($name, array &$args, stdClass $context, Closure $next)
    {
        $server = app('hprose.server');

        $beginTime = microtime(true);
        if ($server->debug) {
            Log::channel('stderr')->debug(
                sprintf(
                    '[%s] (%s) 调用开始, 传入参数: %s.',
                    config('hprose.service'),
                    $name,
                    json_encode($args)
                )
            );
        }

        $result = $next($name, $args, $context, $next);

        $endTime = microtime(true);
        if ($server->debug) {
            Log::channel('stderr')->debug(
                sprintf(
                    '[%s] (%s) 调用结束, 耗时: %s.',
                    config('hprose.service'),
                    $name,
                    round($endTime - $beginTime, 6)
                )
            );
        }

        return $result;
    }
}
