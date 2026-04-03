<?php

namespace FLEA;

use FLEA\View\ViewInterface;
use FLEA\View\StreamingViewInterface;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\JsonView;

/**
 * HTTP 响应封装
 *
 * 统一 JSON 响应格式，支持链式调用。
 *
 * 主要功能：
 * - 链式设置：code()、header() 方法链式调用
 * - JSON 响应：json() 发送 JSON 数据
 * - 统一格式：success()、error()、paginate() 统一响应结构
 * - View 支持：fromView() 根据 ViewInterface 发送响应
 *
 * 用法示例：
 * ```php
 * // 链式调用
 * Response::make()->code(201)->header('X-Custom', 'val')->json($data);
 *
 * // 成功响应
 * Response::success($data);
 * // {"code":0,"message":"ok","data":{...}}
 *
 * // 错误响应
 * Response::error('Not found', 404);
 * // {"code":-1,"message":"Not found","data":null}
 *
 * // 分页响应
 * Response::paginate($items, $total, $page, $pageSize);
 *
 * // 从 View 创建响应
 * Response::fromView($view)->send();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class Response
{
    /**
     * @var int HTTP 状态码
     */
    private int    $statusCode = 200;

    /**
     * @var array 自定义响应头
     */
    private array  $headers    = [];

    /**
     * @var ViewInterface|null 视图对象
     */
    private ?ViewInterface $view = null;

    /**
     * 构造函数（私有）
     */
    private function __construct() {}

    /**
     * 创建 Response 实例
     *
     * 用法示例：
     * ```php
     * Response::make()->code(201)->json($data);
     * ```
     *
     * @return self Response 实例
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * 从 View 创建 Response
     *
     * @param ViewInterface $view 视图对象
     * @return self Response 实例
     */
    public static function fromView(ViewInterface $view): self
    {
        $response = new self();
        $response->view = $view;
        return $response;
    }

    // 链式设置

    /**
     * 设置 HTTP 状态码
     *
     * 用法示例：
     * ```php
     * Response::make()->code(201)->json($data);
     * ```
     *
     * @param int $code HTTP 状态码
     *
     * @return self 返回自身实例（链式调用）
     */
    public function code(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * 设置响应头
     *
     * 用法示例：
     * ```php
     * Response::make()->header('X-Custom', 'value')->json($data);
     * ```
     *
     * @param string $name  响应头名称
     * @param string $value 响应头值
     *
     * @return self 返回自身实例（链式调用）
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    // 发送响应

    /**
     * 发送响应
     *
     * 根据 View 类型发送响应，或输出已设置的数据
     *
     * @return void
     */
    public function send(): void
    {
        if ($this->view !== null) {
            $this->sendFromView();
            return;
        }

        // 兼容旧代码：如果没有 view，使用原来的 json/text 方法
        // 需要调用 json() 或 text() 来实际输出
    }

    /**
     * 根据 View 发送响应
     *
     * @return void
     */
    private function sendFromView(): void
    {
        // 流式视图（SSE、实时推送等）
        if ($this->view instanceof StreamingViewInterface) {
            $this->view->stream();
            return;
        }

        // 重定向视图
        if ($this->view instanceof RedirectView) {
            http_response_code($this->view->getStatusCode());
            header("Location: " . $this->view->getUrl());
            return;
        }

        // 设置 Content-Type
        header("Content-Type: " . $this->view->getContentType() . "; charset=utf-8");

        // CSV 和 BinaryView 需要下载头
        if ($this->view instanceof CsvView || $this->view instanceof BinaryView) {
            header("Content-Disposition: attachment; filename=\"" . $this->view->getFilename() . "\"");
        }

        // JSON 视图设置状态码
        if ($this->view instanceof JsonView) {
            http_response_code($this->view->getStatusCode());
        }

        // BinaryView 支持流式输出
        if ($this->view instanceof BinaryView) {
            $content = $this->view->getContent();
            if (is_resource($content)) {
                // 大文件流式输出
                fpassthru($content);
                fclose($content);
                return;
            }
            // 小文件直接输出
            echo $content;
            return;
        }

        // 输出内容
        echo $this->view->getContent();
    }

    /**
     * 发送 JSON 响应
     *
     * 输出 JSON 格式数据并终止程序。
     * 自动设置 Content-Type 为 application/json。
     *
     * 用法示例：
     * ```php
     * Response::make()->json(['users' => $users]);
     * ```
     *
     * @param mixed $data 要输出的数据
     *
     * @return void
     */
    public function json($data): void
    {
        $this->sendHeaders('application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * 发送文本响应
     *
     * 输出纯文本内容并终止程序。
     * 自动设置 Content-Type 为 text/plain。
     *
     * 用法示例：
     * ```php
     * Response::make()->text('Hello World');
     * ```
     *
     * @param string $content 要输出的文本内容
     *
     * @return void
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
     *
     * 输出统一的 JSON 响应格式：
     * ```json
     * {"code":0,"message":"ok","data":{...}}
     * ```
     *
     * 用法示例：
     * ```php
     * Response::success($data);
     * Response::success($data, '操作成功');
     * Response::success(null, '创建成功', 201);
     * ```
     *
     * @param mixed  $data      响应数据
     * @param string $message   响应消息（默认 'ok'）
     * @param int    $httpCode  HTTP 状态码（默认 200）
     *
     * @return void
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
     *
     * 输出统一的 JSON 错误响应格式：
     * ```json
     * {"code":<errCode>,"message":"...","data":null}
     * ```
     *
     * 用法示例：
     * ```php
     * Response::error('Not found', 404);
     * Response::error('参数错误', 400, 1001);
     * ```
     *
     * @param string $message   错误消息
     * @param int    $httpCode  HTTP 状态码（默认 400）
     * @param int    $errCode   业务错误码（默认 -1）
     *
     * @return void
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
     *
     * 输出统一的分页数据格式：
     * ```json
     * {"code":0,"message":"ok","data":{"items":[...],"total":100,"page":1,"page_size":20}}
     * ```
     *
     * 用法示例：
     * ```php
     * Response::paginate($items, $total, $page, $pageSize);
     * ```
     *
     * @param array $items   当前页数据列表
     * @param int   $total   总记录数
     * @param int   $page    当前页码
     * @param int   $pageSize 每页条数
     *
     * @return void
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
     *
     * 用法示例：
     * ```php
     * Response::send(['status' => 'ok']);
     * Response::send($data, 200);
     * ```
     *
     * @param mixed $data 要输出的数据
     * @param int   $code HTTP 状态码（默认 200）
     *
     * @return void
     */
    public static function doSend($data, int $code = 200): void
    {
        self::make()->code($code)->json($data);
    }

    // 内部

    /**
     * 发送响应头
     *
     * 设置 HTTP 状态码、Content-Type 和自定义头。
     * X-Trace-Id 由 FLEA::init() 统一输出。
     *
     * @param string $contentType Content-Type 值
     *
     * @return void
     */
    private function sendHeaders(string $contentType): void
    {
        if (headers_sent()) { return; }
        http_response_code($this->statusCode);
        header("Content-Type: {$contentType}; charset=utf-8");

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
