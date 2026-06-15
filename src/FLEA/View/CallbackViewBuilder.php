<?php

namespace FLEA\View;

/**
 * CallbackView 的链式构建器
 *
 * 提供更优雅的 API 来创建 CallbackView
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class CallbackViewBuilder
{
    /**
     * @var mixed 用户数据
     */
    private $data;

    /**
     * @var string 内容类型
     */
    private string $contentType;

    /**
     * @var callable 回调函数
     */
    private $callback;

    /**
     * 设置数据类型
     *
     * @param string $contentType 内容类型
     * @return self
     */
    public function type(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * 设置回调函数
     *
     * @param callable $callback 回调函数
     * @return self
     */
    public function handler(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * 构建视图
     *
     * @param mixed $data 用户数据
     * @return CallbackView
     */
    public function toView($data): CallbackView
    {
        return new CallbackView($data, $this->contentType, $this->callback);
    }
}
