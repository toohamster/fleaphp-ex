<?php

/**
 * FLEA 框架全局函数集合
 *
 * 该文件包含了所有 FLEA 框架使用的全局函数
 * 通过 composer 自动加载机制进行加载
 */

/**
 * 追加日志记录
 *
 * @param mixed  $msg
 * @param string $level
 * @param string $title
 * @return void
 */
function log_message($msg, $level = \Psr\Log\LogLevel::DEBUG, $title = '')
{
    static $instance = null;

    if (is_null($instance)) {
        $instance = \FLEA::getSingleton(\FLEA\Log::class);
    }

    $message = $title !== '' ? "{$title}:" . print_r($msg, true) : print_r($msg, true);
    $instance->log($level, $message);
}

/**
 * 返回类加载器对象
 *
 * @return \Composer\Autoload\ClassLoader
 */
function class_loader()
{
    static $loader = null;
    if ( is_null($loader) )
    {
        $loader = new \Composer\Autoload\ClassLoader();
        $loader->register();
    }

    return $loader;
}

/**
 * 创建 SQL 语句对象
 *
 * @param \PDOStatement|\FLEA\Db\SqlStatement|string $sql
 * @return \FLEA\Db\SqlStatement
 */
function sql_statement($sql): \FLEA\Db\SqlStatement
{
    return \FLEA\Db\SqlStatement::create($sql);
}

/**
 * 根据命名路由生成 URL
 *
 * @param string $name 路由名称
 * @param array $params 路径参数
 */
function url(string $name, array $params = []): string
{
    return \FLEA\Router::urlFor($name, $params);
}

/**
 * 转义 HTML 特殊字符
 *
 * @param string $text 要转义的文本
 * @return string
 */
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}

/**
 * 转换为安全输出的文本
 *
 * @param string $text 要转换的文本
 * @return string
 */
function t(string $text): string
{
    return nl2br(htmlspecialchars($text, ENT_QUOTES));
}

/**
 * 安全地写入文件
 *
 * @param string $filename 文件名
 * @param string $content 内容
 * @return bool
 */
function safe_file_put_contents(string $filename, string $content): bool
{
    $tmpFile = $filename . '.tmp';
    if (file_put_contents($tmpFile, $content, LOCK_EX)) {
        if (rename($tmpFile, $filename)) {
            @chmod($filename, 0666);
            return true;
        }
    }
    @unlink($tmpFile);
    return false;
}

/**
 * 安全地读取文件
 *
 * @param string $filename 文件名
 * @return ?string
 */
function safe_file_get_contents(string $filename): ?string
{
    if (!is_file($filename)) {
        return null;
    }
    $content = file_get_contents($filename);
    return $content === false ? null : $content;
}

/**
 * 调试和错误处理相关的全局函数
 */

/**
 * FleaPHP 默认的异常处理例程
 *
 * @param \Throwable $ex
 */
function __FLEA_EXCEPTION_HANDLER(\Throwable $ex): void
{
    // JSON 响应：Accept 头包含 application/json 或配置强制 JSON 模式
    $wantsJson = FLEA::getAppInf('forceJsonResponse')
        || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

    if ($wantsJson) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            if (FLEA::getAppInf('logEnabled') && FLEA::isRegistered(\FLEA\Log::class)) {
                header('X-Trace-Id: ' . FLEA::registry(\FLEA\Log::class)->getTraceId());
            }
        }
        $payload = ['error' => $ex->getMessage(), 'code' => $ex->getCode()];
        if (DEBUG_MODE) {
            $payload['exception'] = get_class($ex);
            $payload['file']      = $ex->getFile();
            $payload['line']      = $ex->getLine();
            $payload['trace']     = explode("\n", $ex->getTraceAsString());
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!FLEA::getAppInf('displayErrors')) { exit; }

    if (DEBUG_MODE) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo (new \FLEA\Error\ErrorRenderer($ex))->render();
    } else {
        \FLEA\Error\ErrorRenderer::renderProduction($ex);
    }
    exit;
}

