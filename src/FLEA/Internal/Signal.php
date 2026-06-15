<?php

namespace FLEA\Internal;

/**
 * 内部信号机制（发布/订阅模式）
 *
 * 用于控制响应发送时机，防止中间件中途发送响应
 *
 * @internal 框架内部使用，不对外暴露
 */
final class Signal
{
    /**
     * @var array<string, array<int, callable>> 事件监听器列表
     */
    private static $listeners = [];

    /**
     * 订阅事件
     *
     * @param string $event 事件名称
     * @param callable $callback 回调函数
     */
    public static function subscribe($event, callable $callback)
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $callback;
    }

    /**
     * 发布事件
     *
     * @param string $event 事件名称
     */
    public static function publish($event)
    {
        if (!isset(self::$listeners[$event])) {
            return;
        }
        foreach (self::$listeners[$event] as $callback) {
            call_user_func($callback);
        }
    }

    /**
     * 清除所有监听器（用于测试）
     */
    public static function clear()
    {
        self::$listeners = [];
    }
}
