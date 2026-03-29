<?php

namespace FLEA\Context\Identity;

use FLEA\Context\IdentityInterface;
use FLEA\Request;

/**
 * JWT 身份标识
 *
 * 从 JWT Token 中获取用户 ID 作为身份标识。
 * 适用于微服务、无状态应用场景。
 *
 * @package FLEA
 * @subpackage Context\Identity
 * @author toohamster
 * @version 2.0.0
 */
class JwtIdentity implements IdentityInterface
{
    /**
     * JWT 密钥（用于验证 Token）
     *
     * @var string
     */
    private string $secret;

    /**
     * Token 前缀（默认 Bearer）
     *
     * @var string
     */
    private string $bearerPrefix;

    /**
     * 构造函数
     *
     * @param string $secret JWT 密钥
     * @param string $bearerPrefix Token 前缀（默认 "Bearer "）
     */
    public function __construct(
        string $secret = '',
        string $bearerPrefix = 'Bearer '
    ) {
        $this->secret = $secret;
        $this->bearerPrefix = $bearerPrefix;
    }

    /**
     * 从 JWT Token 中获取用户 ID
     *
     * @return string 用户 ID（格式：user:{id}）
     */
    public function getId(): string
    {
        $request = Request::current();
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return 'guest:' . uniqid();
        }

        // 移除 Bearer 前缀
        $token = $authHeader;
        if (str_starts_with($authHeader, $this->bearerPrefix)) {
            $token = substr($authHeader, strlen($this->bearerPrefix));
        }

        try {
            // 解码 JWT
            $payload = \FLEA\Jwt::decode($token, $this->secret);

            // 优先使用 jti（Token ID），其次使用 sub（用户 ID）
            $id = $payload['jti'] ?? $payload['sub'] ?? 'unknown';
            return 'user:' . $id;
        } catch (\Exception $e) {
            // Token 无效，返回访客标识
            return 'guest:' . uniqid();
        }
    }
}
