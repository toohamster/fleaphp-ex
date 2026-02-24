<?php

/**
 * FLEA 框架全局函数集合
 *
 * 该文件包含了所有 FLEA 框架使用的全局函数
 * 通过 composer 自动加载机制进行加载
 *
 * @package Core
 * @version 1.0
 */

use FLEA\FLEA;

if (!defined('FLEA_VERSION')) {
    define('FLEA_VERSION', '3.0.0');
}

if (!defined('URL_STANDARD')) {
    define('URL_STANDARD', 1);
}

if (!defined('URL_REWRITE')) {
    define('URL_REWRITE', 2);
}

if (!defined('URL_PATHINFO')) {
    define('URL_PATHINFO', 3);
}

/**
 * 重定向浏览器到指定的 URL
 *
 * @param string $url 要重定向的 URL
 * @param int $delay 延迟多少秒后重定向
 * @param bool $js 是否使用 JavaScript 重定向
 * @param bool $jsWrapped 是否使用 <script> 标签包裹 JavaScript 代码
 * @param bool $return 是否返回 JavaScript 代码而不是直接输出
 * @return ?string
 */
function redirect(string $url, int $delay = 0, bool $js = false, bool $jsWrapped = true, bool $return = false): ?string
{
    if ($js) {
        $html = '';
        if ($jsWrapped) {
            $html .= '<script language="JavaScript" type="text/javascript">';
        }
        if ($delay > 0) {
            $html .= "window.setTimeout(\"location.href = '{$url}';\", {$delay} * 1000);";
        } else {
            $html .= "location.href = '{$url}';";
        }
        if ($jsWrapped) {
            $html .= '</script>';
        }

        if ($return) {
            return $html;
        } else {
            echo $html;
            return null;
        }
    } else {
        if (headers_sent()) {
            echo "<script>location.href='{$url}';</script>";
        } else {
            if ($delay > 0) {
                echo "<meta http-equiv='refresh' content='{$delay};url={$url}'>";
            } else {
                header("Location: {$url}");
            }
        }
        return null;
    }
}

/**
 * 生成 URL 地址
 *
 * @param ?string $controllerName 控制器名称
 * @param ?string $actionName 动作名称
 * @param ?array $params 附加参数
 * @param ?string $anchor 锚点名称
 * @param ?array $options 附加选项
 * @return string
 */
function url(?string $controllerName = null, ?string $actionName = null, ?array $params = null, ?string $anchor = null, ?array $options = null): string
{
    $urlMode = FLEA::getAppInf('urlMode');
    $controllerAccessor = FLEA::getAppInf('controllerAccessor');
    $actionAccessor = FLEA::getAppInf('actionAccessor');

    $url = '';
    if ($urlMode == URL_STANDARD) {
        $url = detect_uri_base() . '?' . $controllerAccessor . '=' . urlencode($controllerName);
        if ($actionName != '') {
            $url .= '&' . $actionAccessor . '=' . urlencode($actionName);
        }
        if (is_array($params) && !empty($params)) {
            $url .= '&' . http_build_query($params);
        }
    } elseif ($urlMode == URL_REWRITE) {
        $url = detect_uri_base();
        if ($controllerName != '') {
            $url .= '/' . urlencode($controllerName);
        }
        if ($actionName != '') {
            $url .= '/' . urlencode($actionName);
        }
        if (is_array($params) && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
    } elseif ($urlMode == URL_PATHINFO) {
        $url = detect_uri_base() . '/' . urlencode($controllerName);
        if ($actionName != '') {
            $url .= '/' . urlencode($actionName);
        }
        if (is_array($params) && !empty($params)) {
            $parameterPairStyle = FLEA::getAppInf('urlParameterPairStyle');
            if ($parameterPairStyle == '/') {
                $url .= '/' . implode('/', $params);
            } else {
                $url .= '?' . http_build_query($params);
            }
        }
    }

    if ($anchor != '') {
        $url .= '#' . $anchor;
    }

    return $url;
}

/**
 * 检测并返回基础 URI
 *
 * @return string
 */
function detect_uri_base(): string
{
    $filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $phpSelf = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

    if ($scriptName != '' && basename($scriptName) == $filename) {
        $baseUrl = dirname($scriptName);
    } elseif ($phpSelf != '' && basename($phpSelf) == $filename) {
        $baseUrl = dirname($phpSelf);
    } else {
        $baseUrl = '';
    }

    if ($baseUrl == '.' || $baseUrl == '') {
        $baseUrl = '/';
    } else {
        $baseUrl = str_replace('\\', '/', $baseUrl);
        $baseUrl = rtrim($baseUrl, '/');
    }

    if (!empty($_SERVER['HTTP_HOST'])) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $baseUrl;
    }

    return $baseUrl;
}

