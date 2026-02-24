<?php

namespace FLEA\Helper\FileUploader;


/**
 * 定义 FLEA_Helper_FileUploader_File 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: File.php 1018 2007-12-04 23:41:47Z qeeyuan $
 */

/**
 * 封装一个上传的文件
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class File
{
    /**
     * 上传文件信息
     *
     * @var array
     */
    public $_file = [];

    /**
     * 上传文件对象的名字
     *
     * @var string
     */
    public $_name;

    /**
     * 构造函数
     *
     * @param array $struct
     * @param string $name
     * @param int $ix
     *
     * @return FLEA_Helper_FileUploader_File
     */
    public function __construct($struct, $name, $ix = false)
    {
        if ($ix !== false) {
            $s = array(
                'name' => $struct['name'][$ix],
                'type' => $struct['type'][$ix],
                'tmp_name' => $struct['tmp_name'][$ix],
                'error' => $struct['error'][$ix],
                'size' => $struct['size'][$ix],
            );
            $this->_file = $s;
        } else {
            $this->_file = $struct;
        }

        $this->_file['is_moved'] = false;
        $this->_name = $name;
    }

    /**
     * 设置自定义属性
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->_file[$name] = $value;
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
        return $this->_file[$name];
    }

    /**
     * 返回上传文件对象的名字
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 指示上传是否成功
     *
     * @return boolean
     */
    public function isSuccessed()
    {
        return $this->_file['error'] == UPLOAD_ERR_OK;
    }

    /**
     * 返回上传错误代码
     *
     * @return int
     */
    public function getError()
    {
        return $this->_file['error'];
    }

    /**
     * 指示上传文件是否已经从临时目录移出
     *
     * @return boolean
     */
    public function isMoved()
    {
        return $this->_file['is_moved'];
    }

    /**
     * 返回上传文件的原名
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_file['name'];
    }

    /**
     * 返回上传文件不带"."的扩展名
     *
     * @return string
     */
    public function getExt()
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
    public function getSize()
    {
        return $this->_file['size'];
    }

    /**
     * 返回上传文件的 MIME 类型（由浏览器提供，不可信）
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->_file['type'];
    }

    /**
     * 返回上传文件的临时文件名
     *
     * @return string
     */
    public function getTmpName()
    {
        return $this->_file['tmp_name'];
    }

    /**
     * 获得文件的新路径（通常是移动后的新路径，包括文件名）
     *
     * @return string
     */
    public function getNewPath()
    {
        return $this->_file['new_path'];
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
    public function check($allowExts = null, $maxSize = null)
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
                $exts = array($allowExts);
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
    public function move($destPath)
    {
        $this->_file['is_moved'] = true;
        $this->_file['new_path'] = $destPath;
        return move_uploaded_file($this->_file['tmp_name'], $destPath);
    }

    /**
     * 删除上传的文件
     */
    public function remove()
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
    public function removeMovedFile()
    {
        if ($this->isMoved()) {
            unlink($this->getNewPath());
        }
    }
}
