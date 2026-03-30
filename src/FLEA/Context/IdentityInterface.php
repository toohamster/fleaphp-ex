<?php

namespace FLEA\Context;

/**
 * 身份标识接口
 *
 * 用于获取当前请求的唯一标识，作为上下文存储的 Key 前缀。
 * 不同的身份标识实现支持不同的应用场景（Session、JWT、API Key 等）。
 *
 * @package FLEA
 * @subpackage Context
 * @author toohamster
 * @version 2.0.0
 */
interface IdentityInterface
{
    /**
     * 获取当前请求的唯一标识
     *
     * @return string 身份标识字符串
     */
    public function getId(): string;
}
