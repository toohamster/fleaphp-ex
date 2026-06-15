<?php

namespace FLEA\View;

/**
 * 文件模板视图
 *
 * 用于渲染任意类型的模板文件（HTML、XML、Markdown、专有 JSON 格式等）
 * 不局限于 HTML，可以是任何需要模板文件生成的内容
 *
 * 依赖 SimpleRenderer 进行实际渲染
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class FileTemplateView implements ViewInterface
{
    /**
     * @var string|null 模板文件路径
     */
    private ?string $template = null;

    /**
     * @var array 视图变量
     */
    private array $vars = [];

    /**
     * @var string 内容类型
     */
    private string $contentType = 'text/html';

    /**
     * @var RendererConfig|null 渲染器配置
     */
    private ?RendererConfig $rendererConfig = null;

    /**
     * 构造函数
     *
     * @param string|null $template 模板文件路径
     * @param array $vars 视图变量
     * @param string $contentType 内容类型（默认 text/html）
     * @param RendererConfig|null $rendererConfig 渲染器配置
     */
    public function __construct(
        ?string $template = null,
        array $vars = [],
        string $contentType = 'text/html',
        ?RendererConfig $rendererConfig = null
    ) {
        $this->template = $template;
        $this->vars = $vars;
        $this->contentType = $contentType;
        $this->rendererConfig = $rendererConfig;
    }

    /**
     * 设置模板文件
     *
     * @param string $template 模板文件路径
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * 分配视图变量
     *
     * @param string|array $key 变量名或变量数组
     * @param mixed $value 变量值
     * @return self
     */
    public function assign($key, $value = null): self
    {
        if (is_array($key)) {
            $this->vars = array_merge($this->vars, $key);
        } else {
            $this->vars[$key] = $value;
        }
        return $this;
    }

    /**
     * 设置渲染器配置
     *
     * @param RendererConfig $config 渲染器配置
     * @return self
     */
    public function setRendererConfig(RendererConfig $config): self
    {
        $this->rendererConfig = $config;
        return $this;
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
     * 获取渲染后的内容
     *
     * 委托给 SimpleRenderer 进行实际渲染
     *
     * @return string
     * @throws \RuntimeException 当模板未设置时抛出异常
     */
    public function getContent(): string
    {
        if ($this->template === null) {
            throw new \RuntimeException('Template not set');
        }

        return SimpleRenderer::render($this->template, $this->vars, $this->rendererConfig);
    }
}
