<?php

namespace FLEA\View;

/**
 * 回调视图
 *
 * 用于特殊场景，允许用户通过回调函数处理任意数据生成逻辑
 * 适用于框架设计时未想到的扩展场景，如：
 * - Protocol Buffers 序列化
 * - GraphQL 响应
 * - MessagePack 编码
 * - 自定义模板引擎（Twig、Smarty 等）
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class CallbackView implements ViewInterface
{
    /**
     * @var mixed 用户数据
     */
    private $data;

    /**
     * @var string 内容类型
     */
    private string $contentType;

    /**
     * @var callable 回调函数
     */
    private $callback;

    /**
     * 构造函数
     *
     * @param mixed $data 用户数据
     * @param string $contentType 内容类型
     * @param callable $callback 回调函数
     */
    public function __construct($data, string $contentType, callable $callback)
    {
        $this->data = $data;
        $this->contentType = $contentType;
        $this->callback = $callback;
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
     * 获取内容（通过回调函数生成）
     *
     * @return string
     */
    public function getContent(): string
    {
        $result = call_user_func($this->callback, $this->data);

        // 确保返回字符串
        if (!is_string($result)) {
            // 对象有 __toString 方法
            if (is_object($result) && method_exists($result, '__toString')) {
                return (string) $result;
            }
            // 其他类型转为 JSON
            return json_encode($result);
        }

        return $result;
    }
}
