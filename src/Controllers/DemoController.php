<?php

namespace Poplary\LumenHprose\Controllers;

/**
 * Class DemoController.
 */
class DemoController
{
    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return config('hprose.service');
    }
}
