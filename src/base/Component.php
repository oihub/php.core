<?php

namespace oihub\base;

use oihub\helper\StringHelper;

/**
 * Class Component.
 * 
 * DEMO.
 * class Cat extends \oihub\base\Component
 * {
 *     public function shout()
 *     {
 *         echo '猫：miao miao miao <br />';
 *         $this->trigger(staic::className(), 'miao');
 *     }
 * }
 * class Mouse
 * {
 *     public static function run()
 *     {
 *         echo '老鼠：有猫来了，赶紧跑啊~~<br />';
 *     }
 * }
 * 
 * Cat::on(Cat::className(), 'miao', 'Mouse::run');
 * 
 * $cat = new Cat;
 * $cat->shout();
 * (new Cat)->shout();
 * exit;
 * 
 * @author sean <maoxfjob@163.com>
 */
class Component extends BaseObject
{
    /**
     * @var string 事件名称.
     */
    public $name;
    /**
     * @var string 发送者.
     */
    public $sender;
    /**
     * @var bool 事件是否被处理.
     */
    public $handled = false;
    /**
     * @var mixed 传递的数据.
     */
    public $data;
    /**
     * @var array 全部事件.
     */
    private static $_events = [];
    /**
     * @var array 通配符模式全部事件.
     */
    private static $_eventWildcards = [];

    /**
     * 附加事件处理器.
     * ```php
     * Event::on(
     *     class: :className(),
     *     class::EVENT_AFTER_INSERT,
     *     function ($event) { }
     * );
     * Event::on(
     *     'app\models\db\*',
     *     '*Insert',
     *     function ($event) { }
     * );
     * ```
     * @param string $class 类名.
     * @param string $name 事件名.
     * @param string|callable $handler 匿名函数.
     * @param mixed $data 传递的数据.
     * @param bool $append 是否将新的事件处理程序附加到现有事件的结尾.
     * @return void
     */
    public static function on(
        string $class,
        string $name,
        $handler,
        $data = null,
        bool $append = true
    ): void {
        $class = ltrim($class, '\\');
        if (strpos($class, '*') !== false || strpos($name, '*') !== false) {
            if ($append || empty(static::$_eventWildcards[$name][$class])) {
                static::$_eventWildcards[$name][$class][] = [$handler, $data];
            } else {
                array_unshift(
                    static::$_eventWildcards[$name][$class],
                    [$handler, $data]
                );
            }
            return;
        }
        if ($append || empty(static::$_events[$name][$class])) {
            static::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(static::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     * 移除事件处理器.
     * @param string $class 类名.
     * @param string $name 事件名.
     * @param callable $handler 匿名函数.
     * @return bool
     */
    public static function off(
        string $class,
        string $name,
        ? callable $handler = null
    ): bool {
        $class = ltrim($class, '\\');
        if (
            empty(static::$_events[$name][$class]) &&
            empty(static::$_eventWildcards[$name][$class])
        ) {
            return false;
        }
        if ($handler === null) {
            unset(static::$_events[$name][$class]);
            if (empty(static::$_events[$name])) {
                unset(static::$_events[$name]);
            }
            unset(static::$_eventWildcards[$name][$class]);
            if (empty(static::$_eventWildcards[$name])) {
                unset(static::$_eventWildcards[$name]);
            }
            return true;
        }
        return static::remove(
            static::$_events,
            $name,
            $class,
            $handler
        ) || static::remove(
            static::$_eventWildcards,
            $name,
            $class,
            $handler
        );
    }

    /**
     * 移除全部事件处理器.
     * @return void
     */
    public static function offAll(): void
    {
        static::$_events = [];
        static::$_eventWildcards = [];
    }

    /**
     * 是否附加了任何处理程序.
     * @param string|object $class 类名.
     * @param string $name 事件名.
     * @return bool
     */
    public static function hasHandlers($class, string $name): bool
    {
        if (
            empty(static::$_eventWildcards) &&
            empty(static::$_events[$name])
        ) {
            return false;
        }
        $class = is_object($class) ? get_class($class) : ltrim($class, '\\');
        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );
        foreach ($classes as $class) {
            if (!empty(static::$_events[$name][$class])) {
                return true;
            }
        }
        foreach (static::$_eventWildcards as
            $nameWildcard => $classHandlers) {
            if (!StringHelper::matchWildcard($nameWildcard, $name)) {
                continue;
            }
            foreach ($classHandlers as $classWildcard => $handlers) {
                if (empty($handlers)) {
                    continue;
                }
                foreach ($classes as $class) {
                    if (!StringHelper::matchWildcard($classWildcard, $class)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 触发事件.
     * @param mixed $class 类名.
     * @param string $name 事件名.
     * @param self $event 事件参数.
     * @return void
     */
    public static function trigger(
        $class,
        string $name,
        self $event = null
    ): void {
        $wildcardEventHandlers = [];
        foreach (static::$_eventWildcards as
            $nameWildcard => $classHandlers) {
            if (!StringHelper::matchWildcard($nameWildcard, $name)) {
                continue;
            }
            $wildcardEventHandlers = array_merge(
                $wildcardEventHandlers,
                $classHandlers
            );
        }

        if (
            empty(static::$_events[$name]) &&
            empty($wildcardEventHandlers)
        ) {
            return;
        }

        $event === null and $event = new static;
        $event->handled = false;
        $event->name = $name;

        if (is_object($class)) {
            $event->sender === null and
                $event->sender = $class;
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            $eventHandlers = [];
            foreach ($wildcardEventHandlers as $classWildcard => $handlers) {
                if (StringHelper::matchWildcard($classWildcard, $class)) {
                    $eventHandlers = array_merge($eventHandlers, $handlers);
                    unset($wildcardEventHandlers[$classWildcard]);
                }
            }

            if (!empty(static::$_events[$name][$class])) {
                $eventHandlers = array_merge(
                    $eventHandlers,
                    static::$_events[$name][$class]
                );
            }

            foreach ($eventHandlers as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                if ($event->handled) {
                    return;
                }
            }
        }
    }

    /**
     * 移除事件处理器.
     * @param array $event 事件.
     * @param string $name 事件名.
     * @param string $class 类名.
     * @param callable $handler 匿名函数.
     * @return bool
     */
    private static function remove(
        array &$event,
        string $name,
        string $class,
        ? callable $handler
    ): bool {
        $removed = false;
        if (isset($event[$name][$class])) {
            foreach ($event[$name][$class] as $i => $item) {
                if ($item[0] === $handler) {
                    unset($event[$name][$class][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $event[$name][$class] = array_values($event[$name][$class]);
                if (empty($event[$name][$class])) {
                    unset($event[$name][$class]);
                    if (empty($event[$name])) {
                        unset($event[$name]);
                    }
                }
            }
        }
        return $removed;
    }
}
