<?php

namespace oihub\helper;

/**
 * Class ObjectHelper.
 * 
 * @author sean <maoxfjob@163.com>
 */
class ObjectHelper
{
    /**
     * 初始属性值配置对象.
     * @param object $object 对象.
     * @param array $properties 属性.
     * @return object
     */
    public function configure(object $object, array $properties): object
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }
}
