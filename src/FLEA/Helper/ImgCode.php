<?php

namespace FLEA\Helper;

/**
 * 图像验证码生成器
 *
 * 实现了一个简单的图像验证码生成器，支持上下文存储和验证。
 * 默认使用 Context 组件存储验证码，支持 Session/Redis/File 多种后端。
 *
 * 主要功能：
 * - 生成随机验证码
 * - 支持数字、字母、混合三种类型
 * - 自定义字体、颜色、背景色
 * - 上下文存储和过期验证
 * - 区分大小写/不区分大小写验证
 * - 获取验证码图像二进制内容（与 View 配合使用）
 *
 * 用法示例：
 * ```php
 * // 新用法：配合 View 返回验证码图像
 * public function actionCaptcha(): ViewInterface
 * {
 *     $imgCode = new ImgCode();
 *     $imgCode->generate();
 *     return View::binary(
 *         $imgCode->getImageData(),
 *         'captcha.jpg',
 *         $imgCode->getContentType()
 *     );
 * }
 *
 * // 验证用户提交的验证码
 * public function actionSubmit(): void
 * {
 *     $imgCode = new ImgCode();
 *     if ($imgCode->check($_POST['imgcode'])) {
 *         // 验证通过
 *     }
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
 */
class ImgCode
{
    /**
     * 生成的验证码
     *
     * @var string
     */
    public string $code = '';

    /**
     * 验证码过期时间
     *
     * @var int
     */
    public int $expired = 0;

    /**
     * 验证码图片的类型（默认为 jpeg）
     *
     * @var string
     */
    public string $imagetype = 'jpeg';

    /**
     * 存储键名前缀
     *
     * @var string
     */
    private string $keyPrefix = 'IMGCODE';

    /**
     * 生成验证码
     *
     * @param int $type 验证码类型：0-数字，1-字母，其他 - 数字和字母
     * @param int $length 验证码长度
     * @param int $lefttime 验证码有效时间（秒）
     * @return void
     */
    public function generate(int $type = 0, int $length = 4, int $lefttime = 900): void
    {
        // 生成验证码种子
        switch ($type) {
        case 0:
            $seed = '0123456789';
            break;
        case 1:
            $seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        default:
            $seed = '346789ABCDEFGHJKLMNPQRTUVWXYabcdefghjklmnpqrtuvwxy';
        }
        if ($length <= 0) { $length = 4; }
        $code = '';
        [$usec, $sec] = explode(" ", microtime());
        srand($sec + $usec * 100000);
        $len = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= substr($seed, rand(0, $len), 1);
        }

        // 存储验证码到上下文
        flea_context()->set($this->keyPrefix, $code, $lefttime);
        flea_context()->set($this->keyPrefix . '_EXPIRED', time() + $lefttime, $lefttime);

