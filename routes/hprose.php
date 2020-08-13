<?php

/**
 * 添加的服务函数，输入和返回的值都需要为简单数据。
 * 简单数据是指：null、数字（包括整数、浮点数）、Boolean 值、字符串、日期时间等基本类型的数据或者不包含引用的数组和对象。
 * 简单的讲，用 JSON 可以表示的数据都是简单数据。
 */

use Poplary\LumenHprose\Facades\Router;

Router::add('getServiceName', 'Poplary\LumenHprose\Controllers\DemoController@getServiceName');
