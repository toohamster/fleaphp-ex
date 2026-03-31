<?php

namespace FLEA\Auth;

/**
 * JWT 认证异常
 *
 * 当 JWT 令牌验证、解析或签发失败时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class JwtException extends \RuntimeException
{
}
