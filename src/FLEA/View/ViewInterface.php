<?php

namespace FLEA\View;

/**
 * 视图顶层接口
 *
 * 定义所有视图必须实现的基本方法
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
interface ViewInterface
{
    /**
     * 获取内容类型
     *
     * @return string 如 'text/html', 'application/json', 'text/csv'
     */
    public function getContentType(): string;

    /**
     * 获取内容字符串
     *
     * @return string 内容（可以是文本或二进制数据）
     *                 对于重定向视图，返回空字符串（HTTP 重定向无 body）
     */
    public function getContent(): string;
}
