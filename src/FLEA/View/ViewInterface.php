<?php

namespace FLEA\View;

/**
 * 视图接口
 *
 * 定义视图引擎必须实现的方法。
 * 所有视图引擎（Simple、Smarty、Twig 等）必须实现此接口。
 *
 * 用法示例：
 * ```php
 * // 实现视图接口
 * class MyView implements \FLEA\View\ViewInterface
 * {
 *     public function assign($key, $value = null): void { }
 *     public function display(string $template): void { }
 *     public function fetch(string $template, ?string $cacheId = null): string { }
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
interface ViewInterface
{
    /**
     * 为视图分配变量
     *
     * 用法示例：
     * ```php
     * // 分配单个变量
     * $view->assign('title', '页面标题');
     *
     * // 分配多个变量
     * $view->assign(['title' => '标题', 'content' => '内容']);
     * ```
     *
     * @param string|array $key   变量名或变量数组
     * @param mixed        $value 变量值（当 $key 为字符串时有效）
     *
     * @return void
     */
    public function assign($key, $value = null): void;

    /**
     * 渲染并显示视图
     *
     * 渲染模板文件并直接输出到浏览器。
     *
     * @param string $template 模板文件路径
     *
     * @return void
     */
    public function display(string $template): void;

    /**
     * 渲染视图并返回内容
     *
     * 渲染模板文件并返回内容（不输出到浏览器）。
     * 支持缓存功能，可通过 $cacheId 指定缓存标识。
     *
     * @param string      $template 模板文件路径
     * @param string|null $cacheId  缓存 ID（可选，用于缓存视图内容）
     *
     * @return string 渲染后的内容
     */
    public function fetch(string $template, ?string $cacheId = null): string;
}