/**
 * 打印异常的详细信息
 *
 * @param \Throwable $ex
 * @param boolean $return 为 true 时返回输出信息，而不是直接显示
 */
function print_ex(\Throwable $ex, bool $return = false): ?string
{
    $out = "exception '" . get_class($ex) . "'";
    if ($ex->getMessage() != '') {
        $out .= " with message '" . $ex->getMessage() . "'";
    }
    if (defined('DEPLOY_MODE') && DEPLOY_MODE != false) {
        $out .= ' in ' . basename($ex->getFile()) . ':' . $ex->getLine() . "\n\n";
    } else {
        $out .= ' in ' . $ex->getFile() . ':' . $ex->getLine() . "\n\n";
        $out .= $ex->getTraceAsString();
    }

    if ($return) { return $out; }

    if (ini_get('html_errors')) {
        echo nl2br(htmlspecialchars($out));
    } else {
        echo $out;
    }

    return '';
}

/**
 * 调试输出变量
 *
 * @param mixed $vars 要输出的变量
 * @param string $label 标签
 * @param bool $return 是否返回字符串
 * @return ?string
 */
function dump($vars, string $label = '', bool $return = false): ?string
{
    if (ini_get('html_errors')) {
        $output = '<pre style="text-align: left; background: #f5f5f5; border: 1px solid #ccc; margin: 1em; padding: 1em;">';
        if ($label != '') {
            $output .= '<strong>' . h($label) . ':</strong> ';
        }
        $output .= h(print_r($vars, true));
        $output .= '</pre>';
    } else {
        $output = '';
        if ($label != '') {
            $output .= $label . ': ';
        }
        $output .= print_r($vars, true) . "\n";
    }

    if ($return) {
        return $output;
    } else {
        echo $output;
        return null;
    }
}

/**
 * 输出调用堆栈
 *
 * @return void
 */
function dump_trace(): void
{
    $output = '<pre style="text-align: left; background: #f5f5f5; border: 1px solid #ccc; margin: 1em; padding: 1em;">';
    $output .= h(print_r(debug_backtrace(), true));
    $output .= '</pre>';
    echo $output;
}

/**
 * 生成随机 traceid（62 进制 5 位）
 *
 * @return string
 */
function generate_traceid(): string
{
    static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num = random_int(0, 2176782335); // 62^5 - 1
    $id = '';
    for ($i = 0; $i < 5; $i++) {
        $id .= $chars[$num % 62];
        $num = intdiv($num, 62);
    }
    return strrev($id);
}

/**
 * 判断字符串是否以指定前缀开头
 *
 * @param string $haystack 被搜索的字符串
 * @param string $needle 前缀字符串
 * @param string $encoding 字符编码，默认 UTF-8
 * @return bool
 */
