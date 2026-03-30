<?php

namespace FLEA\Context\Identity;

use FLEA\Context\IdentityInterface;

/**
 * Session 身份标识
 *
 * 使用 PHP Session ID 作为身份标识。
 * 适用于传统 Web 应用场景。
 *
 * @package FLEA
 * @subpackage Context\Identity
 * @author toohamster
 * @version 2.0.0
 */
class SessionIdentity implements IdentityInterface
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    /**
     * 获取当前 Session ID
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }
}
