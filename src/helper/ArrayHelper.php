<?php

namespace oihub\helper;

/**
 * Class ArrayHelper.
 * 
 * @author sean <maoxfjob@163.com>
 */
class ArrayHelper
{
    /**
     * 过滤数组.
     * @param array $array 数组.
     * @param string|array $keys 需要取得/排除的键.
     * @param bool $exclude [true：排除设置的键名 false：仅得到设置的键名].
     * @return array
     */
    public static function filter(
        array $array,
        $keys,
        bool $exclude = false
    ): array {
        $result = [];
        is_string($keys) and $keys = explode(',', $keys);
        foreach ($array as $key => $value) {
            if ($exclude) {
                in_array($key, $keys) ? null : $result[$key] = $value;
            } else {
                in_array($key, $keys) ? $result[$key] = $value : null;
            }
        }
        return $result;
    }

    /**
     * 得到键值.
     * @param array $array 数组.
     * @param string $path 路径.
     * @return mixed
     */
    public static function get(array $array, string $path)
    {
        $paths = explode('.', $path);
        $temp = $array;
        while ($key = array_shift($paths)) {
            if (isset($temp[$key])) {
                $temp = $temp[$key];
            } else {
                return null;
            }
        }
        return $temp;
    }

    /**
     * 得到一列.
     * @param array $array 数组.
     * @param string $name 名称.
     * @param bool $keepKeys 保留键名.
     * @return array
     */
    public static function getColumn(
        array $array,
        string $name,
        bool $keepKeys = true
    ): array {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::get($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::get($element, $name);
            }
        }
        return $result;
    }

    /**
     * 是否存在键名.
     * @param array $array 数组.
     * @param string $path 路径.
     * @return bool
     */
    public static function has(array $array, string $path): bool
    {
        $paths = explode('.', $path);
        $temp = $array;
        while ($key = array_shift($paths)) {
            if (isset($temp[$key])) {
                $temp = $temp[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 重建数组索引.
     * @param array $array 数组.
     * @param string|null $key 键名.
     * @param string|array|null $groups 分组.
     * @return array
     */
    public static function index(
        array $array,
        ? string $key,
        $groups = []
    ): array {
        $result = [];
        $groups = (array)$groups;
        foreach ($array as $item) {
            $temp = &$result;
            foreach ($groups as $group) {
                $value = static::get($item, $group);
                array_key_exists($value, $temp) or $temp[$value] = [];
                $temp = &$temp[$value];
            }

            if ($key === null) {
                empty($groups) or $temp[] = $item;
            } else {
                $value = static::get($item, $key);
                if ($value !== null) {
                    is_float($value) and $value = str_replace(
                        ',',
                        '.',
                        (string)$value
                    );
                    $temp[$value] = $item;
                }
            }
            unset($temp);
        }
        return $result;
    }

    /**
     * 多维数组建立一个映射表（键值对）.
     * @param array $array 数组.
     * @param string $from 键名.
     * @param string $to 键值.
     * @param string $group 分组.
     * @return array
     */
    public static function map(
        array $array,
        string $from,
        string $to,
        string $group = null
    ): array {
        $result = [];
        foreach ($array as $item) {
            $key = static::get($item, $from);
            $value = static::get($item, $to);
            if ($group !== null) {
                $result[static::get($item, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 合并数组.
     * @param array $arrays 数组.
     * @return array
     */
    public static function merge(array ...$arrays): array
    {
        $res = array_shift($arrays);
        while (!empty($arrays)) {
            foreach (array_shift($arrays) as $key => $value) {
                if (is_int($key)) {
                    if (array_key_exists($key, $res)) {
                        $res[] = $value;
                    } else {
                        $res[$key] = $value;
                    }
                } elseif (
                    is_array($value) &&
                    isset($res[$key]) &&
                    is_array($res[$key])
                ) {
                    $res[$key] = static::merge($res[$key], $value);
                } else {
                    $res[$key] = $value;
                }
            }
        }
        return $res;
    }

    /**
     * 得到数组中重复的值.
     * @param array $array 数组.
     * @return array
     */
    public static function multiple(array $array): array
    {
        $array = array_map('serialize', $array);
        $result = array_diff_assoc($array, array_unique($array));
        return array_map('unserialize', $result);
    }

    /**
     * 根据键名删除.
     * @param array $array 数组.
     * @param string $path 路径.
     * @return void
     */
    public static function remove(array &$array, string $path): void
    {
        $paths = explode('.', $path);
        $lastKey = array_pop($paths);
        $temp = &$array;
        while ($key = array_shift($paths)) {
            if (isset($temp[$key])) {
                $temp = &$temp[$key];
            } else {
                return;
            }
        }
        unset($temp[$lastKey]);
    }

    /**
     * 根据键值删除.
     * @param array $array 数组.
     * @param mixed $value 值.
     * @return void
     */
    public static function removeValue(array &$array, $value): void
    {
        foreach ($array as $key => $item) {
            if ($item === $value) {
                unset($array[$key]);
            }
        }
    }

    /**
     * 设置键值.
     * @param array $array 数组.
     * @param string $path 路径.
     * @param mixed $value 值.
     * @return void
     */
    public static function set(array &$array, string $path, $value): void
    {
        $paths = explode('.', $path);
        $temp = &$array;
        while ($key = array_shift($paths)) {
            $temp = &$temp[$key];
        }
        $temp = $value;
    }

    /**
     * 无限分类.
     * @param array $array 所需二维数组.
     * @param string $first 第一级ID.
     * @param string $pid 上级ID名称.
     * @param string $id ID名称.
     * @param string $child 子级名称.
     * @return array
     */
    public static function tree(
        array $array,
        string $first,
        string $pid,
        string $id,
        string $child = 'son'
    ): array {
        $tree = [];
        foreach ($array as $item) {
            $tree[$item[$id]] = $item;
        }
        foreach ($tree as $item) {
            $tree[$item[$pid]][$child][] = &$tree[$item[$id]];
        }
        return $tree[$first][$child] ?? [];
    }
}