function str_start_with(string $haystack, string $needle, string $encoding = 'UTF-8'): bool
{
    if ($needle === '') { return true; }
    if (\FLEA\Env::hasMbstring()) {
        return mb_strpos($haystack, $needle, 0, $encoding) === 0;
    }
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * 判断字符串是否以指定后缀结尾
 *
 * @param string $haystack 被搜索的字符串
 * @param string $needle 后缀字符串
 * @param string $encoding 字符编码，默认 UTF-8
 * @return bool
 */
function str_end_with(string $haystack, string $needle, string $encoding = 'UTF-8'): bool
{
    if ($needle === '') { return true; }
    if (\FLEA\Env::hasMbstring()) {
        $len = mb_strlen($needle, $encoding);
        return mb_substr($haystack, -$len, null, $encoding) === $needle;
    }
    $len = strlen($needle);
    return substr($haystack, -$len) === $needle;
}

/**
 * 判断字符串是否包含指定子串
 *
 * @param string $haystack 被搜索的字符串
 * @param string $needle 要查找的子串
 * @param string $encoding 字符编码，默认 UTF-8
 * @return bool
 */
function str_contains(string $haystack, string $needle, string $encoding = 'UTF-8'): bool
{
    if ($needle === '') { return true; }
    if (\FLEA\Env::hasMbstring()) {
        return mb_strpos($haystack, $needle, 0, $encoding) !== false;
    }
    return strpos($haystack, $needle) !== false;
}

/**
 * 获取浮点数时间值
 *
 * @param ?string $time 时间字符串
 * @return float
 */
function microtime_float(?string $time = null): float
{
    if ($time === null) {
        return microtime(true);
    }

    // 处理传入的时间字符串
    $parts = explode(' ', $time);
    return (float)($parts[0] ?? 0) + (float)($parts[1] ?? 0);
}

/**
 * 获取错误消息
 *
 * @param int $errorCode 错误代码
 * @param bool $appError 是否为应用程序错误
 * @return string
 */
function _ET(int $errorCode, bool $appError = false): string
{
    if ($appError) {
        $message = FLEA::getAppInf('errorMessages.' . $errorCode);
        if ($message) {
            return $message;
        }
    }

    $message = FLEA::getCache('errorMessages.' . $errorCode);
    if ($message) {
        return $message;
    }

    $errorFile = FLEA::getAppInf('errorMessagesFile');
    if (file_exists($errorFile)) {
        $messages = include($errorFile);
        if (isset($messages[$errorCode])) {
            FLEA::writeCache('errorMessages.' . $errorCode, $messages[$errorCode]);
            return $messages[$errorCode];
        }
    }

    return "Error code: {$errorCode}";
}

/**
 * 调用 FLEA_Language::get() 获取翻译
 *
 * 用法：
 * <code>
 * $msg = _T('ENGLISH', 'chinese');
 * $msg = sprintf(_T('ENGLISH: %s'), 'chinese');
 * </code>
 *
 * @param string $key
 * @param string $language 指定为 '' 时表示从默认语言包中获取翻译
 *
 * @return string
 * @throws FLEA\Exception\ExpectedClass
 */
function _T(string $key, string $language = ''): string
{
    static $instance = null;
    if (is_null($instance)) {
        $instance = FLEA::getSingleton('\\FLEA\\Language');
    }
    return $instance->get($key, $language);
}

/**
 * 载入语言字典文件
 *
 * @param string $dictname
 * @param string $language 指定为 '' 时表示将字典载入默认语言包中
 * @param boolean $noException
 *
 * @return boolean
 * @throws FLEA\Exception\ExpectedClass
 */
function load_language(string $dictname, string $language = '', bool $noException = false): bool
{
    static $instance = null;
    if (is_null($instance)) {
        $instance = FLEA::getSingleton('\\FLEA\\Language');
    }
    return $instance->load($dictname, $language, $noException);
}

/**
 * 创建目录（包括所有必要的父目录）
 *
 * @param string $dir 目录路径
 * @param int $mode 权限模式
 * @return bool
 */
function mkdirs($dir, $mode = 0777): bool
{
    return is_dir($dir) || (mkdirs(dirname($dir), $mode) && @mkdir($dir, $mode));
}

/**
 * 递归删除目录及其内容
 *
 * @param string $dir 目录路径
 * @return bool
 */
function rmdirs($dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            rmdirs($path);
        } else {
            @unlink($path);
        }
    }

    return @rmdir($dir);
}

/**
 * 从数组中删除空白的元素（包括只有空白字符的元素）
 *
 * @param array $arr
 * @param boolean $trim
 */
function array_remove_empty(array &$arr, bool $trim = true): void
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            array_remove_empty($arr[$key]);
        } else {
            $value = trim($value);
            if ($value == '') {
                unset($arr[$key]);
            } elseif ($trim) {
                $arr[$key] = $value;
            }
        }
    }
}

/**
 * 从一个二维数组中返回指定键的所有值
 *
 * @param array $arr
 * @param string $col
 *
 * @return array
 */
function array_col_values(array $arr, string $col): array
{
    return array_column($arr, $col);
}

/**
 * 将一个二维数组转换为 hashmap
 *
 * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
 *
 * @param array $arr
 * @param string $keyField
 * @param string|null $valueField
 *
 * @return array
 */
