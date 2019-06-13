<?php

namespace oihub\base;

use oihub\helper\ArrayHelper;

/**
 * Class Collection.
 * 
 * @author sean <maoxfjob@163.com>
 */
class Collection implements \ArrayAccess
{
    /**
     * @var array 对象属性集合.
     */
    protected $items = [];

    /**
     * 构造函数.
     * @param array $items 对象属性集合.
     * @return void
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * 重写__get.
     * @param string $name 属性名.
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * 重写__set.
     * @param string $name 属性名.
     * @param mixed $value 属性值.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * 重写__isset.
     * @param string $name 属性名.
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * 重写__unset.
     * @param string $name 属性名.
     * @return void
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * 得到一个偏移位置的值.
     * @param string $offset 需要得到的偏移位置.
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return ArrayHelper::get($this->items, $offset);
    }

    /**
     * 设置一个偏移位置的值.
     * @param string $offset 待设置的偏移位置.
     * @param mixed $value 需要设置的值.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        ArrayHelper::set($this->items, $offset, $value);
    }

    /**
     * 检查一个偏移位置是否存在.
     * @param string $offset 需要检查的偏移位置.
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ArrayHelper::has($this->items, $offset);
    }

    /**
     * 复位一个偏移位置的值.
     * @param string $offset 待复位的偏移位置.
     * @return void
     */
    public function offsetUnset($offset)
    {
        ArrayHelper::remove($this->items, $offset);
    }
}
