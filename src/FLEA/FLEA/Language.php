<?php

namespace FLEA;

/**
 * 多语言支持
 *
 * 提供多语言字典管理和翻译功能。
 * 支持自动加载语言字典、按需加载指定语言。
 *
 * 主要功能：
 * - 字典文件加载（支持多个字典）
 * - 多语言翻译获取
 * - 默认语言设置
 * - 自动加载字典
 *
 * 用法示例：
 * ```php
 * // 通过全局函数使用
 * echo _T('hello');                    // 从默认语言字典获取翻译
 * echo _T('hello', 'en');              // 从英文字典获取翻译
 *
 * // 加载字典文件
 * load_language('ui');                 // 载入默认语言的 ui.php 字典
 * load_language('ui', 'en');           // 载入英文的 ui.php 字典
 * load_language('validation');         // 载入验证字典
 *
 * // 直接调用
 * $lang = \FLEA\Language::getInstance();  // 如果作为单例使用
 * echo $lang->get('hello', 'en');
 * ```
 *
 * 字典文件格式（language/zh/ui.php）：
 * ```php
 * return [
 *     'hello' => '你好',
 *     'welcome' => '欢迎光临',
 * ];
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Language
{
    /**
     * @var array<string, array<string, string>> 已载入的字典（按语言名索引）
     */
    private array $dict = [];

    /**
     * @var array<string, bool> 已载入的字典文件路径
     */
    private array $loadedFiles = [];

    /**
     * @var string 当前默认语言
     */
    private string $defaultLanguage = '';

    /**
     * 构造函数
     *
     * 自动加载配置的字典文件。
     */
    public function __construct()
    {
        $this->defaultLanguage = \FLEA::getAppInf('defaultLanguage') ?? '';

        $autoload = \FLEA::getAppInf('autoLoadLanguage') ?? '';
        if (!is_array($autoload)) {
            $autoload = array_filter(array_map('trim', explode(',', $autoload)));
        }
        foreach ($autoload as $dictname) {
            $this->load($dictname);
        }
    }

    /**
     * 载入字典文件
     *
     * 支持载入多种语言的字典文件到内存中供后续翻译使用。
     * 字典文件会自动缓存，避免重复加载。
     *
     * 用法示例：
     * ```php
     * // 载入默认语言的字典
     * $lang->load('ui');
     *
     * // 载入英文字典
     * $lang->load('ui', 'en');
     *
     * // 载入多个字典
     * $lang->load('ui,validation');
     * $lang->load(['ui', 'validation']);
     *
     * // 静默模式（找不到文件不抛异常）
     * $lang->load('ui', '', true);
     * ```
     *
     * @param string|string[] $dictname    字典名（支持数组或逗号分隔的字符串）
     * @param string          $language    语言名（空字符串表示使用默认语言）
     * @param bool            $noException 文件不存在时是否静默（不抛异常）
     *
     * @return bool 是否成功加载（静默模式下部分失败返回 false）
     *
     * @throws \FLEA\Exception\ExpectedFile 字典文件不存在且 $noException=false 时抛出
     */
    public function load($dictname, string $language = '', bool $noException = false): bool
    {
        // 统一转为数组
        if (is_string($dictname)) {
            $dictname = array_filter(array_map('trim', explode(',', $dictname)));
        }

        $lang = $language !== ''
            ? preg_replace('/[^a-z0-9\-_]+/i', '', strtolower($language))
            : $this->defaultLanguage;

        $dir = \FLEA::getAppInf('languageFilesDir');
        $loaded = true;

        foreach ($dictname as $name) {
            $name = preg_replace('/[^a-z0-9\-_]+/i', '', strtolower(trim($name)));
            if ($name === '') { continue; }

            $filename = $dir . DS . $lang . DS . $name . '.php';
            if (isset($this->loadedFiles[$filename])) { continue; }

            if (!is_readable($filename)) {
                if (!$noException) {
                    throw new \FLEA\Exception\ExpectedFile($filename);
                }
                $loaded = false;
                continue;
            }

            $dict = require($filename);
            $this->loadedFiles[$filename] = true;
            $this->dict[$lang] = isset($this->dict[$lang])
                ? array_merge($this->dict[$lang], $dict)
                : $dict;
        }

        return $loaded;
    }

    /**
     * 获取翻译文本
     *
     * 从已加载的字典中查找指定 key 的翻译。
     * 找不到时返回原始 key 字符串。
     *
     * 用法示例：
     * ```php
     * // 从默认语言字典获取
     * echo $lang->get('hello');
     *
     * // 从英文字典获取
     * echo $lang->get('hello', 'en');
     *
     * // 配合 sprintf 使用
     * echo sprintf($lang->get('welcome_user'), $username);
     * ```
     *
     * @param string $key      翻译键名
     * @param string $language 语言名（空字符串表示使用默认语言）
     *
     * @return string 翻译后的文本（找不到时返回原 key）
     */
    public function get(string $key, string $language = ''): string
    {
        $lang = $language !== '' ? $language : $this->defaultLanguage;
        return $this->dict[$lang][$key] ?? $key;
    }
}
