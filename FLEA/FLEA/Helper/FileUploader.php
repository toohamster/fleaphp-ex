<?php


/**
 * 定义 FLEA_Helper_FileUploader 和 FLEA_Helper_FileUploader_File 对象
 *
 * @author toohamster
 * @package Core
 * @version $Id: FileUploader.php 1018 2007-12-04 23:41:47Z qeeyuan $
 */

/**
 * FLEA_Helper_FileUploader 实现了一个简单的、可扩展的文件上传助手
 *
 * 使用方法：
 *
 * <code>
 * $allowExts = 'jpg,png,gif';
 * $maxSize = 150 * 1024; // 150KB
 * $uploadDir = __DIR__ . '/upload';
 *
 * $uploader = new FLEA_Helper_FileUploader();
 * $files =& $uploader->getFiles();
 * foreach ($files as $file) {
 *     if (!$file->check($allowExts, $maxSize)) {
 *         // 上传的文件类型不符或者超过了大小限制。
 *         return false;
 *     }
 *     // 生成唯一的文件名（重复的可能性极小）
 *     $id = md5(time() . $file->getFilename() . $file->getSize() . $file->getTmpName());
 *     $filename = $id . '.' . strtolower($file->getExt());
 *     $file->move($uploadDir . '/' . $filename);
 * }
 * </code>
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Helper_FileUploader
{
    /**
     * 所有的 UploadFile 对象实例
     *
     * @var array
     */
    public $_files = [];

    /**
     * 可用的上传文件对象数量
     *
     * @var int
     */
    public $_count;

    /**
     * 构造函数
     *
     * @param boolean $cascade
     *
     * @return FLEA_Helper_FileUploader
     */
    public function __construct($cascade = false)
    {
        if (is_array($_FILES)) {
            foreach ($_FILES as $field => $struct) {
                if (!isset($struct['error'])) { continue; }
                if (is_array($struct['error'])) {
                    $arr = [];
                    for ($i = 0; $i < count($struct['error']); $i++) {

                        if ($struct['error'][$i] != UPLOAD_ERR_NO_FILE) {
                            $arr[] = new FLEA_Helper_FileUploader_File($struct, $field, $i);
                            if (!$cascade) {
                                $this->_files["{$field}{$i}"] =& $arr[count($arr) - 1];
                            }
                        }
                    }
                    if ($cascade) {
                        $this->_files[$field] = $arr;
                    }
                } else {
                    if ($struct['error'] != UPLOAD_ERR_NO_FILE) {
                        $this->_files[$field] = new FLEA_Helper_FileUploader_File($struct, $field);
                    }
                }
            }
        }
        $this->_count = count($this->_files);
    }

    /**
     * 可用的上传文件对象数量
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * 返回所有的上传文件对象
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * 检查指定名字的上传文件对象是否存在
     *
     * @param string $name
     *
     * @return boolean
     */
    public function existsFile($name)
    {
        return isset($this->_files[$name]);
    }

    /**
     * 返回指定名字的上传文件对象
     *
     * @param string $name
     *
     * @return FLEA_Helper_FileUploader_File
     */
    public function getFile($name)
    {
        if (!isset($this->_files[$name])) {
            throw new FLEA_Exception_ExpectedFile('$_FILES[' . $name . ']');
        }
        return $this->_files[$name];
    }

    /**
     * 检查指定的上传文件是否存在
     *
     * @param string $name
     *
     * @return boolean
     */
    public function isFileExist($name)
    {
        return isset($this->_files[$name]);
    }

    /**
     * 批量移动上传的文件到目标目录
     *
     * @param string $destDir
     */
    public function batchMove($destDir)
    {
        foreach ($this->_files as $file) {
            /* @var $file FLEA_Helper_FileUploader_File */
            $file->move($destDir . '/' . $file->getFilename());
        }
    }
