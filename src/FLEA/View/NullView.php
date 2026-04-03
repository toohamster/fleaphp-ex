<?php

namespace FLEA\View;

/**
 * 空视图实现
 *
 * 空对象模式，避免空指针判断
 * 用于不需要视图内容的场景（如 204 No Content）
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class NullView implements ViewInterface
{
    /**
     * @var string 内容类型
     */
    private string $contentType = 'text/html';

    /**
     * 构造函数
     *
     * @param string $contentType 内容类型
     */
    public function __construct(string $contentType = 'text/html')
    {
        $this->contentType = $contentType;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * 获取内容（空字符串）
     *
     * @return string
     */
    public function getContent(): string
    {
        return '';
    }
}
