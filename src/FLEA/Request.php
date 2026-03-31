<?php

namespace FLEA;

/**
 * HTTP 请求封装
 *
 * 自动解析 JSON body，统一访问请求数据。
 * 提供链式 API 用于获取请求参数、Header、认证信息等。
 *
 * 用法示例：
 * ```php
 * // 获取当前请求实例
 * $req = \FLEA\Request::current();
 *
 * // 从 GET/POST/JSON body 取值（优先级：JSON > POST > GET）
 * $name = $req->input('name');
 *
 * // 只从 JSON body 取值
 * $key = $req->json('key');
 *
 * // 只从 $_GET 取值
 * $page = $req->get('page', 1);
 *
 * // 只从 $_POST 取值
 * $email = $req->post('email');
 *
 * // 路由路径参数
 * $id = $req->param('id');
 *
 * // 请求方法
 * $req->method();     // 'GET', 'POST', 'PUT', 'DELETE'
 * $req->isJson();    // Content-Type 是否为 JSON
 * $req->isAjax();    // 是否为 Ajax 请求
 *
 * // Bearer Token
 * $token = $req->bearerToken();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Request
{
    /**
     * @var ?self Request 单例实例
     */
    private static ?self $instance = null;

    /**
     * @var array 解析后的 JSON body 数据
     */
    private array $jsonBody  = [];

    /**
     * @var bool JSON body 是否已解析
     */
    private bool  $jsonParsed = false;

    /**
     * 构造函数（私有）
     */
    private function __construct() {}

    /**
     * 获取当前请求实例（单例）
     *
     * 用法示例：
     * ```php
     * $req = \FLEA\Request::current();
     * $name = $req->input('name');
     * ```
     *
     * @return self Request 实例
     */
    public static function current(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // -------------------------------------------------------------------------
    // 请求方法
    // -------------------------------------------------------------------------

    /**
     * 获取请求方法
     *
     * 支持 POST + _method 伪装为 PUT/DELETE。
     *
     * @return string 请求方法（'GET', 'POST', 'PUT', 'DELETE' 等）
     */
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $method;
    }

    /**
     * 判断是否为 GET 请求
     *
     * @return bool
     */
    public function isGet(): bool    { return $this->method() === 'GET'; }

    /**
     * 判断是否为 POST 请求
     *
     * @return bool
     */
    public function isPost(): bool   { return $this->method() === 'POST'; }

    /**
     * 判断是否为 PUT 请求
     *
     * @return bool
     */
    public function isPut(): bool    { return $this->method() === 'PUT'; }

    /**
     * 判断是否为 DELETE 请求
     *
     * @return bool
     */
    public function isDelete(): bool { return $this->method() === 'DELETE'; }

    /**
     * 判断是否为 Ajax 请求
     *
     * 检查 X-Requested-With 头是否为 XMLHttpRequest。
     *
     * @return bool
     */
    public function isAjax(): bool   { return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'; }

    /**
     * 判断是否为 JSON 请求
     *
     * 检查 Content-Type 头是否包含 application/json。
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return mb_str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    // -------------------------------------------------------------------------
    // 数据获取
    // -------------------------------------------------------------------------

    /**
     * 从 GET/POST/JSON body 按优先级取值
     *
     * 优先级顺序：JSON body > $_POST > $_GET
     *
     * 用法示例：
     * ```php
     * // 获取参数（自动从 JSON、POST 或 GET 中查找）
     * $name = $req->input('name');
     *
     * // 带默认值
     * $page = $req->input('page', 1);
     * ```
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值
     */
    public function input(string $key, $default = null)
    {
        return $this->json($key)
            ?? $_POST[$key]
            ?? $_GET[$key]
            ?? $default;
    }

    /**
     * 从 JSON body 取值
     *
     * 非 JSON 请求时返回 null。
     *
     * 用法示例：
     * ```php
     * // 获取 JSON 字段
     * $key = $req->json('key');
     *
     * // 带默认值
     * $status = $req->json('status', 'pending');
     *
     * // 获取所有 JSON 数据
     * $all = $req->json();
     * ```
     *
     * @param string|null $key     参数名（省略时返回整个 JSON 对象）
     * @param mixed       $default 默认值
     *
     * @return mixed 参数值或整个 JSON 对象
     */
    public function json(string $key = null, $default = null)
    {
        $body = $this->parseJsonBody();
        if ($key === null) { return $body; }
        return $body[$key] ?? $default;
    }

    /**
     * 从 $_GET 取值
     *
     * 用法示例：
     * ```php
     * $page = $req->get('page', 1);
     * ```
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * 从 $_POST 取值
     *
     * 用法示例：
     * ```php
     * $email = $req->post('email');
     * ```
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值
     */
    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * 获取路由路径参数
     *
     * 路由参数由 Router 注入到 $_GET 中。
     *
     * 用法示例：
     * ```php
     * // 路由：/users/{id}
     * $id = $req->param('id');
     * ```
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值
     */
    public function param(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取所有输入数据（GET + POST + JSON body 合并）
     *
     * @return array 所有输入数据的数组
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST, $this->parseJsonBody());
    }

    // -------------------------------------------------------------------------
    // 请求头 / 认证
    // -------------------------------------------------------------------------

    /**
     * 获取请求头
     *
     * 自动转换头名称格式：Content-Type → HTTP_CONTENT_TYPE
     *
     * 用法示例：
     * ```php
     * $contentType = $req->header('Content-Type');
     * $token = $req->header('Authorization');
     * ```
     *
     * @param string $name    请求头名称
     * @param mixed  $default 默认值
     *
     * @return string|null 请求头值
     */
    public function header(string $name, $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    /**
     * 获取 Bearer Token
     *
     * 从 Authorization 头提取 Bearer token。
     *
     * 用法示例：
     * ```php
     * // Authorization: Bearer abc123
     * $token = $req->bearerToken();
     * // 返回：abc123
     * ```
     *
     * @return string|null Token 值，无则返回 null
     */
    public function bearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (mb_str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    /**
     * 获取客户端 IP 地址
     *
     * 优先级：X-Forwarded-For > X-Real-IP > REMOTE_ADDR
     *
     * @return string IP 地址
     */
    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';
    }

    /**
     * 获取请求 URI
     *
     * @return string 请求 URI
     */
    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    // -------------------------------------------------------------------------
    // 内部
    // -------------------------------------------------------------------------

    /**
     * 解析 JSON body
     *
     * 从 php://input 读取原始 POST 数据并解析为数组。
     * 结果会被缓存，避免重复解析。
     *
     * @return array 解析后的 JSON 数据
     */
    private function parseJsonBody(): array
    {
        if ($this->jsonParsed) { return $this->jsonBody; }
        $this->jsonParsed = true;

        if (!$this->isJson()) { return []; }

        $raw = file_get_contents('php://input');
        if (!$raw) { return []; }

        $data = json_decode($raw, true);
        $this->jsonBody = is_array($data) ? $data : [];
        return $this->jsonBody;
    }
}
