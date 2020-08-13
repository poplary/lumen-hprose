<?php

namespace Poplary\LumenHprose\Routing;

use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;

/**
 * Class Router.
 */
class Router
{
    /**
     * @var array
     */
    protected $groupStack = [];

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * 创建一组方法.
     *
     * @param array    $attributes
     * @param callable $callback
     */
    public function group(array $attributes, callable $callback): void
    {
        $attributes = $this->mergeLastGroupAttributes($attributes);

        if ((!isset($attributes['prefix']) || empty($attributes['prefix'])) && isset($this->prefix)) {
            $attributes['prefix'] = $this->prefix;
        }

        $this->groupStack[] = $attributes;

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * 添加方法.
     *
     * @param string          $name
     * @param string|callable $action
     * @param array           $options
     *                                 是一个关联数组，它里面包含了一些对该服务函数的特殊设置，详情参考hprose-php文档介绍
     *                                 https://github.com/hprose/hprose-php/wiki/06-Hprose-%E6%9C%8D%E5%8A%A1%E5%99%A8#addfunction-%E6%96%B9%E6%B3%95
     *
     * @throws ReflectionException
     */
    public function add(string $name, $action, array $options = []): void
    {
        if (is_string($action)) {
            $action = ['controller' => $action, 'type' => 'method'];
        } elseif (is_callable($action)) {
            $action = ['callable' => $action, 'type' => 'callable'];
        }

        $action = $this->mergeLastGroupAttributes($action);

        if (!empty($action['prefix'])) {
            $name = ltrim(rtrim(trim($action['prefix'], '_').'_'.trim($name, '_'), '_'), '_');
        }

        switch ($action['type']) {
            case 'method':
                [$class, $method] = $this->parseController($action['namespace'], $action['controller']);

                $this->addMethod($method, $class, $name, $options);
                break;

            case 'callable':
                $this->addFunction($action['callable'], $name, $options);
                break;
        }
    }

    /**
     * 获取所有已添加方法列表.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * 合并最后一组属性.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function mergeLastGroupAttributes(array $attributes): array
    {
        if (empty($this->groupStack)) {
            return $this->mergeGroup($attributes, []);
        }

        return $this->mergeGroup($attributes, end($this->groupStack));
    }

    /**
     * 合并新加入的组.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected function mergeGroup(array $new, array $old): array
    {
        $new['namespace'] = $this->formatNamespace($new, $old);
        $new['prefix'] = $this->formatPrefix($new, $old);

        return array_merge_recursive(Arr::except($old, ['namespace', 'prefix']), $new);
    }

    /**
     * 格式化命名空间.
     *
     * @param array $new
     * @param array $old
     *
     * @return string|null
     */
    protected function formatNamespace(array $new, array $old): ?string
    {
        if (isset($new['namespace'], $old['namespace'])) {
            return trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\');
        }
        if (isset($new['namespace'])) {
            return trim($new['namespace'], '\\');
        }

        return Arr::get($old, 'namespace');
    }

    /**
     * 解析控制器.
     *
     * @param string|null $namespace
     * @param string      $controller
     *
     * @throws ReflectionException
     *
     * @return array
     */
    protected function parseController($namespace, string $controller): array
    {
        [$classAsStr, $method] = explode('@', $controller);

        $refClass = new ReflectionClass(
            implode('\\', array_filter([$namespace, $classAsStr]))
        );

        $class = $refClass->newInstance();

        return [$class, $method];
    }

    /**
     * 格式化前缀
     *
     * @param array $new
     * @param array $old
     *
     * @return string
     */
    protected function formatPrefix(array $new, array $old): string
    {
        if (isset($new['prefix'])) {
            return trim(Arr::get($old, 'prefix'), '_').'_'.trim($new['prefix'], '_');
        }

        return Arr::get($old, 'prefix', '');
    }

    /**
     * 添加匿名函数.
     *
     * @param callable $action
     * @param string   $alias
     * @param array    $options
     */
    private function addFunction(callable $action, string $alias, array $options): void
    {
        $this->methods[] = $alias;

        app('hprose.server')->addFunction($action, $alias, $options);
    }

    /**
     * 添加类方法.
     *
     * @param string $method
     * @param object $class
     * @param string $alias
     * @param array  $options
     */
    private function addMethod(string $method, $class, string $alias, array $options): void
    {
        $this->methods[] = $alias;

        app('hprose.server')->addMethod(
            $method,
            $class,
            $alias,
            $options
        );
    }
}
