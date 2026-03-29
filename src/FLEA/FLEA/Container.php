<?php

namespace FLEA;

/**
 * PSR-11 对象容器
 *
 * 管理对象实例的注册与获取，支持单例模式。
 * 实现了 PSR-11 ContainerInterface 接口，提供依赖注入功能。
 *
 * 主要功能：
 * - 对象注册：register() 手动注册对象实例
 * - 单例获取：singleton() 自动实例化并缓存
 * - 存在检查：has() 检查对象是否已注册
 * - 获取对象：get() 获取已注册对象，不存在抛出异常
 *
 * 用法示例：
 * ```php
 * // 获取容器单例
 * $container = \FLEA\Container::getInstance();
 *
 * // 注册对象
 * $container->register(new MyService(), 'myService');
 *
 * // 获取单例（自动实例化并缓存）
 * $logger = $container->singleton(\FLEA\Log::class);
 *
 * // 检查对象是否存在
 * if ($container->has('myService')) {
 *     $service = $container->get('myService');
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\Container\ContainerInterface
 */
class Container implements \Psr\Container\ContainerInterface
{
    /**
     * @var ?self Container 单例实例
     */
    private static ?self $instance = null;

    /**
     * @var array 已注册的对象实例池
     */
    private array $objects = [];

    /**
     * 构造函数
     */
    private function __construct() {}

    /**
     * 阻止克隆实例
     */
    private function __clone() {}

    /**
     * 获取 Container 单例实例
     *
     * 用法示例：
     * ```php
     * $container = \FLEA\Container::getInstance();
     * $container->register(new MyService());
     * ```
     *
     * @return self Container 实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PSR-11: 获取对象
     *
     * 从对象池中获取指定名称的对象。如果对象不存在则抛出
     * \FLEA\Exception\NotExistsKeyName 异常。
     *
     * 用法示例：
     * ```php
     * try {
     *     $service = $container->get('myService');
     * } catch (\FLEA\Exception\NotExistsKeyName $e) {
     *     // 处理对象不存在的情况
     * }
     * ```
     *
     * @param string $id 对象名称
     *
     * @return object 对象实例
     *
     * @throws \FLEA\Exception\NotExistsKeyName 当对象不存在时抛出
     *
     * @see    \Psr\Container\ContainerInterface::get()
     */
    public function get(string $id)
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }
        throw new \FLEA\Exception\NotExistsKeyName($id);
    }

    /**
     * PSR-11: 检查对象是否存在
     *
     * 用法示例：
     * ```php
     * if ($container->has('myService')) {
     *     $service = $container->get('myService');
     * }
     * ```
     *
     * @param string $id 对象名称
     *
     * @return bool 对象存在返回 true，否则返回 false
     *
     * @see    \Psr\Container\ContainerInterface::has()
     */
    public function has(string $id): bool
    {
        return isset($this->objects[$id]);
    }

    /**
     * 注册对象到容器中
     *
     * 将对象实例注册到容器内，使用对象名称或指定的名称作为键。
     * 如果该名称已存在则抛出 \FLEA\Exception\ExistsKeyName 异常。
     *
     * 用法示例：
     * ```php
     * // 注册对象，使用类名作为键
     * $container->register(new MyService());
     *
     * // 注册对象，指定自定义键名
     * $container->register(new MyService(), 'myService');
     * ```
     *
     * @param object      $obj  要注册的对象实例
     * @param string|null $name 对象名称（可选，省略时使用类名）
     *
     * @return object 返回注册的对象实例
     *
     * @throws \FLEA\Exception\ExistsKeyName 当对象名称已存在时抛出
     */
    public function register(object $obj, ?string $name = null): object
    {
        $name ??= get_class($obj);
        if (isset($this->objects[$name])) {
            throw new \FLEA\Exception\ExistsKeyName($name);
        }
        $this->objects[$name] = $obj;
        return $obj;
    }

    /**
     * 获取单例对象
     *
     * 如果对象已存在则直接返回；否则自动实例化类并注册到容器。
     * 如果类有静态 getInstance() 方法则调用它（支持单例模式类）。
     *
     * 用法示例：
     * ```php
     * // 自动实例化并缓存
     * $logger = $container->singleton(\FLEA\Log::class);
     *
     * // 单例类（有 getInstance() 方法）
     * $config = $container->singleton(\FLEA\Config::class);
     * ```
     *
     * @param string $className 类名
     *
     * @return object 对象实例
     *
     * @throws \FLEA\Exception\ExpectedClass 当类不存在时抛出
     */
    public function singleton(string $className): object
    {
        if (isset($this->objects[$className])) {
            return $this->objects[$className];
        }
        if (!class_exists($className, true)) {
            throw new \FLEA\Exception\ExpectedClass($className);
        }

        // 支持单例模式类（有 getInstance() 方法）
        $reflection = new \ReflectionClass($className);
        if ($reflection->hasMethod('getInstance') && $reflection->getMethod('getInstance')->isStatic()) {
            $obj = $className::getInstance();
        } else {
            $obj = new $className();
        }

        return $this->register($obj);
    }

    /**
     * 获取所有已注册的对象
     *
     * 用法示例：
     * ```php
     * $allObjects = $container->all();
     * foreach ($allObjects as $name => $obj) {
     *     echo get_class($obj);
     * }
     * ```
     *
     * @return array 所有已注册对象的数组
     */
    public function all(): array
    {
        return $this->objects;
    }
}
