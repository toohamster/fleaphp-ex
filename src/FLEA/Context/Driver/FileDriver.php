<?php

namespace FLEA\Context\Driver;

use FLEA\Context\DriverInterface;

/**
 * 文件存储驱动
 *
 * 使用文件系统作为上下文存储后端。
 * 适用于简单应用场景或无法使用 Session/Redis 的环境。
 *
 * @package FLEA
 * @subpackage Context\Driver
 * @author toohamster
 * @version 2.0.0
 */
class FileDriver implements DriverInterface
{
    /**
     * 存储目录
     *
     * @var string
     */
    private string $path;

    /**
     * 构造函数
     *
     * @param string $path 文件存储目录（默认系统临时目录）
     */
    public function __construct(string $path = '')
    {
        $this->path = $path ?: sys_get_temp_dir() . '/fleaphp_context';
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * 获取值
     *
     * @param string $key 键名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->path . '/' . md5($key);
        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));
        if ($data === false) {
            return $default;
        }

        // 检查是否过期
        if (isset($data['expires']) && $data['expires'] !== null && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * 设置值
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int|null $ttl 过期时间（秒）
     *
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $data = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
        ];

        $file = $this->path . '/' . md5($key);
        $result = file_put_contents($file, serialize($data), LOCK_EX);
        return $result !== false;
    }

    /**
     * 删除值
     *
     * @param string $key 键名
     *
     * @return bool
     */
    public function remove(string $key): bool
    {
        $file = $this->path . '/' . md5($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return false;
    }

    /**
     * 检查键是否存在
     *
     * @param string $key 键名
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $file = $this->path . '/' . md5($key);
        return file_exists($file);
    }
}
