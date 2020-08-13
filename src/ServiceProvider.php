<?php

namespace Poplary\LumenHprose;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadCommands();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ('swoole' === config('hprose.server') && !extension_loaded('swoole')) {
            Config::set('hprose.server', 'socket');
        }
    }

    /**
     * 加载命令.
     */
    protected function loadCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\HproseServer::class,
                Commands\HproseClientDemo::class,
            ]);
        }
    }
}
