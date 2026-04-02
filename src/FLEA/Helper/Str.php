<?php

namespace FLEA\Helper;

/**
 * 字符串工具类
 *
 * 提供常用字符串操作工具方法。
 *
 * 主要功能：
 * - 命名参数提取（extract）
 *
 * 用法示例：
 * ```php
 * // 提取命名参数
 * $result = \FLEA\Helper\Str::extract('/2012/08/12/test.html', '/{year}/{month}/{day}/{title}.html');
 * // ['year' => '2012', 'month' => '08', 'day' => '12', 'title' => 'test']
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.2.1
 */
class Str
{
    /**
     * 从字符串提取命名参数
     *
     * 将模式字符串转换为正则表达式，从目标字符串中提取命名参数。
     *
     * 用法示例：
     * ```php
     * // 基本用法
     * $result = Str::extract('380-250-80-j', '{width}-{height}-{quality}-{format}');
     * // ['width' => '380', 'height' => '250', 'quality' => '80', 'format' => 'j']
     *
     * // 提取 URL 路径
     * $result = Str::extract('/2012/08/12/test.html', '/{year}/{month}/{day}/{title}.html');
     * // ['year' => '2012', 'month' => '08', 'day' => '12', 'title' => 'test']
     *
     * // 忽略大小写
     * $result = Str::extract('HELLO World', 'hello {name}', ['case_insensitive' => true]);
     * // ['name' => 'World']
     *
     * // 压缩空白字符
     * $result = Str::extract('hello   world', 'hello {name}', ['collapse_whitespace' => true]);
     * // ['name' => 'world']
     *
     * // 自定义分隔符
     * $result = Str::extract('Hello :name', 'Hello :name', ['delimiters' => [':', '']]);
     * // ['name' => 'name']
     * ```
     *
     * @param string $string  目标字符串
     * @param string $pattern 模式字符串（支持 {name} 占位符）
     * @param array  $options 选项
     *
     * @return array 提取的命名参数，匹配失败返回空数组
     */
    public static function extract(string $string, string $pattern, array $options = []): array
    {
        $defaults = [
            'delimiters' => ['{', '}'],       // 分隔符
            'strip_values' => false,          // 去除值的首尾空格
            'case_insensitive' => false,      // 忽略大小写
            'collapse_whitespace' => false,   // 压缩连续空白为单个空格
        ];
        $options = array_merge($defaults, $options);

        // 空白压缩处理
        if ($options['collapse_whitespace']) {
            $string = preg_replace('/\s+/', ' ', $string);
            $pattern = preg_replace('/\s+/', ' ', $pattern);
        }

        // 分隔符验证
        if (!is_array($options['delimiters']) || count($options['delimiters']) != 2) {
            return [];
        }
        list($startDelim, $endDelim) = $options['delimiters'];

        // 构建正则表达式模式
        $hasEndDelim = $endDelim !== '';

        if ($hasEndDelim) {
            $splitter = '/('.preg_quote($startDelim, '/').'\w+'.preg_quote($endDelim, '/').')/';
            $extracter = '/'.preg_quote($startDelim, '/').'(\w+)'.preg_quote($endDelim, '/').'/';
        } else {
            // 空结束分隔符：匹配到空格或字符串末尾
            $splitter = '/('.preg_quote($startDelim, '/').'\w+)/';
            $extracter = '/'.preg_quote($startDelim, '/').'(\w+)/';
        }

        // 分割模式
        $parts = preg_split($splitter, $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $regexParts = [];
        foreach ($parts as $p) {
            if (preg_match($extracter, $p, $matches)) {
                $name = $matches[1];
                $regexParts[] = "(?P<$name>.+?)";
            } else {
                $regexParts[] = preg_quote($p, '/');
            }
        }

        $expandedPattern = '/^'.implode('', $regexParts).'$/';
        if ($options['case_insensitive']) {
            $expandedPattern .= 'i';
        }

        // 执行匹配
        if (!preg_match($expandedPattern, $string, $matches)) {
            return [];
        }

        // 提取结果
        $result = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $result[$key] = $options['strip_values'] ? trim($value) : $value;
            }
        }

        return $result;
    }
}