        $this->code = $code;
        $this->expired = time() + $lefttime;
    }

    /**
     * 获取验证码图像的二进制内容（不直接输出）
     *
     * 与 image() 方法不同，此方法返回图像内容而不是直接输出。
     * 可以与 View::binary() 配合使用，实现更灵活的响应。
     *
     * 用法示例：
     * ```php
     * // 控制器中返回验证码图像
     * public function actionCaptcha(): ViewInterface
     * {
     *     $imgCode = new ImgCode();
     *     $imgCode->generate();
     *     return View::binary(
     *         $imgCode->getImageData(),
     *         'captcha.jpg',
     *         $imgCode->getContentType()
     *     );
     * }
     * ```
     *
     * @param array|null $options 附加选项（字体、颜色、背景等）
     * @return string 图像二进制内容
     */
    public function getImageData(?array $options = null): string
    {
        if ($this->code === '') {
            throw new \RuntimeException('Call generate() first to generate a code');
        }

        // 创建图像
        $img = $this->createImage($options);

        // 获取图像内容到字符串
        ob_start();
        switch (strtolower($this->imagetype)) {
        case 'png':
            imagepng($img);
            break;
        case 'gif':
            imagegif($img);
            break;
        case 'jpg':
        default:
            imagejpeg($img);
        }
        $content = ob_get_clean();
        imagedestroy($img);
        unset($img);

        return $content;
    }

    /**
     * 获取图像 Content-Type
     *
     * @return string 如 'image/jpeg', 'image/png', 'image/gif'
     */
    public function getContentType(): string
    {
        switch (strtolower($this->imagetype)) {
        case 'png':
            return 'image/png';
        case 'gif':
            return 'image/gif';
        default:
            return 'image/jpeg';
        }
    }

    /**
     * 检查图像验证码是否有效
     *
     * @param string $code 用户提交的验证码
     * @return bool 验证通过返回 true
     */
    public function check(string $code): bool
    {
        $time = time();
        if ($time >= $this->expired || strtoupper($code) != strtoupper($this->code)) {
            return false;
        }
        return true;
    }

    /**
     * 检查图像验证码是否有效（区分大小写）
     *
     * @param string $code 用户提交的验证码
     * @return bool 验证通过返回 true
     */
    public function checkCaseSensitive(string $code): bool
    {
        $time = time();
        if ($time >= $this->expired || $code != $this->code) {
            return false;
        }
        return true;
    }

    /**
     * 清除上下文存储中的 imgcode 相关信息
     *
     * @return void
     */
    public function clear(): void
    {
        flea_context()->remove($this->keyPrefix);
        flea_context()->remove($this->keyPrefix . '_EXPIRED');
    }

    /**
     * 创建验证码图像资源
     *
     * @param array|null $options 选项（字体、颜色、背景、边框等）
     * @return resource GD 图像资源
     */
    private function createImage(?array $options = null)
    {
        // 设置选项
        $paddingLeft = (int)($options['paddingLeft'] ?? 3);
        $paddingRight = (int)($options['paddingRight'] ?? 3);
        $paddingTop = (int)($options['paddingTop'] ?? 2);
        $paddingBottom = (int)($options['paddingBottom'] ?? 2);
        $color = $options['color'] ?? '0xffffff';
        $bgcolor = $options['bgcolor'] ?? '0x666666';
        $border = (int) ($options['border'] ?? 1);
        $bdColor = $options['borderColor'] ?? '0x000000';

        // 确定要使用的字体
        if (!isset($options['font'])) {
            $font = 5;
        } else if (is_int($options['font'])) {
            $font = (int)$options['font'];
            if ($font < 0 || $font > 5) { $font = 5; }
        } else {
            $font = imageloadfont($options['font']);
        }

        // 确定字体宽度和高度
        $fontWidth = imagefontwidth($font);
        $fontHeight = imagefontheight($font);

        // 确定图像的宽度和高度
        $width = $fontWidth * strlen($this->code) + $paddingLeft + $paddingRight +
                $border * 2 + 1;
        $height = $fontHeight + $paddingTop + $paddingBottom + $border * 2 + 1;

        // 创建图像
        $img = imagecreate($width, $height);

        // 绘制边框
        if ($border) {
            [$r, $g, $b] = self::hex2rgb($bdColor);
            $borderColor = imagecolorallocate($img, $r, $g, $b);
            imagefilledrectangle($img, 0, 0, $width, $height, $borderColor);
        }

        // 绘制背景
        [$r, $g, $b] = self::hex2rgb($bgcolor);
        $backgroundColor = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, $border, $border,
                $width - $border - 1, $height - $border - 1, $backgroundColor);

        // 绘制文字
        [$r, $g, $b] = self::hex2rgb($color);
        $textColor = imagecolorallocate($img, $r, $g, $b);
        imagestring($img, $font, $paddingLeft + $border, $paddingTop + $border,
                $this->code, $textColor);

        return $img;
    }

    /**
     * 将 16 进制颜色值转换为 RGB 数组
     *
     * 支持多种格式：
     * - '0xffffff' 或 '0xfff'
     * - '#ffffff' 或 '#fff'
     * - 'ffffff' 或 'fff'
     *
     * @param string $color 16 进制颜色值
     * @param string $default 默认颜色值（当输入格式无效时）
     * @return array [r, g, b] 如 [255, 255, 255]
     */
    public static function hex2rgb(string $color, string $default = 'ffffff'): array
    {
        $color = strtolower($color);
        if (substr($color, 0, 2) == '0x') {
            $color = substr($color, 2);
        } elseif (substr($color, 0, 1) == '#') {
            $color = substr($color, 1);
        }
        $l = strlen($color);
        if ($l == 3) {
            $r = hexdec(substr($color, 0, 1));
            $g = hexdec(substr($color, 1, 1));
            $b = hexdec(substr($color, 2, 1));
            return [$r, $g, $b];
        } elseif ($l != 6) {
            $color = $default;
        }

        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        return [$r, $g, $b];
    }
}
