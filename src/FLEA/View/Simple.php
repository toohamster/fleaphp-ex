<?php

namespace FLEA\View;

/**
 * 简单视图引擎（PHP 原生模板）
 *
 * 使用 PHP 原生语法作为模板语言，支持模板缓存功能。
 *
 * 主要功能：
 * - 模板变量分配（assign）
 * - 模板渲染输出（display/fetch）
 * - 模板缓存（可开关）
 * - 缓存管理
 *
 * 用法示例：
 * ```php
 * // 创建视图对象
 * $view = new \FLEA\View\Simple('/path/to/templates');
 *
 * // 分配变量
 * $view->assign('title', '页面标题');
 * $view->assign(['name' => 'John', 'age' => 30]);
 *
 * // 渲染并输出
 * $view->display('index.php');
 *
 * // 获取渲染内容（不输出）
 * $content = $view->fetch('index.php');
 *
 * // 带缓存渲染
 * $content = $view->fetch('index.php', 'cache_key_123');
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\View\ViewInterface
 */
class Simple implements ViewInterface
{
    /**
     * @var string|null 模板文件所在目录
     */
    public ?string $templateDir = null;

    /**
     * @var int 缓存有效期（秒），默认 900 秒（15 分钟）
     */
    public int $cacheLifetime;

    /**
     * @var bool 是否启用缓存
     */
    public bool $enableCache;

    /**
     * @var string 缓存文件目录
     */
    public string $cacheDir;

    /**
     * @var array 模板变量
     */
    public array $vars = [];

    /**
     * @var array 缓存状态记录
     */
    public array $cacheState = [];

    /**
     * 构造函数
     *
     * @param string|null $templateDir 模板文件目录
     */
    public function __construct(?string $templateDir = null)
    {
        $this->templateDir = $templateDir;
        $this->cacheLifetime = 900;
        $this->enableCache = true;
        $this->cacheDir = './cache';

        $viewConfig = (array)\FLEA::getAppInf('viewConfig');
        $keys = [
            'templateDir', 'cacheDir', 'cacheLifeTime', 'enableCache',
        ];
        foreach ($keys as $key)
        {
            if (array_key_exists($key, $viewConfig))
            {
                $this->{$key} = $viewConfig[$key];
            }
        }
    }

    /**
     * 分配模板变量
     *
     * 用法示例：
     * ```php
     * // 分配单个变量
     * $view->assign('title', '页面标题');
     *
     * // 分配多个变量
     * $view->assign(['name' => 'John', 'age' => 30]);
     * ```
     *
     * @param string|array $name  变量名或变量数组
     * @param mixed        $value 变量值（当 $name 为字符串时有效）
     *
     * @return void
     */
    public function assign($name, $value = null): void
    {
        if (is_array($name) && is_null($value)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }
    }

    /**
     * 获取渲染后的内容
     *
     * 渲染模板文件并返回内容，支持缓存功能。
     * 指定 $cacheId 时会使用缓存，避免重复渲染。
     *
     * 用法示例：
     * ```php
     * // 渲染模板
     * $content = $view->fetch('index.php');
     *
     * // 使用缓存
     * $content = $view->fetch('index.php', 'home_page');
     * ```
     *
     * @param string      $file    模板文件名
     * @param string|null $cacheId 缓存 ID（可选，用于缓存视图内容）
     *
     * @return string 渲染后的 HTML 内容
     */
    public function fetch(string $file, ?string $cacheId = null): string
    {
        log_message('Rendering view: ' . $file . ($cacheId ? ' (cacheId: ' . $cacheId . ')' : ''), \Psr\Log\LogLevel::DEBUG);

        if ($this->enableCache) {
            $cacheFile = $this->_getCacheFile($file, $cacheId);
            if ($this->isCached($file, $cacheId)) {
                return file_get_contents($cacheFile);
            }
        }

        // 生成输出内容并缓存
        extract($this->vars);
        ob_start();

        include($this->templateDir . DIRECTORY_SEPARATOR . $file);
        $contents = ob_get_contents();
        ob_end_clean();

        if ($this->enableCache) {
            // 缓存输出内容，并保存缓存状态
            $this->cacheState[$cacheFile] = file_put_contents($cacheFile, $contents) > 0;
        }

        return $contents;
    }

    /**
     * 显示视图
     *
     * 渲染模板并输出到浏览器。
     *
     * 用法示例：
     * ```php
     * // 直接输出
     * $view->display('index.php');
     *
     * // 使用缓存
     * $view->display('index.php', 'home_page');
     * ```
     *
     * @param string      $file    模板文件名
     * @param string|null $cacheId 缓存 ID（可选）
     *
     * @return void
     */
    public function display(string $file, ?string $cacheId = null): void
    {
        echo $this->fetch($file, $cacheId);
    }

    /**
     * 检查缓存是否有效
     *
     * 判断指定模板和缓存 ID 的内容是否已缓存且未过期。
     *
     * @param string      $file    模板文件名
     * @param string|null $cacheId 缓存 ID
     *
     * @return bool 缓存有效返回 true，否则返回 false
     */
    public function isCached(string $file, ?string $cacheId = null): bool
    {
        // 如果禁用缓存则返回 false
        if (!$this->enableCache) { return false; }

        // 如果缓存标志有效返回 true
        $cacheFile = $this->_getCacheFile($file, $cacheId);
        if (isset($this->cacheState[$cacheFile]) && $this->cacheState[$cacheFile]) {
            return true;
        }

        // 检查缓存文件是否存在
        if (!is_readable($cacheFile)) { return false; }

        // 检查缓存文件是否已经过期
        $mtime = filemtime($cacheFile);
        if ($mtime == false) { return false; }
        if (($mtime + $this->cacheLifetime) < time()) {
            $this->cacheState[$cacheFile] = false;
            @unlink($cacheFile);
            return false;
        }

        $this->cacheState[$cacheFile] = true;
        return true;
    }

    /**
     * 清除指定缓存
     *
     * @param string      $file    模板文件名
     * @param string|null $cacheId 缓存 ID
     *
     * @return void
     */
    public function cleanCache(string $file, ?string $cacheId = null): void
    {
        @unlink($this->_getCacheFile($file, $cacheId));
    }

    /**
     * 清除所有缓存
     *
     * @return void
     */
    public function cleanAllCache(): void
    {
        foreach (glob($this->cacheDir . '/' . "*.php") as $filename) {
            @unlink($filename);
        }
    }

    /**
     * 获取缓存文件路径
     *
     * @param string      $file    模板文件名
     * @param string|null $cacheId 缓存 ID
     *
     * @return string 缓存文件完整路径
     */
    protected function _getCacheFile(string $file, ?string $cacheId = null): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . rawurlencode($file . '-' . $cacheId) . '.php';
    }
}
