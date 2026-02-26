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

/**
 * 追加日志记录
 *
 * @param mixed  $msg
 * @param string $level
 * @param string $title
 * @return void
 */
function log_message($msg, $level = 'log', $title = '')
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
            $html .= '<script type="text/javascript">';
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
 * 获得当前请求的 URL 地址
 *
 * 参考 QeePHP 和 Zend Framework 实现。
 *
 * @return string
 */
function detect_uri_base(): string
{
    static $baseuri = null;

    if ($baseuri) { return $baseuri; }
    $filename = basename($_SERVER['SCRIPT_FILENAME']);

    if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
        $url = $_SERVER['SCRIPT_NAME'];
    } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
        $url = $_SERVER['PHP_SELF'];
    } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
        $url = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
    } else {
        // Backtrack up the script_filename to find the portion matching
        // php_self
        $path    = $_SERVER['PHP_SELF'];
        $segs    = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
        $segs    = array_reverse($segs);
        $index   = 0;
        $last    = count($segs);
        $url = '';
        do {
            $seg     = $segs[$index];
            $url = '/' . $seg . $url;
            ++$index;
        } while (($last > $index) && (false !== ($pos = strpos($path, $url))) && (0 != $pos));
    }

    // Does the baseUrl have anything in common with the request_uri?
    if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
        $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $request_uri = $_SERVER['REQUEST_URI'];
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
        $request_uri = $_SERVER['ORIG_PATH_INFO'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $request_uri .= '?' . $_SERVER['QUERY_STRING'];
        }
    } else {
        $request_uri = '';
    }

    if (0 === strpos($request_uri, $url)) {
        // full $url matches
        $baseuri = $url;
        return $baseuri;
    }

    if (0 === strpos($request_uri, dirname($url))) {
        // directory portion of $url matches
        $baseuri = rtrim(dirname($url), '/') . '/';
        return $baseuri;
    }

    if (!strpos($request_uri, basename($url))) {
        // no match whatsoever; set it blank
        return '';
    }

    // If using mod_rewrite or ISAPI_Rewrite strip the script filename
    // out of baseUrl. $pos !== 0 makes sure it is not matching a value
    // from PATH_INFO or QUERY_STRING
    if ((strlen($request_uri) >= strlen($url))
        && ((false !== ($pos = strpos($request_uri, $url))) && ($pos !== 0)))
    {
        $url = substr($request_uri, 0, $pos + strlen($url));
    }

    $baseuri = rtrim($url, '/') . '/';
    return $baseuri;
}

/**
 * 将数组转换为可通过 url 传递的字符串连接
 *
 * 用法：
 * <code>
 * $string = encode_url_args(array('username' => 'dualface', 'mode' => 'md5'));
 * // $string 现在为 username=dualface&mode=md5
 * </code>
 *
 * @param array $args
 * @param enum $urlMode
 * @param string $parameterPairStyle
 *
 * @return string
 */
function encode_url_args(array $args, string $urlMode = URL_STANDARD, ?string $parameterPairStyle = null): string
{
    $str = '';
    switch ($urlMode) {
        case URL_STANDARD:
            if (is_null($parameterPairStyle)) {
                $parameterPairStyle = '=';
            }
            $sc = '&';
            break;
        case URL_PATHINFO:
        case URL_REWRITE:
            if (is_null($parameterPairStyle)) {
                $parameterPairStyle = FLEA::getAppInf('urlParameterPairStyle');
            }
            $sc = '/';
            break;
    }

    foreach ($args as $key => $value) {
        if (is_null($value) || $value === '') { continue; }
        if (is_array($value)) {
            $append = encode_url_args($value, $urlMode);
        } else {
            $append = rawurlencode($key) . $parameterPairStyle . rawurlencode($value);
        }
        if (substr($str, -1) != $sc) {
            $str .= $sc;
        }
        $str .= $append;
    }
    return substr($str, 1);
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
    echo '<script type="text/javascript">';
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
 * 调试和错误处理相关的全局函数
 */

/**
 * FleaPHP 默认的异常处理例程
 *
 * @package Core
 *
 * @param \Throwable $ex
 */
function __FLEA_EXCEPTION_HANDLER(\Throwable $ex): void
{
    if (!FLEA::getAppInf('displayErrors')) { exit; }
    if (FLEA::getAppInf('friendlyErrorsMessage')) {
        $language = FLEA::getAppInf('defaultLanguage');
        $language = preg_replace('/[^a-z0-9\-_]+/i', '', $language);

        $exclass = strtoupper(get_class($ex));
        $template = FLEA_DIR . "/_Errors/{$language}/{$exclass}.php";
        if (!file_exists($template)) {
            $template = FLEA_DIR . "/_Errors/{$language}/FLEA_EXCEPTION.php";
            if (!file_exists($template)) {
                $template = FLEA_DIR . "/_Errors/default/FLEA_EXCEPTION.php";
            }
        }
        include($template);
    } else {
        print_ex($ex);
    }
    exit;
}

/**
 * 打印异常的详细信息
 *
 * @package Core
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
            FLEA::saveCache('errorMessages.' . $errorCode, $messages[$errorCode]);
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
 * @param string $fid 节点ID字段名
 * @param string $parentIdKey 节点父ID字段名
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
 * 载入 YAML 文件，返回分析结果
 *
 * load_yaml() 会自动使用缓存，只有当 YAML 文件被改变后，缓存才会更新。
 *
 * 关于 YAML 的详细信息,请参考 www.yaml.org 。
 *
 * 用法：
 * <code>
 * $data = load_yaml('myData.yaml');
 * </code>
 *
 * 注意：为了安全起见，不要使用 YAML 存储敏感信息，例如密码。
 * 或者将 YAML 文件的扩展名设置为 .yaml.php，并且在每一个 YAML 文件开头添加“exit()”。
 * 例如：
 * <code>
 * # <?php exit(); ?>
 *
 * invoice: 34843
 * date   : 2001-01-23
 * bill-to: &id001
 * ......
 * </code>
 *
 * 这样可以确保即便浏览器直接访问该 .yaml.php 文件，也无法看到内容。
 *
 * @param string $filename
 * @param boolean $cacheEnabled 是否缓存分析内容
 * @param null $replace
 *
 * @return array
 * @throws FLEA\Exception\CacheDisabled
 */
function load_yaml(string $filename, $cacheEnabled = true, $replace = null): array
{
    static $firstTime = true;

    if ($firstTime) {
        require_once FLEA_3RD_DIR . '/Spyc/spyc.php';
        $firstTime = false;
    }

    if ($cacheEnabled) {
        $arr = FLEA::getCache('yaml-' . $filename, filemtime($filename), false);
        if ($arr) { return $arr; }
    }

    $arr = spyc_load_file($filename) ?: $replace;
    if ($cacheEnabled) {
        FLEA::writeCache('yaml-' . $filename, $arr);
    }
    return $arr;
}