/**
 * 将数组转换为 URL 查询字符串
 *
 * @param array $args 要转换的数组
 * @param string $urlMode URL 模式
 * @param ?string $parameterPairStyle 参数对样式
 * @return string
 */
function encode_url_args(array $args, string $urlMode = URL_STANDARD, ?string $parameterPairStyle = null): string
{
    if ($urlMode == URL_PATHINFO && $parameterPairStyle != null && $parameterPairStyle != '/') {
        $pairs = [];
        foreach ($args as $key => $value) {
            $pairs[] = urlencode($key) . $parameterPairStyle . urlencode($value);
        }
        return implode('/', $pairs);
    }

    return http_build_query($args);
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
 * 显示 JavaScript alert 对话框
 *
 * @param string $message 提示信息
 * @param string $after_action 执行后的操作
 * @param string $url 要跳转的 URL
 * @return void
 */
function js_alert(string $message = '', string $after_action = '', string $url = ''): void
{
    echo '<script language="JavaScript" type="text/javascript">';
    if ($message != '') {
        echo "alert(\"" . addslashes($message) . "\");";
    }
    if ($after_action != '') {
        echo $after_action;
    }
    if ($url != '') {
        echo "location.href='{$url}';";
    }
    echo '</script>';
}

/**
 * 将内容转换为 JavaScript 字符串
 *
 * @param string $content 要转换的内容
 * @return string
 */
function t2js(string $content): string
{
    return str_replace(['\\', '"', "\r", "\n", "'"], ['\\\\', '\\"', '', '\\n', "\\'"], $content);
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
 * 设置异常处理器
 *
 * @param $callback 回调函数
 * @return void
 */
function __SET_EXCEPTION_HANDLER($callback)
{
    set_exception_handler($callback);
}

/**
 * FLEA 框架异常处理器
 *
 * @param FLEA_Exception $ex 异常对象
 * @return void
 */
function __FLEA_EXCEPTION_HANDLER(FLEA_Exception $ex): void
{
    if (FLEA::getAppInf('displayErrors')) {
        print_ex($ex);
    } else {
        log_message($ex->toString(), 'error');
    }
}

/**
 * 输出异常信息
 *
 * @param FLEA_Exception $ex 异常对象
 * @param bool $return 是否返回字符串
 * @return ?string
 */
function print_ex(FLEA_Exception $ex, bool $return = false): ?string
{
    $output = '<pre style="text-align: left; background: #fff; border: 1px solid #ccc; margin: 1em; padding: 1em;">';
    $output .= '<strong>' . h(get_class($ex)) . '</strong>';
    $output .= '<br />Error Code: ' . h($ex->getCode());
    $output .= '<br />Error Message: ' . h($ex->getMessage());
    $output .= '<br />File: ' . h($ex->getFile());
    $output .= '<br />Line: ' . h($ex->getLine());
    $output .= '<hr />';
    $output .= h($ex->getTraceAsString());
    $output .= '</pre>';

    if ($return) {
        return $output;
    } else {
        echo $output;
        return null;
    }
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
 * 获取浮点数时间值
 *
 * @param ?string $time 时间字符串
 * @return float
 */
function microtime_float(?string $time = null): float
{
    if ($time === null) {
        list($usec, $sec) = explode(' ', microtime());
        return (float)$usec + (float)$sec;
    }

    list($usec, $sec) = explode(' ', $time);
    return (float)$usec + (float)$sec;
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

    $message = FLEA::loadCache('errorMessages.' . $errorCode);
    if ($message) {
        return $message;
    }

    $errorFile = FLEA::getAppInf('errorMessagesFile');
    if (file_exists($errorFile)) {
        $messages = include($errorFile);
        if (isset($messages[$errorCode])) {
            FLEA::saveCache('errorMessages.' . $errorCode, $messages[$errorCode]);
            return $messages[$errorCode];
        }
    }

    return "Error code: {$errorCode}";
}

/**
 * 创建目录（包括所有必要的父目录）
 *
 * @param string $dir 目录路径
 * @param int $mode 权限模式
 * @return bool
 */
function mkdirs($dir, $mode = 0777)
{
    if (is_dir($dir)) {
        return true;
    }
    if (!mkdirs(dirname($dir), $mode)) {
        return false;
    }
    return @mkdir($dir, $mode);
}

/**
 * 递归删除目录及其内容
 *
 * @param string $dir 目录路径
 * @return bool
 */
function rmdirs($dir)
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
 * 移除数组中的空值
 *
 * @param array &$arr 数组引用
 * @param bool $trim 是否去除空白字符
 * @return void
 */
function array_remove_empty(array &$arr, bool $trim = true): void
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            array_remove_empty($arr[$key], $trim);
            if (empty($arr[$key])) {
                unset($arr[$key]);
            }
        } else {
            if ($trim) {
                $value = trim($value);
            }
            if ($value === '' || $value === null) {
                unset($arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
    }
}

/**
 * 获取数组中指定列的所有值
 *
 * @param array &$arr 数组引用
 * @param string $col 列名
 * @return array
 */
function array_col_values(array &$arr, string $col): array
{
    $result = [];
    foreach ($arr as $row) {
        if (isset($row[$col])) {
            $result[] = $row[$col];
        }
    }
    return $result;
}

/**
 * 将数组转换为哈希表
 *
 * @param array &$arr 数组引用
 * @param string $keyField 键字段
 * @param ?string $valueField 值字段
 * @return array
 */
function array_to_hashmap(array &$arr, string $keyField, ?string $valueField = null): array
{
    $result = [];
    foreach ($arr as $row) {
        $key = $row[$keyField];
        if ($valueField === null) {
            $result[$key] = $row;
        } else {
            $result[$key] = $row[$valueField];
        }
    }
    return $result;
}

/**
 * 按指定字段对数组进行分组
 *
 * @param array &$arr 数组引用
 * @param string $keyField 键字段
 * @return array
 */
function array_group_by(array &$arr, string $keyField): array
{
    $result = [];
    foreach ($arr as $row) {
        $key = $row[$keyField];
        if (!isset($result[$key])) {
            $result[$key] = [];
        }
        $result[$key][] = $row;
    }
    return $result;
}

/**
 * 将数组转换为树形结构
 *
 * @param array $arr 数组
 * @param string $fid ID 字段名
 * @param string $fparent 父ID字段名
 * @param string $fchildrens 子节点字段名
 * @param bool $returnReferences 是否返回引用
 * @return array
 */
function array_to_tree(array $arr, string $fid, string $fparent = 'parent_id', string $fchildrens = 'childrens', bool $returnReferences = false): array
{
    $pkvRefs = [];
    $result = [];

    foreach ($arr as $offset => $row) {
        $pkv = $row[$fid];
        $pkvRefs[$pkv] =& $arr[$offset];
    }

    foreach ($pkvRefs as $parentId => &$row) {
        if ($parentId != '' && isset($pkvRefs[$row[$fparent]])) {
            $parent =& $pkvRefs[$row[$fparent]];
            $parent[$fchildrens][] =& $row;
        } else {
            $result[] =& $row;
        }
    }

    return $result;
}

/**
 * 将树形结构转换为数组
 *
 * @param array &$node 树节点
 * @param string $fchildrens 子节点字段名
 * @return array
 */
function tree_to_array(array &$node, string $fchildrens = 'childrens'): array
{
    static $counter = 0;

    if (empty($node[$fchildrens])) {
        return [];
    }

    $arr = [];
    foreach ($node[$fchildrens] as &$child) {
        $arr[] =& $child;
        if (isset($child[$fchildrens]) && !empty($child[$fchildrens])) {
            $arr = array_merge($arr, tree_to_array($child, $fchildrens));
        }
    }

    return $arr;
}

/**
 * 按指定列对数组进行排序
 *
 * @param array $array 数组
 * @param string $keyname 键名
 * @param int $sortDirection 排序方向
 * @return array
 */
function array_column_sort(array $array, string $keyname, int $sortDirection = SORT_ASC): array
{
    $sorted = [];
    foreach ($array as $row) {
        $sorted[$row[$keyname]] = $row;
    }

    if ($sortDirection == SORT_ASC) {
        ksort($sorted);
    } else {
        krsort($sorted);
    }

    return array_values($sorted);
}

/**
 * 按多个字段对数组进行排序
 *
 * @param array $rowset 行集合
 * @param array $args 参数
 * @return array
 */
function array_sortby_multifields(array $rowset, array $args): array
{
    if (empty($args)) {
        return $rowset;
    }

    usort($rowset, function($a, $b) use ($args) {
        foreach ($args as $key => $value) {
            if ($a[$key] != $b[$key]) {
                return ($a[$key] > $b[$key]) ? $value : -$value;
            }
        }
        return 0;
    });

    return $rowset;
}

/**
 * 生成下拉列表 HTML
 *
 * @param string $name 名称
 * @param array $arr 选项数组
 * @param mixed $selected 选中的值
 * @param ?string $extra 附加属性
 * @return void
 */
function html_dropdown_list(string $name, array $arr, $selected = null, ?string $extra = null): void
{
    echo '<select name="' . h($name) . '"';
    if ($extra) {
        echo ' ' . $extra;
    }
    echo ">\n";

    foreach ($arr as $value => $text) {
        echo '<option value="' . h($value) . '"';
        if (strval($value) == strval($selected)) {
            echo ' selected="selected"';
        }
        echo '>' . h($text) . "</option>\n";
    }

    echo "</select>\n";
}

/**
 * 生成单选按钮组 HTML
 *
 * @param string $name 名称
 * @param array $arr 选项数组
 * @param mixed $checked 选中的值
 * @param string $separator 分隔符
 * @param ?string $extra 附加属性
 * @return void
 */
function html_radio_group(string $name, array $arr, $checked = null, string $separator = '', ?string $extra = null): void
{
    foreach ($arr as $value => $text) {
        echo '<input type="radio" name="' . h($name) . '" value="' . h($value) . '"';
        if (strval($value) == strval($checked)) {
            echo ' checked="checked"';
        }
        if ($extra) {
            echo ' ' . $extra;
        }
        echo ' /> ' . h($text) . $separator . "\n";
    }
}

/**
 * 生成复选框组 HTML
 *
 * @param string $name 名称
 * @param array $arr 选项数组
 * @param array $selected 选中的值数组
 * @param string $separator 分隔符
 * @param ?string $extra 附加属性
 * @return void
 */
function html_checkbox_group(string $name, array $arr, $selected = [], string $separator = '', ?string $extra = null): void
{
    foreach ($arr as $value => $text) {
        echo '<input type="checkbox" name="' . h($name) . '[]" value="' . h($value) . '"';
        if (in_array(strval($value), $selected)) {
            echo ' checked="checked"';
        }
        if ($extra) {
            echo ' ' . $extra;
        }
        echo ' /> ' . h($text) . $separator . "\n";
    }
}

/**
 * 生成复选框 HTML
 *
 * @param string $name 名称
 * @param int $value 值
 * @param bool $checked 是否选中
 * @param string $label 标签
 * @param ?string $extra 附加属性
 * @return void
 */
function html_checkbox(string $name, int $value = 1, bool $checked = false, string $label = '', ?string $extra = null): void
{
    echo '<input type="checkbox" name="' . h($name) . '" value="' . h($value) . '"';
    if ($checked) {
        echo ' checked="checked"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo ' /> ' . h($label) . "\n";
}

/**
 * 生成文本框 HTML
 *
 * @param string $name 名称
 * @param string $value 值
 * @param ?int $width 宽度
 * @param ?int $maxLength 最大长度
 * @param ?string $extra 附加属性
 * @return void
 */
function html_textbox(string $name, string $value = '', ?int $width = null, ?int $maxLength = null, ?string $extra = null): void
{
    echo '<input type="text" name="' . h($name) . '" value="' . h($value) . '"';
    if ($width) {
        echo ' size="' . h($width) . '"';
    }
    if ($maxLength) {
        echo ' maxlength="' . h($maxLength) . '"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo " />\n";
}

/**
 * 生成密码框 HTML
 *
 * @param string $name 名称
 * @param string $value 值
 * @param ?int $width 宽度
 * @param ?int $maxLength 最大长度
 * @param ?string $extra 附加属性
 * @return void
 */
function html_password(string $name, string $value = '', ?int $width = null, ?int $maxLength = null, ?string $extra = null): void
{
    echo '<input type="password" name="' . h($name) . '" value="' . h($value) . '"';
    if ($width) {
        echo ' size="' . h($width) . '"';
    }
    if ($maxLength) {
        echo ' maxlength="' . h($maxLength) . '"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo " />\n";
}

/**
 * 生成文本域 HTML
 *
 * @param string $name 名称
 * @param string $value 值
 * @param ?int $width 宽度
 * @param ?int $height 高度
 * @param ?string $extra 附加属性
 * @return void
 */
function html_textarea(string $name, string $value = '', ?int $width = null, ?int $height = null, ?string $extra = null): void
{
    echo '<textarea name="' . h($name) . '"';
    if ($width) {
        echo ' cols="' . h($width) . '"';
    }
    if ($height) {
        echo ' rows="' . h($height) . '"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo '>' . h($value) . "</textarea>\n";
}

/**
 * 生成隐藏字段 HTML
 *
 * @param string $name 名称
 * @param string $value 值
 * @param ?string $extra 附加属性
 * @return void
 */
function html_hidden(string $name, string $value = '', ?string $extra = null): void
{
    echo '<input type="hidden" name="' . h($name) . '" value="' . h($value) . '"';
    if ($extra) {
        echo ' ' . $extra;
    }
    echo " />\n";
}

/**
 * 生成文件上传字段 HTML
 *
 * @param string $name 名称
 * @param ?int $width 宽度
 * @param ?string $extra 附加属性
 * @return void
 */
function html_filefield(string $name, ?int $width = null, ?string $extra = null): void
{
    echo '<input type="file" name="' . h($name) . '"';
    if ($width) {
        echo ' size="' . h($width) . '"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo " />\n";
}

/**
 * 生成表单开始标签 HTML
 *
 * @param string $name 名称
 * @param string $action 动作
 * @param string $method 方法
 * @param string $onsubmit 提交时执行的脚本
 * @param ?string $extra 附加属性
 * @return void
 */
function html_form(string $name, string $action, string $method='post', string $onsubmit='', ?string $extra = null): void
{
    echo '<form name="' . h($name) . '" action="' . h($action) . '" method="' . h($method) . '"';
    if ($onsubmit) {
        echo ' onsubmit="' . h($onsubmit) . '"';
    }
    if ($extra) {
        echo ' ' . $extra;
    }
    echo ">\n";
}

/**
 * 生成表单结束标签 HTML
 *
 * @return void
 */
function html_form_close(): void
{
    echo "</form>\n";
}

/**
 * 加载 YAML 配置文件
 *
 * @param string $filename 文件名
 * @param bool $cacheEnabled 是否启用缓存
 * @param mixed $replace 要替换的内容
 * @return mixed
 */
function load_yaml($filename, $cacheEnabled = true, $replace = null)
{
    $cacheKey = 'yaml_' . md5($filename);

    if ($cacheEnabled) {
        $cached = FLEA::loadCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    }

    $content = file_get_contents($filename);
    if ($content === false) {
        return null;
    }

    $yaml = new \FLEA\Helper\Yaml();
    $data = $yaml->parse($content);

    if ($replace !== null) {
        $data = array_replace_recursive($data, $replace);
    }

    if ($cacheEnabled) {
        FLEA::saveCache($cacheKey, $data);
    }

    return $data;
}

/**
 * URI 过滤器
 * 根据应用程序设置 'urlMode' 分析 $_GET 参数
 * 该函数由框架自动调用，应用程序不需要调用该函数
 *
 * @return void
 */
function ___uri_filter()
{
    static $firstTime = true;

    if (!$firstTime) {
        return;
    }
    $firstTime = false;

    $pathinfo = !empty($_SERVER['PATH_INFO']) ?
                $_SERVER['PATH_INFO'] :
                (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

    $parts = explode('/', substr($pathinfo, 1));
    if (isset($parts[0]) && strlen($parts[0])) {
        $_GET[FLEA::getAppInf('controllerAccessor')] = $parts[0];
    }
    if (isset($parts[1]) && strlen($parts[1])) {
        $_GET[FLEA::getAppInf('actionAccessor')] = $parts[1];
    }

    $style = FLEA::getAppInf('urlParameterPairStyle');
    if ($style == '/') {
        for ($i = 2; $i < count($parts); $i += 2) {
            if (isset($parts[$i + 1])) {
                $_GET[$parts[$i]] = $parts[$i + 1];
            }
        }
    } else {
        for ($i = 2; $i < count($parts); $i++) {
            $p = $parts[$i];
            $arr = explode($style, $p);
            if (isset($arr[1])) {
                $_GET[$arr[0]] = $arr[1];
            }
        }
    }

    // 将 $_GET 合并到 $_REQUEST
    $_REQUEST = array_merge($_REQUEST, $_GET);
}

// 调用 URI 过滤器
if (defined('FLEA_VERSION')) {
    ___uri_filter();
}
