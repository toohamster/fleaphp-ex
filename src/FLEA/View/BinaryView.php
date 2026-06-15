<?php

namespace FLEA\View;

/**
 * 二进制文件视图
 *
 * 用于下载二进制文件（PDF、Excel、图片等）
 * 支持流式输出大文件，避免内存溢出
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class BinaryView implements ViewInterface
{
    /**
     * @var string 文件路径
     */
    private string $filePath;

    /**
     * @var string 下载文件名
     */
    private string $filename;

    /**
     * @var string MIME 类型
     */
    private string $mimeType;

    /**
     * 构造函数
     *
     * @param string $filePath 文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型
     */
    public function __construct(
        string $filePath,
        string $filename,
        string $mimeType
    ) {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->mimeType;
    }

    /**
     * 获取文件内容
     *
     * @return string|resource 小文件返回 string，大文件返回 resource
     */
    public function getContent()
    {
        $size = filesize($this->filePath);

        // 小文件（< 1MB）直接读取为 string
        if ($size < 1024 * 1024) {
            return file_get_contents($this->filePath);
        }

        // 大文件返回流资源，由 Response 流式输出
        return fopen($this->filePath, 'rb');
    }

    /**
     * 获取下载文件名
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
