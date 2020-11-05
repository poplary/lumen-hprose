<?php

namespace Poplary\LumenHprose\Server;

use Hprose\Filter;
use Poplary\LumenHprose\Middleware\Contracts\AfterFilterHandler;
use Poplary\LumenHprose\Middleware\Contracts\BeforeFilterHandler;
use Poplary\LumenHprose\Middleware\Contracts\InvokeHandler;
use Poplary\LumenHprose\Routing\Router;
use Laravel\Lumen\Application;
use RuntimeException;
use stdClass;

/**
 * Class ServerLaunch.
 */
class ServerLaunch
{
    /**
     * 提供的 server 类型.
     *
     * @var array
     */
    private const ALLOW_SERVERS = [
        'socket',
        'swoole',
    ];

    /**
     * @param Application $app
     */
    public static function run(Application $app): void
    {
        self::registerServer($app);
        self::registerRouter($app);
        self::loadRoutes();
    }

    /**
     * 注册服务的单例.
     *
     * @param Application $app
     */
    private static function registerServer(Application $app): void
    {
        $app->singleton('hprose.server', function (Application $app) {
            $uri = config('hprose.uri');
            $hproseServer = config('hprose.server');

            if (!in_array($hproseServer, self::ALLOW_SERVERS, true)) {
                throw new RuntimeException('HPROSE_SERVER 设置错误，只能为 socket 或者 swoole.');
            }

            // 没有 swoole 扩展时，Server 为 socket
            if ('swoole' === $hproseServer && !extension_loaded('swoole')) {
                $hproseServer = 'socket';
            }

            // 实例化 Server
            if ('socket' === $hproseServer) {
                $server = new \Hprose\Socket\Server($uri);
            } elseif ('swoole' === $hproseServer) {
                $server = new \Hprose\Swoole\Socket\Server($uri);
            } else {
                throw new RuntimeException('HPROSE_SERVER 设置错误，只能为 socket 或者 swoole.');
            }

            // 加载中间件
            $middlewareClasses = config('hprose.middleware');
            foreach ($middlewareClasses as $middlewareClass) {
                $middleware = new $middlewareClass();

                /**
                 * 过滤器和中间件的使用.
                 * @see https://github.com/hprose/hprose-php/wiki/11-Hprose-%E8%BF%87%E6%BB%A4%E5%99%A8
                 * @see https://github.com/hprose/hprose-php/wiki/12-Hprose-%E4%B8%AD%E9%97%B4%E4%BB%B6
                 */
                if ($middleware instanceof BeforeFilterHandler) {
                    $server->addBeforeFilterHandler($middleware);
                }

                if ($middleware instanceof Filter) {
                    $server->addFilter($middleware);
                }

                if ($middleware instanceof AfterFilterHandler) {
                    $server->addAfterFilterHandler($middleware);
                }

                if ($middleware instanceof InvokeHandler) {
                    $server->addInvokeHandler($middleware);
                }
            }

            // 错误处理
            $server->onSendError = function ($error, stdClass $context) {
                $message = json_encode(['message' => $error->getMessage(), 'code' => $error->getCode()]);
                throw new RuntimeException($message, $error->getCode());
            };

            // 是否开启 debug
            $server->debug = config('hprose.debug');

            return $server;
        });
    }

    /**
     * 注册路由的单例.
     *
     * @param Application $app
     */
    private static function registerRouter(Application $app): void
    {
        $app->singleton('hprose.router', function (Application $app) {
            return new Router();
        });
    }

    /**
     * 加载路由文件.
     */
    private static function loadRoutes(): void
    {
        $routeFilePath = base_path('routes/hprose.php');

        if (file_exists($routeFilePath)) {
            require $routeFilePath;
        }
    }
}
