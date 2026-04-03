<?php

namespace FLEA\View;

/**
 * 重定向视图
 *
 * 用于 HTTP 重定向响应，无内容体
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class RedirectView implements ViewInterface
{
    /**
     * @var string 重定向 URL
     */
    private string $url;

    /**
     * @var int HTTP 状态码
     */
    private int $statusCode;

    /**
     * 构造函数
     *
     * @param string $url 重定向 URL
     * @param int $statusCode HTTP 状态码
     */
    public function __construct(string $url, int $statusCode = 302)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * 获取内容（重定向无内容）
     *
     * @return string
     */
    public function getContent(): string
    {
        return '';
    }

    /**
     * 获取重定向 URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * 获取 HTTP 状态码
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
