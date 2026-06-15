<?php

namespace FLEA;

use FLEA\View\FileTemplateView;
use FLEA\View\JsonView;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\SseView;
use FLEA\View\CallbackView;
use FLEA\View\CallbackViewBuilder;

/**
 * 视图工厂类
 *
 * 简化常用视图的创建
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class View
{
    /**
     * 创建文件模板视图（通用方法）
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @param string $contentType 内容类型（默认 text/html）
     * @return FileTemplateView
     */
    public static function render(string $template, array $vars = [], string $contentType = 'text/html'): FileTemplateView
    {
        return new FileTemplateView($template, $vars, $contentType);
    }

    /**
     * 创建 HTML 视图
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @return FileTemplateView
     */
    public static function html(string $template, array $vars = []): FileTemplateView
    {
        return new FileTemplateView($template, $vars, 'text/html');
    }

    /**
     * 创建 XML 视图
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @return FileTemplateView
     */
    public static function xml(string $template, array $vars = []): FileTemplateView
    {
        return new FileTemplateView($template, $vars, 'text/xml');
    }

    /**
     * 创建 JSON 视图
     *
     * @param mixed $data 要编码的数据
     * @param int $status HTTP 状态码
     * @return JsonView
     */
    public static function json($data, int $status = 200): JsonView
    {
        return new JsonView($data, $status);
    }

    /**
     * 创建 CSV 视图
     *
     * @param array $rows 数据行
     * @param string $filename 文件名
     * @param string $delimiter 分隔符
     * @param bool $excelCompatible Excel 兼容模式（添加 UTF-8 BOM）
     * @return CsvView
     */
    public static function csv(array $rows, string $filename = 'export.csv', string $delimiter = ',', bool $excelCompatible = false): CsvView
    {
        return new CsvView($rows, $delimiter, $filename, $excelCompatible);
    }

    /**
     * 创建重定向视图
     *
     * @param string $url 重定向 URL
     * @param int $code HTTP 状态码
     * @return RedirectView
     */
    public static function redirect(string $url, int $code = 302): RedirectView
    {
        return new RedirectView($url, $code);
    }

    /**
     * 创建二进制文件视图
     *
     * @param string $filePath 文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型
     * @return BinaryView
     */
    public static function binary(string $filePath, string $filename, string $mimeType): BinaryView
    {
        return new BinaryView($filePath, $filename, $mimeType);
    }

    /**
     * 创建 SSE 视图
     *
     * @param callable $generator Generator 函数
     * @return SseView
     */
    public static function sse(callable $generator): SseView
    {
        return new SseView($generator);
    }

    /**
     * 创建回调视图
     *
     * @param mixed $data 用户数据
     * @param string $contentType 内容类型
     * @param callable $callback 回调函数
     * @return CallbackView
     */
    public static function callback($data, string $contentType, callable $callback): CallbackView
    {
        return new CallbackView($data, $contentType, $callback);
    }

    /**
     * 使用构建器创建回调视图
     *
     * @return CallbackViewBuilder
     */
    public static function build(): CallbackViewBuilder
    {
        return new CallbackViewBuilder();
    }

    /**
     * 创建 PDF 视图（快捷方式）
     *
     * @param string $filePath PDF 文件路径
     * @param string $filename 下载文件名
     * @return BinaryView
     */
    public static function pdf(string $filePath, string $filename = 'document.pdf'): BinaryView
    {
        return new BinaryView($filePath, $filename, 'application/pdf');
    }

    /**
     * 创建 Excel 视图（快捷方式）
     *
     * @param string $filePath Excel 文件路径
     * @param string $filename 下载文件名
     * @return BinaryView
     */
    public static function excel(string $filePath, string $filename = 'data.xlsx'): BinaryView
    {
        return new BinaryView($filePath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * 创建图片视图（快捷方式）
     *
     * @param string $filePath 图片文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型（默认 image/jpeg）
     * @return BinaryView
     */
    public static function image(string $filePath, string $filename = 'image.jpg', string $mimeType = 'image/jpeg'): BinaryView
    {
        return new BinaryView($filePath, $filename, $mimeType);
    }
}
