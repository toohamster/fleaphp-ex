<?php

namespace FLEA;

/**
 * HTTP 请求封装
 *
 * 自动解析 JSON body，统一访问请求数据。
 *
 * 用法：
 *   $req = Request::current();
 *   $req->input('name');          // 从 GET/POST/JSON body 取值
 *   $req->json('key');            // 只从 JSON body 取值
 *   $req->get('page', 1);         // 只从 $_GET 取值
 *   $req->post('email');          // 只从 $_POST 取值
 *   $req->param('id');            // 路由路径参数
 *   $req->method();               // GET POST PUT DELETE ...
 *   $req->isJson();               // Content-Type 是否为 JSON
 *   $req->bearerToken();          // Authorization: Bearer xxx
 */
class Request
{
    private static ?self $instance = null;

    private array $jsonBody  = [];
    private bool  $jsonParsed = false;

    private function __construct() {}

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

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $method;
    }

    public function isGet(): bool    { return $this->method() === 'GET'; }
    public function isPost(): bool   { return $this->method() === 'POST'; }
    public function isPut(): bool    { return $this->method() === 'PUT'; }
    public function isDelete(): bool { return $this->method() === 'DELETE'; }
    public function isAjax(): bool   { return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'; }

    public function isJson(): bool
    {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    // -------------------------------------------------------------------------
    // 数据获取
    // -------------------------------------------------------------------------

    /**
     * 从 GET / POST / JSON body 按优先级取值
     */
    public function input(string $key, $default = null)
    {
        return $this->json($key)
            ?? $_POST[$key]
            ?? $_GET[$key]
            ?? $default;
    }

    /**
     * 从 JSON body 取值，非 JSON 请求返回 null
     */
    public function json(string $key = null, $default = null)
    {
        $body = $this->parseJsonBody();
        if ($key === null) { return $body; }
        return $body[$key] ?? $default;
    }

    /**
     * 从 $_GET 取值
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * 从 $_POST 取值
     */
    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * 路由路径参数（由 Router 注入到 $_GET）
     */
    public function param(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取所有输入（GET + POST + JSON body 合并）
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST, $this->parseJsonBody());
    }

    // -------------------------------------------------------------------------
    // 请求头 / 认证
    // -------------------------------------------------------------------------

    public function header(string $name, $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    /**
     * 提取 Authorization: Bearer <token> 中的 token
     */
    public function bearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';
    }

    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    // -------------------------------------------------------------------------
    // 内部
    // -------------------------------------------------------------------------

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
