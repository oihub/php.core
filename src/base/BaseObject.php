<?php

namespace oihub\base;

use oihub\helper\ObjectHelper;
use oihub\exception\Exception;

/**
 * Class BaseObject.
 * 
 * @author sean <maoxfjob@163.com>
 */
class BaseObject
{
    const FILTER_NOT_NULL = 1; // 过滤NULL.
    const FILTER_NOT_EMPTY = 2; // 过滤空. 注：0不为空.

    /**
     * 得到静态方法调用的类名.
     * @return string
     */
    public static function className(): string
    {
        return get_called_class();
    }

    /**
     * 构造函数.
     * 1. 用给定的配置初始化对象.
     * 2. 调用init.
     * @param array $config 初始化对象属性配置.
     * @return void
     */
    public function __construct(array $config = [])
    {
        empty($config) or ObjectHelper::configure($this, $config);
        $this->init();
    }

    /**
     * 在构造函数加载配置之后初始化.
     * @return void
     */
    public function init(): void
    { }

    /**
     * 重写__get.
     * @param string $name 属性名.
     * @return mixed
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            Exception::writeOnlyProperty(get_class($this) . '::' . $name);
        }
        Exception::unknownProperty(get_class($this) . '::' . $name);
    }

    /**
     * 重写__set.
     * @param string $name 属性名.
     * @param mixed $value 属性值.
     * @return void
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            Exception::readOnlyProperty(get_class($this) . '::' . $name);
        } else {
            Exception::unknownProperty(get_class($this) . '::' . $name);
        }
    }

    /**
     * 重写__isset.
     * @param string $name 属性名.
     * @return bool
     */
    public function __isset($name)
    {
        return method_exists($this, 'get' . $name);
    }

    /**
     * 重写__unset.
     * @param string $name 属性名.
     * @return void
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            Exception::readOnlyProperty(get_class($this) . '::' . $name);
        }
    }

    /**
     * 重写__call.
     * @param string $name 方法名.
     * @param array $params 参数.
     * @return mixed
     */
    public function __call($name, $params)
    {
        Exception::unknownMethod(get_class($this) . '::' . $name);
    }

    /**
     * 属性是否被定义.
     * @param string $name 属性名.
     * @param bool $checkVars 是否是属性.
     * @return bool
     */
    public function hasProperty(
        string $name,
        bool $checkVars = true
    ): bool {
        return $this->canGetProperty($name, $checkVars) ||
            $this->canSetProperty($name, false);
    }

    /**
     * 属性是否可读.
     * @param string $name 属性名.
     * @param bool $checkVars 是否是为属性.
     * @return bool
     */
    public function canGetProperty(
        string $name,
        bool $checkVars = true
    ): bool {
        return method_exists($this, 'get' . $name) ||
            $checkVars && property_exists($this, $name);
    }

    /**
     * 属性是否可写.
     * @param string $name 属性名.
     * @param bool $checkVars 是否是为属性.
     * @return bool
     */
    public function canSetProperty(
        string $name,
        bool $checkVars = true
    ): bool {
        return method_exists($this, 'set' . $name) ||
            $checkVars && property_exists($this, $name);
    }

    /**
     * 得到全部属性.
     * @return array
     */
    public final function allProperty(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            $data[] = $key;
        }
        return $data;
    }

    /**
     * 方法是否被定义.
     * @param string $name 方法名.
     * @return bool
     */
    public function hasMethod(string $name): bool
    {
        return method_exists($this, $name);
    }

    /**
     * 转换为数组.
     * @param array $columns 要得到的属性.
     * @param int|callable $filter 过滤.
     * [FILTER_NOT_NULL|FILTER_NOT_EMPTY|CALLABLE].
     * @return array
     */
    public function toArray(
        array $columns = [],
        $filter = null
    ): array {
        $data = [];
        foreach ($this as $key => $item) {
            $data[$key] = $item;
        }
        unset($data['_keyMap']);
        if (!empty($this->_keyMap)) {
            foreach ($this->_keyMap as $beanKey => $dataKey) {
                if (array_key_exists($beanKey, $data)) {
                    $data[$dataKey] = $data[$beanKey];
                    unset($data[$beanKey]);
                }
            }
        }

        empty($columns) or $data = array_intersect_key(
            $data,
            array_flip($columns)
        );
        if ($filter === static::FILTER_NOT_NULL) {
            return array_filter($data, function ($val) {
                return !is_null($val);
            });
        } else if ($filter === static::FILTER_NOT_EMPTY) {
            return array_filter($data, function ($val) {
                if ($val === 0 || $val === '0') {
                    return true;
                } else {
                    return !empty($val);
                }
            });
        } else if (is_callable($filter)) {
            return array_filter($data, $filter);
        }
        return $data;
    }
}
