<?php

namespace FLEA;

use FLEA\View\ViewInterface;
use FLEA\View\StreamingViewInterface;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\JsonView;

/**
 * HTTP 响应适配器
 *
 * 响应数据容器 + 实际发送逻辑。
 * 不依赖 Signal，只接收指令。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
 */
class HttpResponse
{
    /**
     * @var ViewInterface|null 视图对象
     */
    private $view = null;

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
     * 允许发送（由 Response 门面调用）
     *
     * @return void
     */
    public function allowSend()
    {
        $this->canSend = true;
    }

    /**
     * 设置视图
     *
     * @param ViewInterface $view
     * @return self
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * 添加响应头
     *
     * @param string $name
     * @param string $value
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
     * @param int $statusCode
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
     * @return ViewInterface|null
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
     * @throws \RuntimeException 如果未收到允许发送的指令
     */
    public function send()
    {
        if (!$this->canSend) {
            throw new \RuntimeException(
                'Response can only be sent after FLEA::runMVC() publishes "response.send" signal.'
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
