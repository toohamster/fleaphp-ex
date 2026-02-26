<?php

namespace FLEA;

use Psr\Log\AbstractLogger;

/**
 * 定义 FLEA_Log 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Log.php 999 2007-10-30 05:39:57Z qeeyuan $
 */

/**
 * FLEA_Log 类提供基本的日志服务，实现 PSR-3 LoggerInterface
 *
 * @package Core
 * @author toohamster
 * @version 2.0
 */
class Log extends AbstractLogger
{
    /**
     * 保存运行期间的日志，在脚本结束时将日志内容写入到文件
     *
     * @var string
     */
    public string $_log = '';

    /**
     * 日期格式
     *
     * @var string
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * 保存日志文件的目录
     *
     * @var string|null
     */
    public ?string $_logFileDir = null;

    /**
     * 保存日志的文件名
     *
     * @var string|null
     */
    public ?string $_logFilename = null;

    /**
     * 是否允许日志保存
     *
     * @var bool
     */
    public bool $_enabled = true;

    /**
     * 要写入日志文件的错误级别
     *
     * @var array|null
     */
    public ?array $_errorLevel = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $dir = \FLEA::getAppInf('logFileDir');
        if (empty($dir)) {
            // 如果没有指定日志存放目录，则保存到内部缓存目录中
            $dir = \FLEA::getAppInf('internalCacheDir');
        }
        $dir = realpath($dir);
        if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            $this->_enabled = false;
        } else {
            $this->_logFileDir = $dir;
            $this->_logFilename = $this->_logFileDir . \FLEA::getAppInf('logFilename');
            $errorLevel = (array)\FLEA::getAppInf('logErrorLevel');
            $this->_errorLevel = array_flip($errorLevel);

            [$usec, $sec] = explode(" ", FLEA_LOADED_TIME);
            $this->_log = sprintf("[%s %s] ======= FleaPHP Loaded =======\n",
                date($this->dateFormat, $sec), $usec);

            if (isset($_SERVER['REQUEST_URI'])) {
                $this->_log .= sprintf("[%s] REQUEST_URI: %s\n",
                        date($this->dateFormat),
                        $_SERVER['REQUEST_URI']);
            }

            // 注册脚本结束时要运行的方法，将缓存的日志内容写入文件
            register_shutdown_function([$this, '__writeLog']);

            // 检查文件是否已经超过指定大小
            if (file_exists($this->_logFilename)) {
                $filesize = filesize($this->_logFilename);
            } else {
                $filesize = 0;
            }
            $maxsize = (int)\FLEA::getAppInf('logFileMaxSize');
            if ($maxsize >= 512) {
                $maxsize = $maxsize * 1024;
                if ($filesize >= $maxsize) {
                    // 使用新的日志文件名
                    $pathinfo = pathinfo($this->_logFilename);
                    $newFilename = $pathinfo['dirname'] . DS .
                        basename($pathinfo['basename'], '.' . $pathinfo['extension']) .
                        date('-Ymd-His') . '.' . $pathinfo['extension'];
                    rename($this->_logFilename, $newFilename);
                }
            }
        }
    }

    /**
     * PSR-3 核心日志方法
     *
     * @param mixed  $level   日志级别（PSR-3 标准级别或自定义级别）
     * @param string $message 日志消息，支持 {key} 占位符
     * @param array  $context 上下文数据，用于替换占位符
     */
    public function log($level, $message, array $context = []): void
    {
        if (!$this->_enabled) { return; }

        $levelStr = strtolower((string)$level);
        if ($this->_errorLevel !== null && !isset($this->_errorLevel[$levelStr])) { return; }

        $message = $this->interpolate((string)$message, $context);
        $this->_log .= sprintf("[%s] [%s] %s\n", date($this->dateFormat), $levelStr, $message);
    }

    /**
     * 将日志信息写入文件
     */
    public function __writeLog(): void
    {
        // 计算应用程序执行时间（不包含入口文件）
        [$usec, $sec] = explode(" ", FLEA_LOADED_TIME);
        $beginTime = (float)$sec + (float)$usec;
        $endTime = microtime();
        [$usec, $sec] = explode(" ", $endTime);
        $endTime = (float)$sec + (float)$usec;
        $elapsedTime = $endTime - $beginTime;
        $this->_log .= sprintf("[%s %s] ======= FleaPHP End (elapsed: %f seconds) =======\n\n",
            date($this->dateFormat, $sec), $usec, $elapsedTime);

        $fp = fopen($this->_logFilename, 'a');
        if (!$fp) { return; }
        flock($fp, LOCK_EX);
        fwrite($fp, str_replace("\r", '', $this->_log));
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * PSR-3 context 占位符插值
     *
     * @param string $message
     * @param array  $context
     * @return string
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
}
