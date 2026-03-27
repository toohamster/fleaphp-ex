<?php

namespace FLEA\View;

class NullView implements ViewInterface
{
    /**
     * 为视图分配变量（空实现）
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function assign($key, $value = null): void
    {
        // 空实现 - 当使用 PHP 原生模板时，数据通过 extract() 传递
    }

    /**
     * 渲染并显示视图（空实现）
     * @param string $template
     * @return void
     */
    public function display(string $template): void
    {
        // 空实现 - 当使用 PHP 原生模板时，直接使用 include()
    }

    /**
     * 渲染视图并返回内容（空实现）
     * @param string $template
     * @param string|null $cacheId
     * @return string
     */
    public function fetch(string $template, ?string $cacheId = null): string
    {
        // 空实现 - 返回空字符串
        return '';
    }
}
