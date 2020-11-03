<?php

namespace Poplary\LumenHprose\Commands;

use Hprose\Filter;
use Poplary\LumenHprose\Facades\Router;
use Poplary\LumenHprose\Middleware\Contracts\AfterFilterHandler;
use Poplary\LumenHprose\Middleware\Contracts\BeforeFilterHandler;
use Poplary\LumenHprose\Middleware\Contracts\InvokeHandler;
use Poplary\LumenHprose\Middleware\ServerLoggerMiddleware;
use Poplary\LumenHprose\Server\ServerLaunch;
use Illuminate\Console\Command;
use Laravel\Lumen\Application;

/**
 * Class HproseSocket.
 */
class HproseServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hprose:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hprose socket server';

    /**
     * Execute the console command.
     *
     * @param Application $app
     *
     * @return mixed
     */
    public function handle(Application $app)
    {
        ServerLaunch::run($app);

        $this->outputInfo();
        $server = app('hprose.server');

        // 服务启动
        $server->start();

        return 0;
    }

    /**
     * 输出基础信息.
     */
    protected function outputInfo(): void
    {
        $this->comment('Service:');
        $this->output->writeln(sprintf(' - <info>%s</info>', config('hprose.service')));
        $this->output->newLine();

        $this->comment('版本:');
        $this->output->writeln(sprintf(' - Laravel/Lumen=<info>%s</info>', app()->version()));
        $this->output->writeln(sprintf(' - Hprose-php=<info>2.0.*</info>'));
        $this->output->newLine();

        $this->comment('启动的服务器类型:');
        $this->line(sprintf(' - <info>%s</info>', config('hprose.server')));
        $this->output->newLine();

        $this->comment('监听:');
        $this->line(sprintf(' - <info>%s</info>', config('hprose.uri')));
        $this->output->newLine();

        $this->comment('加载的中间件:');
        $middlewareClasses = config('hprose.middleware');
        foreach ($middlewareClasses as $middlewareClass) {
            $this->line(sprintf(' - <info>%s</info>', $middlewareClass));
        }
        $this->output->newLine();

        $this->comment('可调用远程方法:');
        $methods = Router::getMethods();
        if ($methods) {
            foreach ($methods as $method) {
                $this->line(sprintf(' - <info>%s</info>', $method));
            }
            $this->output->newLine();
        } else {
            $this->line(sprintf(' - <info>无可调用方法</info>'));
        }
    }
}
