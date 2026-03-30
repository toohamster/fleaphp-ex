<?php

namespace FLEA\Context\Driver;

use FLEA\Context\DriverInterface;
use FLEA\Db\Driver\AbstractDriver;

/**
 * 数据库 Session 存储驱动
 *
 * 将上下文数据保存到数据库，适用于无法使用 Redis 或文件系统的共享主机环境。
 *
 * 使用前准备：
 * 1. 创建数据表：
 *    CREATE TABLE contexts (
 *        context_id VARCHAR(64) PRIMARY KEY,
 *        context_data TEXT,
 *        activity INT(11)
 *    );
 *
 * 2. 配置应用程序：
 *    'contextDriver' => 'database',
 *    'context' => [
 *        'database' => [
 *            'tableName' => 'contexts',
 *            'fieldId' => 'context_id',
 *            'fieldData' => 'context_data',
 *            'fieldActivity' => 'activity',
 *            'lifeTime' => 3600,
 *        ],
 *    ],
 *
 * @package FLEA
 * @subpackage Context\Driver
 * @author toohamster
 * @version 2.0.0
 */
class DatabaseSessionDriver implements DriverInterface
{
    /**
     * 数据库访问对象
     *
     * @var AbstractDriver
     */
    private AbstractDriver $dbo;

    /**
     * 数据表名称
     *
     * @var string
     */
    private string $tableName;

    /**
     * ID 字段名
     *
     * @var string
     */
    private string $fieldId;

    /**
     * 数据字段名
     *
     * @var string
     */
    private string $fieldData;

    /**
     * 活动时间字段名
     *
     * @var string
     */
    private string $fieldActivity;

    /**
     * 有效期（秒）
     *
     * @var int
     */
    private int $lifeTime;

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        // 获取数据库连接
        $this->dbo = \FLEA::getSingleton(\FLEA\Database::class)->connection();

        // 配置参数
        $this->tableName = $config['tableName'] ?? 'contexts';
        $this->fieldId = $config['fieldId'] ?? 'context_id';
        $this->fieldData = $config['fieldData'] ?? 'context_data';
        $this->fieldActivity = $config['fieldActivity'] ?? 'activity';
        $this->lifeTime = (int)($config['lifeTime'] ?? 3600);

        // 初始化表名和字段名（加引号）
        $this->tableName = $this->dbo->qtable($this->tableName);
        $this->fieldId = $this->dbo->qfield($this->fieldId);
        $this->fieldData = $this->dbo->qfield($this->fieldData);
        $this->fieldActivity = $this->dbo->qfield($this->fieldActivity);

        // 清理过期数据
        $this->gc();
    }

    /**
     * 获取值
     *
     * @param string $key 键名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $key = $this->dbo->qstr($key);
        $sql = "SELECT {$this->fieldData} FROM {$this->tableName} WHERE {$this->fieldId} = {$key}";

        if ($this->lifeTime > 0) {
            $time = time() - $this->lifeTime;
            $sql .= " AND {$this->fieldActivity} >= {$time}";
        }

        $data = $this->dbo->getOne(sql_statement($sql));
        if ($data === null || $data === '') {
            return $default;
        }

        return unserialize($data);
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
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $key = $this->dbo->qstr($key);
        $data = $this->dbo->qstr(serialize($value));
        $activity = time();

        // 检查记录是否存在
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$this->fieldId} = {$key}";
        $count = (int)$this->dbo->getOne(sql_statement($sql));

        if ($count > 0) {
            // 更新记录
            $sql = "UPDATE {$this->tableName} SET {$this->fieldData} = {$data}, {$this->fieldActivity} = {$activity} WHERE {$this->fieldId} = {$key}";
        } else {
            // 插入记录
            $sql = "INSERT INTO {$this->tableName} ({$this->fieldId}, {$this->fieldData}, {$this->fieldActivity}) VALUES ({$key}, {$data}, {$activity})";
        }

        $this->dbo->execute(sql_statement($sql));
        return true;
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
        $key = $this->dbo->qstr($key);
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->fieldId} = {$key}";
        $this->dbo->execute(sql_statement($sql));
        return true;
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
        $key = $this->dbo->qstr($key);
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$this->fieldId} = {$key}";

        if ($this->lifeTime > 0) {
            $time = time() - $this->lifeTime;
            $sql .= " AND {$this->fieldActivity} >= {$time}";
        }

        return (int)$this->dbo->getOne(sql_statement($sql)) > 0;
    }

    /**
     * 清理过期数据
     *
     * @return void
     */
    private function gc(): void
    {
        if ($this->lifeTime > 0) {
            $time = time() - $this->lifeTime;
            $sql = "DELETE FROM {$this->tableName} WHERE {$this->fieldActivity} < {$time}";
            $this->dbo->execute(sql_statement($sql));
        }
    }
}
