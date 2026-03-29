<?php

namespace FLEA;

use Psr\Log\AbstractLogger;

/**
 * 日志服务，实现 PSR-3 LoggerInterface
 *
 * 每条日志格式：[datetime] [traceId] [level] message
 */
class Log extends AbstractLogger
{
    public string $traceId    = '';
    public string $dateFormat = 'Y-m-d H:i:s';
    public bool   $enabled    = true;

    public ?string $logFileDir  = null;
    public ?string $logFilename = null;
    public ?array  $errorLevel  = null;

    /** 内存中缓存的日志内容 */
    private string $buffer = '';

    public function __construct()
    {
        $this->traceId = $this->generateTraceId();

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
            date($this->dateFormat), $this->traceId,
            $_SERVER['REQUEST_METHOD'] ?? 'CLI', $uri
        );

        register_shutdown_function([$this, 'flush']);
    }

    /**
     * PSR-3 核心日志方法
     */
    public function log($level, $message, array $context = []): void
    {
        if (!$this->enabled) { return; }

        $levelStr = strtolower((string)$level);
        if ($this->errorLevel && !isset($this->errorLevel[$levelStr])) { return; }

        $this->buffer .= sprintf("[%s] [%s] [%s] %s\n",
            date($this->dateFormat),
            $this->traceId,
            $levelStr,
            $this->interpolate((string)$message, $context)
        );
    }

    /**
     * 将缓冲日志写入文件（由 register_shutdown_function 调用）
     */
    public function flush(): void
    {
        if (!$this->enabled || $this->buffer === '') { return; }

        $elapsed = microtime(true) - microtime_float(FLEA_LOADED_TIME);
        $this->buffer .= sprintf("[%s] [%s] [info] elapsed %.4fs\n",
            date($this->dateFormat), $this->traceId, $elapsed
        );

        $fp = fopen($this->logFilename, 'a');
        if (!$fp) { return; }
        flock($fp, LOCK_EX);
        fwrite($fp, $this->buffer);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    private function generateTraceId(): string
    {
        return generate_traceid();
    }

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
