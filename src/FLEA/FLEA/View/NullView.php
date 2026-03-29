<?php

namespace FLEA\View;

/**
 * 空视图实现
 *
 * 实现 ViewInterface 接口，但所有方法都是空实现。
 * 当使用 PHP 原生模板时，数据通过 extract() 传递，不需要视图对象。
 *
 * 用途：
 * - 作为空对象模式，避免空指针判断
 * - 用于不需要视图引擎的场景（纯 PHP 模板）
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\View\ViewInterface
 */
class NullView implements ViewInterface
{
    /**
     * 为视图分配变量（空实现）
     *
     * 当使用 PHP 原生模板时，数据通过 extract() 传递，
     * 不需要通过视图对象分配变量。
     *
     * @param string|array $key   变量名或变量数组
     * @param mixed        $value 变量值
     *
     * @return void
     */
    public function assign($key, $value = null): void
    {
        // 空实现 - 当使用 PHP 原生模板时，数据通过 extract() 传递
    }

    /**
     * 渲染并显示视图（空实现）
     *
     * 当使用 PHP 原生模板时，直接使用 include() 加载模板文件，
     * 不需要通过视图对象渲染。
     *
     * @param string $template 模板文件路径
     *
     * @return void
     */
    public function display(string $template): void
    {
        // 空实现 - 当使用 PHP 原生模板时，直接使用 include()
    }

    /**
     * 渲染视图并返回内容（空实现）
     *
     * @param string      $template 模板文件路径
     * @param string|null $cacheId  缓存 ID
     *
     * @return string 空字符串
     */
    public function fetch(string $template, ?string $cacheId = null): string
    {
        // 空实现 - 返回空字符串
        return '';
    }
}
