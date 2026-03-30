<?php

namespace FLEA\Helper;

/**
 * 文件发送辅助类
 *
 * 用于向浏览器发送文件，支持下载和内联显示两种模式。
 * 利用 SendFile，应用程序可以将重要的文件保存在浏览器无法访问的位置，
 * 然后通过程序将文件内容发送给浏览器。
 *
 * 主要功能：
 * - 设置正确的 Content-Type
 * - 处理文件名转义
 * - 设置 Content-Length
 * - 支持附件下载和内联显示
 *
 * 用法示例：
 * ```php
 * // 下载文件
 * $sendFile = new SendFile();
 * $sendFile->sendFile(
 *     '/path/to/secure/file.pdf',
 *     'document.pdf',
 *     'application/pdf',
 *     'attachment'
 * );
 *
 * // 在浏览器中显示图片
 * $sendFile->sendFile(
 *     '/path/to/image.jpg',
 *     'photo.jpg',
 *     'image/jpeg',
 *     'inline'
 * );
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class SendFile
{
    /**
     * 向浏览器发送文件内容
     *
     * @param string $serverPath 文件在服务器上的路径（绝对或者相对路径）
     * @param string $filename 发送给浏览器的文件名（尽可能不要使用中文）
     * @param string $mimeType 指示文件类型
     * @param string $disposition 发送方式：'attachment'（下载）或 'inline'（浏览器内显示）
     *
     * @return void
     */
    public function sendFile(string $serverPath, string $filename, string $mimeType = 'application/octet-stream', string $disposition = 'attachment'): void
    {
        header("Content-Type: {$mimeType}");
        $filename = '"' . htmlspecialchars($filename) . '"';
        $filesize = filesize($serverPath);
        $charset = FLEA::getAppInf('responseCharset');
        header("Content-Disposition: {$disposition}; filename={$filename}; charset={$charset}");
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header("Content-Length: {$filesize}");
        readfile($serverPath);
        exit;
    }
}
