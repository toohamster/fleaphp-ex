<?php

namespace FLEA;

use Psr\Log\AbstractLogger;

/**
 * 日志服务，实现 PSR-3 LoggerInterface
 *
 * 每条日志格式：`[datetime] [traceId] [level] message`
 *
 * 支持功能：
 * - 唯一 TraceId：每条请求生成独立的 traceId，便于追踪
 * - 日志轮转：按文件大小自动轮转日志文件
 * - 级别过滤：通过 logErrorLevel 配置过滤日志级别
 * - 自动刷新：请求结束时自动将缓冲日志写入文件
 *
 * 用法示例：
 * ```php
 * // 获取日志服务实例
 * $log = \FLEA::getSingleton(\FLEA\Log::class);
 *
 * // 记录日志
 * $log->info('用户登录成功', ['user_id' => 123]);
 * $log->error('数据库连接失败', ['error' => $e->getMessage()]);
 *
 * // 获取当前请求的 TraceId
 * $traceId = $log->getTraceId();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\Log\LoggerInterface
 * @see     \Psr\Log\AbstractLogger
 */
class Log extends AbstractLogger
{
    /**
     * @var string 日期时间格式（默认 'Y-m-d H:i:s'）
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var bool 日志服务是否启用
     */
    public bool   $enabled    = true;

    /**
     * @var string|null 日志文件目录
     */
    public ?string $logFileDir  = null;

    /**
     * @var string|null 日志文件名
     */
    public ?string $logFilename = null;

    /**
     * @var array|null 允许的日志级别列表
     */
    public ?array  $errorLevel  = null;

    /**
     * @var string 内存中缓存的日志内容
     */
    private string $buffer = '';

    /**
     * 构造函数
     *
     * 初始化日志服务：
     * 1. 生成 TraceId
     * 2. 设置日志目录和文件名
     * 3. 检查目录是否可写
     * 4. 执行日志轮转
     * 5. 记录请求入口日志
     * 6. 注册关闭时自动刷新
     */
    public function __construct()
    {
        $dir = \FLEA::getAppInf('logFileDir') ?: \FLEA::getAppInf('internalCacheDir');
        $dir = realpath($dir);
        if (!$dir || !is_dir($dir) || !is_writable($dir)) {
            $this->enabled = false;
            return;
        }

        $this->logFileDir  = rtrim($dir, DS) . DS;
        $this->logFilename = $this->logFileDir . \FLEA::getAppInf('logFilename');
        $this->errorLevel  = array_flip((array)\FLEA::getAppInf('logErrorLevel'));

        $this->rotateIfNeeded();

        // 记录请求入口
        $uri = $_SERVER['REQUEST_URI'] ?? 'cli';
        $this->buffer = sprintf("[%s] [%s] [info] %s %s\n",
            date($this->dateFormat), \FLEA\Context\TraceContext::getFullTraceId(),
            $_SERVER['REQUEST_METHOD'] ?? 'CLI', $uri
        );

        register_shutdown_function([$this, 'flush']);
    }

    /**
     * PSR-3 核心日志方法
     *
     * 记录日志消息到内存缓冲。支持级别过滤和上下文占位符替换。
     *
     * 用法示例：
     * ```php
     * // 记录信息日志
     * $log->log('info', '用户 {user_id} 登录成功', ['user_id' => 123]);
     *
     * // 记录错误日志
     * $log->log('error', '数据库错误：{error}', ['error' => $e->getMessage()]);
     * ```
     *
     * @param string $level   日志级别（debug, info, notice, warning, error, critical, alert, emergency）
     * @param string $message 日志消息（可包含 {placeholder} 占位符）
     * @param array  $context 上下文变量（用于替换占位符）
     *
     * @return void
     *
     * @see    \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = []): void
    {
        if (!$this->enabled) { return; }

        $levelStr = strtolower((string)$level);
        if ($this->errorLevel && !isset($this->errorLevel[$levelStr])) { return; }

        $this->buffer .= sprintf("[%s] [%s] [%s] %s\n",
            date($this->dateFormat),
            \FLEA\Context\TraceContext::getFullTraceId(),
            $levelStr,
            $this->interpolate((string)$message, $context)
        );
    }

    /**
     * 刷新缓冲日志到文件
     *
     * 由 register_shutdown_function 在请求结束时自动调用。
     * 将内存中的日志缓冲追加写入到日志文件。
     *
     * @return void
     */
    public function flush(): void
    {
        if (!$this->enabled || $this->buffer === '') { return; }

        $elapsed = microtime(true) - microtime_float(FLEA_LOADED_TIME);
        $this->buffer .= sprintf("[%s] [%s] [info] elapsed %.4fs\n",
            date($this->dateFormat), \FLEA\Context\TraceContext::getFullTraceId(), $elapsed
        );

        $fp = fopen($this->logFilename, 'a');
        if (!$fp) { return; }
        flock($fp, LOCK_EX);
        fwrite($fp, $this->buffer);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 检查并执行日志轮转
     *
     * 当日志文件超过配置的最大大小时，将其重命名为带时间戳的备份文件。
     *
     * @return void
     */
    private function rotateIfNeeded(): void
    {
        $maxsize = (int)\FLEA::getAppInf('logFileMaxSize');
        if ($maxsize < 512 || !file_exists($this->logFilename)) { return; }
        if (filesize($this->logFilename) < $maxsize * 1024) { return; }

        $info = pathinfo($this->logFilename);
        $newName = $info['dirname'] . DS
            . basename($info['basename'], '.' . $info['extension'])
            . date('-Ymd-His') . '.' . $info['extension'];
        rename($this->logFilename, $newName);
    }

    /**
     * 替换日志消息中的占位符
     *
     * 将消息中的 {key} 占位符替换为 context 中对应的值。
     *
     * @param string $message 日志消息
     * @param array  $context 上下文变量
     *
     * @return string 替换后的消息
     *
     * @see    \Psr\Log\LoggerInterface::interpolate()
     */
    private function interpolate(string $message, array $context): string
    {
        if (empty($context)) { return $message; }
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
}
