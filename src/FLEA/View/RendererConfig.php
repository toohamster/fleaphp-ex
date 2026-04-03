<?php

namespace FLEA\View;

/**
 * 渲染器配置
 *
 * 封装模板渲染的配置项
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class RendererConfig
{
    /**
     * @var string|null 模板文件目录
     */
    public ?string $templateDir = null;

    /**
     * @var string 缓存文件目录
     */
    public string $cacheDir = './cache';

    /**
     * @var int 缓存有效期（秒）
     */
    public int $cacheLifetime = 900;

    /**
     * @var bool 是否启用缓存
     */
    public bool $enableCache = true;

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
