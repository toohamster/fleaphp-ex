<?php

namespace FLEA;

use FLEA\Internal\Signal;
use FLEA\View\ViewInterface;

/**
 * HTTP 响应门面（Facade）
 *
 * 统一入口，管理单例、订阅信号、委托给 HttpResponse。
 * 与 Request::current() 对称，提供一致的使用体验。
 *
 * 用法示例：
 * ```php
 * // 获取当前响应实例
 * $res = Response::current();
 *
 * // 链式设置
 * $res->withStatus(401)->setView(View::json(['error' => 'Unauthorized']));
 *
 * // 快捷方法
 * Response::success($data);
 * Response::error('Not found', 404);
 * Response::paginate($items, $total, $page, $pageSize);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
 */
class Response
{
    /**
     * @var self|null 单例实例
     */
    private static $instance = null;

    /**
     * @var HttpResponse 内部适配器
     */
    private $http;

    /**
     * 构造函数（私有）
     */
    private function __construct()
    {
        $this->http = new HttpResponse();

        // 订阅"允许发送"信号
        Signal::subscribe('response.send', function () {
            $this->http->allowSend();
        });
    }

    /**
     * 获取当前响应实例
     *
     * @return self
     */
    public static function current()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 从 View 创建（快捷工厂）
     *
     * @param ViewInterface $view 视图对象
     * @return self
     */
    public static function fromView(ViewInterface $view)
    {
        $instance = self::current();
        $instance->http->setView($view);
        return $instance;
    }

    /**
     * 错误响应（快捷方法）
     *
     * @param mixed $message 错误消息
     * @param int   $httpCode HTTP 状态码（默认 400）
     * @param int   $errCode  业务错误码（默认 -1）
     * @return self
     */
    public static function error($message, $httpCode = 400, $errCode = -1)
    {
        $res = self::current();
        $res->http->setView(View::json([
            'code'    => $errCode,
            'message' => $message,
            'data'    => null,
        ], $httpCode));
        $res->http->withStatus($httpCode);
        return $res;
    }

    /**
     * 成功响应（快捷方法）
     *
     * @param mixed  $data     响应数据
     * @param string $message  响应消息（默认 'ok'）
     * @param int    $httpCode HTTP 状态码（默认 200）
     * @return self
     */
    public static function success($data = null, $message = 'ok', $httpCode = 200)
    {
        $res = self::current();
        $res->http->setView(View::json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], $httpCode));
        $res->http->withStatus($httpCode);
        return $res;
    }

    /**
     * 分页响应（快捷方法）
     *
     * @param array $items    当前页数据列表
     * @param int   $total    总记录数
     * @param int   $page     当前页码
     * @param int   $pageSize 每页条数
     * @return self
     */
    public static function paginate(array $items, $total, $page, $pageSize)
    {
        return self::success([
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 添加响应头
     *
     * @param string $name  响应头名称
     * @param string $value 响应头值
     * @return self
     */
    public function withHeader($name, $value)
    {
        $this->http->withHeader($name, $value);
        return $this;
    }

    /**
     * 设置状态码
     *
     * @param int $statusCode HTTP 状态码
     * @return self
     */
    public function withStatus($statusCode)
    {
        $this->http->withStatus($statusCode);
        return $this;
    }

    /**
     * 设置视图
     *
     * @param ViewInterface $view 视图对象
     * @return self
     */
    public function setView(ViewInterface $view)
    {
        $this->http->setView($view);
        return $this;
    }

    /**
     * 获取 View 对象
     *
     * @return ViewInterface|null
     */
    public function getView()
    {
        return $this->http->getView();
    }

    /**
     * 获取状态码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->http->getStatusCode();
    }

    /**
     * 获取响应头
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->http->getHeaders();
    }

    /**
     * 判断是否有内容
     *
     * @return bool
     */
    public function hasContent()
    {
        return $this->http->getView() !== null;
    }

    /**
     * 发送响应（委托给 HttpResponse）
     *
     * @throws \RuntimeException 如果在未收到信号时调用
     */
    public function send()
    {
        $this->http->send();
    }
}
