<?php

namespace FLEA\View;

/**
 * \FLEA\View\ViewInterface 视图引擎接口
 *
 * 定义了视图引擎必须实现的方法
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
interface ViewInterface
{
    /**
     * 为视图分配变量
     *
     * @param string|array $key 变量名或变量数组
     * @param mixed $value 变量值
     *
     * @return void
     */
    public function assign($key, $value = null): void;

    /**
     * 渲染并显示视图
     *
     * @param string $template 模板文件路径
     *
     * @return void
     */
    public function display(string $template): void;

    /**
     * 渲染视图并返回内容（不输出）
     *
     * @param string $template 模板文件路径
     * @param string|null $cacheId 缓存 ID
     *
     * @return string
     */
    public function fetch(string $template, ?string $cacheId = null): string;
}
