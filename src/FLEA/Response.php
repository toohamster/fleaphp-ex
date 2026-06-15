<?php

namespace FLEA;

use FLEA\Internal\Signal;
use FLEA\View\ViewInterface;
use FLEA\View\StreamingViewInterface;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\JsonView;

/**
 * HTTP 响应包装器
 *
 * 包装 ViewInterface，处理响应头、状态码，提供发送方法。
 * send() 方法受 Signal 控制，只有收到 'response.send' 信号后才能发送。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
 */
class Response
{
    /**
     * @var ViewInterface 视图对象
     */
    private $view;

    /**
     * @var int HTTP 状态码
     */
    private $statusCode = 200;

    /**
     * @var array 自定义响应头
     */
    private $headers = [];

    /**
     * @var bool 是否允许发送
     */
    private $canSend = false;

    /**
     * 构造函数（私有）
     *
     * @param ViewInterface $view 视图对象
     */
    private function __construct(ViewInterface $view)
    {
        $this->view = $view;

        // 订阅"允许发送"信号
        Signal::subscribe('response.send', function () {
            $this->canSend = true;
        });
    }

    /**
     * 从 View 创建 Response
     *
     * @param ViewInterface $view 视图对象
     * @return self
     */
    public static function fromView(ViewInterface $view)
    {
        return new self($view);
    }

    /**
     * 错误响应
     *
     * @param string $message 错误消息
     * @param int $httpCode HTTP 状态码（默认 400）
     * @param int $errCode 业务错误码（默认 -1）
     * @return self
     */
    public static function error($message, $httpCode = 400, $errCode = -1)
    {
        return new self(View::json([
            'code'    => $errCode,
            'message' => $message,
            'data'    => null,
        ], $httpCode));
    }

    /**
     * 成功响应
     *
     * @param mixed $data 响应数据
     * @param string $message 响应消息（默认 'ok'）
     * @param int $httpCode HTTP 状态码（默认 200）
     * @return self
     */
    public static function success($data = null, $message = 'ok', $httpCode = 200)
    {
        return new self(View::json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], $httpCode));
    }

    /**
     * 分页响应
     *
     * @param array $items 当前页数据列表
     * @param int $total 总记录数
     * @param int $page 当前页码
     * @param int $pageSize 每页条数
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
     * @param string $name 响应头名称
     * @param string $value 响应头值
     * @return self
     */
    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
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
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * 获取 View 对象
     *
     * @return ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 获取状态码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取响应头
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 发送响应
     *
     * 注意：
     * 1. 此方法不调用 exit，由调用者决定何时终止
     * 2. 只有在收到 'response.send' 信号后才能发送
     * 3. 这样设计是为了：
     *    - 防止中间件中途发送响应
     *    - 支持协程/多线程环境（exit 会终止整个进程）
     *    - 支持测试时抓取输出内容
     *    - 支持中间件后置逻辑执行
     *
     * @throws \RuntimeException 如果在未收到信号时调用
     */
    public function send()
    {
        if (!$this->canSend) {
            throw new \RuntimeException(
                'Response::send() can only be called after FLEA::run() publishes "response.send" signal. ' .
                'In middleware, return Response instead of calling send().'
            );
        }

        // 流式视图
        if ($this->view instanceof StreamingViewInterface) {
            $this->view->stream();
            return;
        }

        // 重定向
        if ($this->view instanceof RedirectView) {
            http_response_code($this->view->getStatusCode());
            header('Location: ' . $this->view->getUrl());
            return;
        }

        // 设置状态码
        http_response_code($this->statusCode);

        // 设置自定义响应头
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Content-Type
        header('Content-Type: ' . $this->view->getContentType() . '; charset=utf-8');

        // 下载头
        if ($this->view instanceof CsvView || $this->view instanceof BinaryView) {
            header('Content-Disposition: attachment; filename="' . $this->view->getFilename() . '"');
        }

        // JSON 状态码
        if ($this->view instanceof JsonView) {
            http_response_code($this->view->getStatusCode());
        }

        // 输出内容
        $content = $this->view->getContent();
        if (is_resource($content)) {
            fpassthru($content);
            fclose($content);
        } else {
            echo $content;
        }
    }
}
