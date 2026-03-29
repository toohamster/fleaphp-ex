<?php

namespace FLEA;

/**
 * PSR-11 对象容器
 *
 * 管理对象实例的注册与获取，支持单例模式。
 */
class Container implements \Psr\Container\ContainerInterface
{
    private static ?self $instance = null;

    private array $objects = [];

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PSR-11: 获取对象，不存在则抛出 NotFoundExceptionInterface
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
     */
    public function has(string $id): bool
    {
        return isset($this->objects[$id]);
    }

    /**
     * 注册对象，重复注册抛出 ExistsKeyName
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
     * 获取单例：存在则返回，不存在则实例化后注册
     * 如果类有 getInstance() 静态方法则调用它（支持单例模式类）
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
     * 返回所有已注册对象
     */
    public function all(): array
    {
        return $this->objects;
    }
}
