<?php

namespace Poplary\LumenHprose\Commands;

use Hprose\Socket\Client;
use Illuminate\Console\Command;
use Throwable;

/**
 * Class HproseClientDemo.
 */
class HproseClientDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hprose:client:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hprose heartbeat';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $uri = config('hprose.uri');

            $this->comment('连接测试');

            $this->line(sprintf(' - 预计返回内容 <comment>%s</comment>.', config('hprose.service')));
            $this->output->newLine();

            $this->comment(sprintf('连接服务 <info>%s</info>:', $uri));
            $productClient = Client::create($uri, false);

            $this->line(' - 调用 <info>getServiceName</info> 方法:');
            $result = $productClient->getServiceName();

            $this->line(sprintf(' - 返回内容: <comment>%s</comment>.', $result));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
        }

        $this->output->newLine();

        return 0;
    }
}
