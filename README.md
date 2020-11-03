# Lumen-hprose

## 安装

直接使用
```shell
composer require poplary/lumen-hprose
```

## 使用**lumen**配置
1. 在 bootstrap/app.php 注册 ServiceProvider
    ```php
    $app->register(\Poplary\LumenHprose\ServiceProvider::class);
    ```
    
2. 配置.env文件

    ```
    // 服务的名称
    HPROSE_SERVICE=product
    
    // 是否开启 debug
    HPROSE_DEBUG=true
    
    // Hprose 服务启用方式，可选 socket 和 swoole，选择 swoole 时需要安装 swoole 扩展
    HPROSE_SERVER=socket
    
    // 监听的 TCP 端口
    HPROSE_URI=tcp://0.0.0.0:8889
    ```

    

3. 创建`配置`和`路由`文件：
    ```shell
    cp ./vendor/poplary/lumen-hprose/config/hprose.php ./config/hprose.php
    cp ./vendor/poplary/lumen-hprose/routes/hprose.php ./routes/hprose.php
    ```

## 使用

### 路由
路由文件

```
routes/hprose.php
```

添加路由方法
```php
use Poplary\LumenHprose\Facades\Router;

Router::add(string $name, string|callable $action, array $options = []);
```
- string $name 可供客户端远程调用的方法名
- string|callable $action 类方法，格式：App\Controllers\User@update
- array $options 是一个关联数组，它里面包含了一些对该服务函数的特殊设置，详情请参考hprose-php官方文档介绍 [链接](https://github.com/hprose/hprose-php/wiki/06-Hprose-%E6%9C%8D%E5%8A%A1%E5%99%A8#addfunction-%E6%96%B9%E6%B3%95)

发布远程调用方法 `getUserByName` 和 `update`
```php
Router::add('getServiceName', 'Poplary\LumenHprose\Controllers\DemoController@getServiceName');
```

控制器
```php
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
```

客户端调用 客户端可以只安装 Hprose
```php
$client = new \Hprose\Socket\Client('tcp://127.0.0.1:8889', false);
echo $client->getServiceName();
```

路由组
```php
Router::group(array $attributes, callable $callback);
```
- array $attributes 属性 ['namespace' => '', 'prefix' => '']
- callable $callback 回调函数

```php
Router::group(['namespace' => 'Poplary\LumenHprose\Controllers'], function ($route) {    
    $route->add('getServiceName', 'DemoController@getServiceName');
});
```
客户端调用
```php
echo $client->getServiceName();
```

前缀
```php
Router::group(['namespace' => 'Poplary\LumenHprose\Controllers', 'prefix' => 'demo'], function ($route) {
    $route->add('getServiceName', 'DemoController@getServiceName');
});
```
客户端调用
```php
echo $client->demo->getServiceName();
// 或者
echo $client->demo_getServiceName();
```

如果服务端出现 Exception ，因为 Hprose 没有返回 code，需要将code 合并到message用json方式包裹返回
```php
try{
    $client->user->getServiceName();
}catch(\Exception $e){
    $info = json_decode($e->getMessage(),true);
    $message = $info['message'];
    $code = $info['code'];
}

```

### 中间件

中间件分为四种，`Filter`，`BeforeFilterHandler`，`AfterFilterHandler`，`InvokeHandler`.

* `Filter` 的使用可以查看 [11 Hprose 过滤器](https://github.com/hprose/hprose-php/wiki/11-Hprose-%E8%BF%87%E6%BB%A4%E5%99%A8)。

* `BeforeFilterHandler`，`AfterFilterHandler`，`InvokeHandler` 有对应的接口，存放在 `./src/Middleware/Contracts` 目录中，新建中间件时，需要实现对应的接口。相关的使用可以查看 [12 Hprose 中间件](https://github.com/hprose/hprose-php/wiki/12-Hprose-%E4%B8%AD%E9%97%B4%E4%BB%B6)。
* 中间件需要注意加载的顺序，具体配置在 `./config/hprose.php` 的 `middleware` 数组中。

### 启动服务

```shell
php artisan hprose:server
```
**更新了路由后需要重新启动服务**

### Demo 测试

```shell
php artisan hprose:client:demo
```

