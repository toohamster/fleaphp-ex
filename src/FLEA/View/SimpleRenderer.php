<?php

namespace FLEA\View;

/**
 * 简单 PHP 模板渲染器
 *
 * 专注模板渲染和缓存，使用 extract() + include() 渲染 PHP 原生模板
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class SimpleRenderer
{
    /**
     * @var RendererConfig|null 全局配置
     */
    private static ?RendererConfig $config = null;

    /**
     * 设置全局配置
     *
     * @param RendererConfig $config 配置对象
     * @return void
     */
    public static function configure(RendererConfig $config): void
    {
        self::$config = $config;
    }

    /**
     * 获取全局配置
     *
     * @return RendererConfig|null
     */
    public static function getConfig(): ?RendererConfig
    {
        return self::$config;
    }

    /**
     * 渲染模板
     *
     * @param string $template 模板文件路径（绝对路径或相对路径）
     * @param array $vars 视图变量
     * @param RendererConfig|null $config 临时配置（可选，覆盖全局配置）
     * @return string 渲染后的内容
     */
    public static function render(string $template, array $vars = [], ?RendererConfig $config = null): string
    {
        $config = $config ?? self::$config ?? new RendererConfig();

        // 如果是相对路径，拼接模板目录
        if (!str_starts_with($template, '/')) {
            $template = $config->templateDir . DIRECTORY_SEPARATOR . $template;
        }

        // 缓存处理
        if ($config->enableCache) {
            $cacheFile = self::getCacheFile($template, $config);
            if (self::isCacheValid($cacheFile, $config->cacheLifetime)) {
                return file_get_contents($cacheFile);
            }
        }

        // 渲染模板
        extract($vars);
        ob_start();
        include $template;
        $content = ob_get_clean();

        // 保存缓存
        if ($config->enableCache && isset($cacheFile)) {
            self::saveCache($cacheFile, $content);
        }

        return $content;
    }

    /**
     * 获取缓存文件路径
     *
     * @param string $template 模板文件路径
     * @param RendererConfig $config 渲染器配置
     * @return string 缓存文件路径
     */
    private static function getCacheFile(string $template, RendererConfig $config): string
    {
        $hash = md5($template);
        return $config->cacheDir . DIRECTORY_SEPARATOR . $hash . '.php';
    }

    /**
     * 检查缓存是否有效
     *
     * @param string $cacheFile 缓存文件路径
     * @param int $lifetime 缓存有效期
     * @return bool 缓存是否有效
     */
    private static function isCacheValid(string $cacheFile, int $lifetime): bool
    {
        if (!file_exists($cacheFile)) {
            return false;
        }
        return (time() - filemtime($cacheFile)) < $lifetime;
    }

    /**
     * 保存缓存
     *
     * @param string $cacheFile 缓存文件路径
     * @param string $content 内容
     * @return void
     */
    private static function saveCache(string $cacheFile, string $content): void
    {
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        file_put_contents($cacheFile, $content);
    }
}
