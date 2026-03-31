<?php

namespace FLEA\Helper\FileUploader;

/**
 * 上传文件封装类
 *
 * 封装一个上传的文件，提供文件信息访问、文件检查和移动等功能。
 *
 * 主要功能：
 * - 访问上传文件信息（文件名、类型、大小、临时路径）
 * - 文件检查（类型、大小限制）
 * - 文件移动（保存到新位置）
 * - 删除上传的文件
 *
 * 用法示例：
 * ```php
 * $uploader = new FileUploader();
 * $files = $uploader->getFiles();
 *
 * foreach ($files as $file) {
 *     // 获取文件信息
 *     $name = $file->getFilename();
 *     $size = $file->getSize();
 *     $ext = $file->getExt();
 *
 *     // 检查文件
 *     if (!$file->check('jpg,png,gif', 1024 * 1024)) {
 *         echo "文件检查失败：" . $file->getError();
 *         continue;
 *     }
 *
 *     // 移动文件
 *     $file->move('/path/to/uploads/' . $file->getNewPath());
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class File
{
    /**
     * 上传文件信息
     *
     * @var array
     */
    public array $file = [];

    /**
     * 上传文件对象的名字
     *
     * @var string
     */
    public string $name = '';

    /**
     * 构造函数
     *
     * @param array $struct
     * @param string $name
     * @param int $ix
     */
    public function __construct(array $struct, string $name, $ix = false)
    {
        if ($ix !== false) {
            $s = [
                'name' => $struct['name'][$ix],
                'type' => $struct['type'][$ix],
                'tmp_name' => $struct['tmp_name'][$ix],
                'error' => $struct['error'][$ix],
                'size' => $struct['size'][$ix],
            ];
            $this->file = $s;
        } else {
            $this->file = $struct;
        }

        $this->file['is_moved'] = false;
        $this->name = $name;
    }

    /**
     * 设置自定义属性
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
        $this->file[$name] = $value;
    }

    /**
     * 获取自定义属性
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        return $this->file[$name];
    }

    /**
     * 返回上传文件对象的名字
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 指示上传是否成功
     *
     * @return boolean
     */
    public function isSuccessed(): bool
    {
        return $this->file['error'] == UPLOAD_ERR_OK;
    }

    /**
     * 返回上传错误代码
     *
     * @return int
     */
    public function getError(): int
    {
        return $this->file['error'];
    }

    /**
     * 指示上传文件是否已经从临时目录移出
     *
     * @return boolean
     */
    public function isMoved(): bool
    {
        return $this->file['is_moved'];
    }

    /**
     * 返回上传文件的原名
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->file['name'];
    }

    /**
     * 返回上传文件不带"."的扩展名
     *
     * @return string
     */
    public function getExt(): string
    {
        if ($this->isMoved()) {
            return pathinfo($this->getNewPath(), PATHINFO_EXTENSION);
        } else {
            return pathinfo($this->getFilename(), PATHINFO_EXTENSION);
        }
    }

    /**
     * 返回上传文件的大小（字节数）
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->file['size'];
    }

    /**
     * 返回上传文件的 MIME 类型（由浏览器提供，不可信）
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->file['type'];
    }

    /**
     * 返回上传文件的临时文件名
     *
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->file['tmp_name'];
    }

    /**
     * 获得文件的新路径（通常是移动后的新路径，包括文件名）
     *
     * @return string
     */
    public function getNewPath(): string
    {
        return $this->file['new_path'];
    }

    /**
     * 检查上传的文件是否成功上传，并符合检查条件（文件类型、最大尺寸）
     *
     * 文件类型以扩展名为准，多个扩展名以 , 分割，例如 .jpg,.jpeg,.png。
     *
     * @param string $allowExts 允许的扩展名
     * @param int $maxSize 允许的最大上传字节数
     *
     * @return boolean
     */
    public function check(?string $allowExts = null, ?int $maxSize = null): bool
    {
        if (!$this->isSuccessed()) { return false; }

        if ($allowExts) {
            if (strpos($allowExts, ',')) {
                $exts = explode(',', $allowExts);
            } elseif (strpos($allowExts, '/')) {
                $exts = explode('/', $allowExts);
            } elseif (strpos($allowExts, '|')) {
                $exts = explode('|', $allowExts);
            } else {
                $exts = [$allowExts];
            }

            $filename = $this->getFilename();
            $fileexts = explode('.', $filename);
            array_shift($fileexts);
            $count = count($fileexts);
            $passed = false;
            $exts = array_filter(array_map('trim', $exts), 'trim');
            foreach ($exts as $ext) {
                if (substr($ext, 0, 1) == '.') {
                    $ext = substr($ext, 1);
                }
                $fileExt = implode('.', array_slice($fileexts, $count - count(explode('.', $ext))));
                if (strtolower($fileExt) == strtolower($ext)) {
                    $passed = true;
                    break;
                }
            }
            if (!$passed) {
                return false;
            }
        }

        if ($maxSize && $this->getSize() > $maxSize) {
            return false;
        }

        return true;
    }

    /**
     * 移动上传文件到指定位置和文件名
     *
     * @param string $destPath
     */
    public function move(string $destPath): bool
    {
        $this->file['is_moved'] = true;
        $this->file['new_path'] = $destPath;
        return move_uploaded_file($this->file['tmp_name'], $destPath);
    }

    /**
     * 删除上传的文件
     */
    public function remove(): void
    {
        if ($this->isMoved()) {
            unlink($this->getNewPath());
        } else {
            unlink($this->getTmpName());
        }
    }

    /**
     * 删除移动后的文件
     */
    public function removeMovedFile(): void
    {
        if ($this->isMoved()) {
            unlink($this->getNewPath());
        }
    }
}
