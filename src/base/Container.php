<?php

namespace oihub\base;

use oihub\exception\Exception;

/**
 * Class Container.
 * 
 * @author sean <maoxfjob@163.com>
 */
class Container extends Collection
{
    /**
     * @var array 对象属性集合.
     */
    protected $items = [];
    /**
     * @var array 工厂集合.
     */
    protected $factory;
    /**
     * @var array 属性集合.
     */
    protected $protect;
    /**
     * @var array 冻结集合.
     */
    protected $frozen = [];
    /**
     * @var array 原始集合.
     */
    protected $raw = [];
    /**
     * @var array 键值集合.
     */
    protected $keys = [];

    /**
     * 构造函数.
     * @param array $items 对象属性集合.
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->factory = new \SplObjectStorage;
        $this->protect = new \SplObjectStorage;
        parent::__construct($items);
    }

    /**
     * 得到一个偏移位置的值.
     * @param string $offset 需要得到的偏移位置.
     * @return mixed
     */
    public function offsetGet($offset)
    {
        isset($this->keys[$offset]) or
            Exception::unknownProperty($offset);
        if (
            isset($this->raw[$offset]) ||
            !is_object($this->items[$offset]) ||
            isset($this->protect[$this->items[$offset]]) ||
            !method_exists($this->items[$offset], '__invoke')
        ) {
            return $this->items[$offset];
        }

        if (isset($this->factory[$this->items[$offset]])) {
            return $this->items[$offset]($this);
        }

        $raw = $this->items[$offset];
        $val = $this->items[$offset] = $raw($this);
        $this->raw[$offset] = $raw;
        $this->frozen[$offset] = true;
        return $val;
    }

    /**
     * 设置一个偏移位置的值.
     * @param string $offset 待设置的偏移位置.
     * @param mixed $value 需要设置的值.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        isset($this->frozen[$offset]) and
            Exception::overrideService($offset);
        $this->items[$offset] = $value;
        $this->keys[$offset] = true;
    }

    /**
     * 检查一个偏移位置是否存在.
     * @param string $offset 需要检查的偏移位置.
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->keys[$offset]);
    }

    /**
     * 复位一个偏移位置的值.
     * @param string $offset 待复位的偏移位置.
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->keys[$offset])) {
            if (is_object($this->items[$offset])) {
                unset($this->factory[$this->items[$offset]],
                $this->protect[$this->items[$offset]]);
            }
            unset($this->items[$offset], $this->frozen[$offset],
            $this->raw[$offset], $this->keys[$offset]);
        }
    }

    /**
     * 定义工厂服务.
     * @param callable $callable 匿名函数.
     * @return callable
     */
    public function factory(callable $callable): callable
    {
        method_exists($callable, '__invoke') or
            Exception::unknownMethod('__invoke');
        $this->factory->attach($callable);
        return $callable;
    }

    /**
     * 将匿名函数定义为参数.
     * @param callable $callable 匿名函数.
     * @return callable
     */
    public function protect(callable $callable): callable
    {
        method_exists($callable, '__invoke') or
            Exception::unknownMethod('__invoke');
        $this->protect->attach($callable);
        return $callable;
    }

    /**
     * 得到对象的参数或闭包.
     * @param string $offset 需要得到的偏移位置.
     * @return mixed
     */
    public function raw(string $offset)
    {
        isset($this->keys[$offset]) or
            Exception::unknownProperty($offset);
        if (isset($this->raw[$offset])) {
            return $this->raw[$offset];
        }
        return $this->items[$offset];
    }

    /**
     * 扩展对象.
     * @param string $offset 需要得到的偏移位置.
     * @param callable $callable 匿名函数.
     * @return callable
     */
    public function extend(string $offset, callable $callable): callable
    {
        isset($this->keys[$offset]) or
            Exception::unknownProperty($offset);
        isset($this->frozen[$offset]) and
            Exception::overrideService($offset);
        is_object($this->items[$offset]) or
            Exception::unknownMethod('__invoke');
        method_exists($this->items[$offset], '__invoke') or
            Exception::unknownMethod('__invoke');
        isset($this->protect[$this->items[$offset]]) and
            Exception::permission($offset);
        is_object($callable) or
            Exception::unknownMethod('__invoke');
        method_exists($callable, '__invoke') or
            Exception::unknownMethod('__invoke');

        $factory = $this->items[$offset];
        $extended = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
        if (isset($this->factory[$factory])) {
            $this->factory->detach($factory);
            $this->factory->attach($extended);
        }
        return $this[$offset] = $extended;
    }

    /**
     * 返回所有名称.
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * 注册服务.
     * @param ServiceProviderInterface $provider 实例.
     * @param array $items 配置.
     * @return self
     */
    public function register(
        ServiceProviderInterface $provider,
        array $items = []
    ): self {
        $provider->register($this);
        foreach ($items as $key => $value) {
            $this[$key] = $value;
        }
        return $this;
    }
}
