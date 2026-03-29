<?php

namespace FLEA;

/**
 * 多语言支持
 *
 * 用法：
 *   load_language('ui');           // 载入默认语言的 ui.php 字典
 *   load_language('ui', 'en');     // 载入 en/ui.php 字典
 *   _T('key');                     // 从默认语言取翻译
 *   _T('key', 'en');               // 从指定语言取翻译
 *
 */
class Language
{
    /** @var array<string, array<string, string>> 已载入的字典，key 为语言名 */
    private array $dict = [];

    /** @var array<string, bool> 已载入的文件路径 */
    private array $loadedFiles = [];

    /** @var string 当前默认语言 */
    private string $defaultLanguage = '';

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
     * @param string|string[] $dictname 字典名，支持数组或逗号分隔字符串
     * @param string $language 语言名，空字符串表示使用默认语言
     * @param bool $noException 找不到文件时是否静默
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
     * 获取翻译，找不到时返回原 key
     *
     * @param string $key
     * @param string $language 空字符串表示使用默认语言
     */
    public function get(string $key, string $language = ''): string
    {
        $lang = $language !== '' ? $language : $this->defaultLanguage;
        return $this->dict[$lang][$key] ?? $key;
    }
}
