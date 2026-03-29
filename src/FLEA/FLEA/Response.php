<?php

namespace FLEA;

/**
 * HTTP 响应封装
 *
 * 统一 JSON 响应格式，链式调用。
 *
 * 用法：
 *   Response::json(['users' => $users]);
 *   Response::success($data);
 *   Response::error('Not found', 404);
 *   Response::make()->code(201)->header('X-Custom', 'val')->json($data);
 */
class Response
{
    private int    $statusCode = 200;
    private array  $headers    = [];

    private function __construct() {}

    public static function make(): self
    {
        return new self();
    }

    // 链式设置

    public function code(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    // 发送响应

    /**
     * 发送 JSON 响应（原始数据，不包装）
     */
    public function json($data): void
    {
        $this->sendHeaders('application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * 发送文本响应
     */
    public function text(string $content): void
    {
        $this->sendHeaders('text/plain');
        echo $content;
        exit;
    }

    // 快捷方法（统一响应结构）

    /**
     * 成功响应
     * {"code":0,"message":"ok","data":{...}}
     */
    public static function success($data = null, string $message = 'ok', int $httpCode = 200): void
    {
        self::make()->code($httpCode)->json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * 错误响应
     * {"code":<errCode>,"message":"...","data":null}
     */
    public static function error(string $message, int $httpCode = 400, int $errCode = -1): void
    {
        self::make()->code($httpCode)->json([
            'code'    => $errCode,
            'message' => $message,
            'data'    => null,
        ]);
    }

    /**
     * 分页响应
     * {"code":0,"message":"ok","data":{items,total,page,page_size}}
     */
    public static function paginate(array $items, int $total, int $page, int $pageSize): void
    {
        self::success([
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 直接输出 JSON（最简用法）
     */
    public static function send($data, int $code = 200): void
    {
        self::make()->code($code)->json($data);
    }

    // 内部

    private function sendHeaders(string $contentType): void
    {
        if (headers_sent()) { return; }
        http_response_code($this->statusCode);
        header("Content-Type: {$contentType}; charset=utf-8");

        // 附加 traceId
        if (\FLEA::isRegistered(\FLEA\Log::class)) {
            header('X-Trace-Id: ' . \FLEA::registry(\FLEA\Log::class)->getTraceId());
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
