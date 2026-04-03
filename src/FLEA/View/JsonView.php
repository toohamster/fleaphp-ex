<?php

namespace FLEA\View;

/**
 * JSON 数据视图
 *
 * 用于 API 响应，自动序列化数据为 JSON 格式
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class JsonView implements ViewInterface
{
    /**
     * @var mixed 要编码的数据
     */
    private $data;

    /**
     * @var int HTTP 状态码
     */
    private int $statusCode;

    /**
     * 构造函数
     *
     * @param mixed $data 要编码的数据
     * @param int $statusCode HTTP 状态码
     */
    public function __construct($data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * 获取 JSON 内容
     *
     * @return string
     */
    public function getContent(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