function array_to_hashmap(array &$arr, string $keyField, ?string $valueField = null): array
{
    $ret = [];
    if ($valueField) {
        foreach ($arr as $row) {
            $ret[$row[$keyField]] = $row[$valueField];
        }
    } else {
        foreach ($arr as $row) {
            $ret[$row[$keyField]] = $row;
        }
    }
    return $ret;
}

/**
 * 将一个二维数组按照指定字段的值分组
 *
 * @param array $arr
 * @param string $keyField
 *
 * @return array
 */
function array_group_by(array &$arr, string $keyField): array
{
    $ret = [];
    foreach ($arr as $row) {
        if (isset($row[$keyField])) {
            $ret[$row[$keyField]][] = $row;
        }
    }
    return $ret;
}

/**
 * 将一个平面的二维数组按照指定的字段转换为树状结构
 *
 * 当 $returnReferences 参数为 true 时，返回结果的 tree 字段为树，refs 字段则为节点引用。
 * 利用返回的节点引用，可以很方便的获取包含以任意节点为根的子树。
 *
 * @param array $arr 原始数据
 * @param string $fid 节点 ID 字段名
 * @param string $parentIdKey 节点父 ID 字段名
 * @param string $childrenIdKey 保存子节点的字段名
 * @param boolean $returnReferences 是否在返回结果中包含节点引用
 *
 * return array
 */
function array_to_tree(array $arr, string $fid, string $parentIdKey = 'parent_id', string $childrenIdKey = 'children', bool $returnReferences = false): array
{
    $pkvRefs = [];
    foreach ($arr as $offset => $row) {
        $pkvRefs[$row[$fid]] =& $arr[$offset];
    }

    $tree = [];
    foreach ($arr as $offset => $row) {
        $parentId = $row[$parentIdKey];
        if ($parentId) {
            if (!isset($pkvRefs[$parentId])) { continue; }
            $parent =& $pkvRefs[$parentId];
            $parent[$childrenIdKey][] =& $arr[$offset];
        } else {
            $tree[] =& $arr[$offset];
        }
    }
    if ($returnReferences) {
        return ['tree' => $tree, 'refs' => $pkvRefs];
    }

    return $tree;
}

/**
 * 将树转换为平面的数组
 *
 * @param array $node
 * @param string $fchildren
 *
 * @return array
 */
function tree_to_array(array &$node, string $fchildren = 'children'): array
{
    $ret = [];
    if (isset($node[$fchildren]) && is_array($node[$fchildren])) {
        foreach ($node[$fchildren] as $child) {
            $ret = array_merge($ret, tree_to_array($child, $fchildren));
        }
        unset($node[$fchildren]);
        $ret[] = $node;
    } else {
        $ret[] = $node;
    }
    return $ret;
}

/**
 * 根据指定的键值对数组排序
 *
 * @param array $array 要排序的数组
 * @param string $key 键值名称
 * @param int $sort 排序方向
 *
 * @return array
 */
function array_column_sort(array $array, string $key, int $sort = SORT_ASC): array
{
    return array_sortby_multifields($array, [$key => $sort]);
}

/**
 * 将一个二维数组按照指定列进行排序，类似 SQL 语句中的 ORDER BY
 *
 * @param array $rowset
 * @param array $args
 */
function array_sortby_multifields(array $rowset, array $args): array
{
    // 参数验证
    if (empty($rowset) || empty($args)) {
        return $rowset;
    }

    // 构建排序参数数组
    $sortParams = [];
    $firstRow = reset($rowset);

    // 验证字段存在性并构建排序参数
    foreach ($args as $field => $direction) {
        if (!is_array($firstRow) || !array_key_exists($field, $firstRow)) {
            return $rowset;
        }

        // 提取该字段的所有值
        $columnValues = array_column($rowset, $field);
        $sortParams[] = $columnValues;
        $sortParams[] = $direction;
    }

    // 添加主数组引用
    $sortParams[] = &$rowset;

    // 使用 array_multisort 进行排序
    array_multisort(...$sortParams);

    return $rowset;
}
