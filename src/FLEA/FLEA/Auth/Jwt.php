<?php

namespace FLEA\Auth;

/**
 * JWT (JSON Web Token) 工具类
 *
 * 实现基于 HS256 算法的 JWT 签发、验证和解析功能。
 * JWT 是一种开放标准 (RFC 7519)，用于在网络应用环境间安全地传输声明信息。
 *
 * 配置项（通过 FLEA::setAppInf 设置）：
 * - jwtSecret: 签名密钥（必须配置）
 * - jwtTtl: Token 有效期（秒），默认 7200 秒（2 小时）
 * - jwtIssuer: 签发者标识，可选
 *
 * Token 结构：
 * - Header: 包含类型和算法信息 {"typ": "JWT", "alg": "HS256"}
 * - Payload: 包含声明信息（自定义 claims + iat, exp, iss）
 * - Signature: HMAC-SHA256 签名
 *
 * 用法示例：
 * ```php
 * // 签发 Token
 * $token = Jwt::encode([
 *     'user_id' => 1,
 *     'username' => 'john',
 *     'role' => 'admin'
 * ]);
 *
 * // 自定义有效期（1 小时）
 * $token = Jwt::encode(['user_id' => 1], 3600);
 *
 * // 验证并解析 Token
 * try {
 *     $payload = Jwt::decode($token);
 *     echo "User ID: " . $payload['user_id'];
 * } catch (JwtException $e) {
 *     echo "Invalid token: " . $e->getMessage();
 * }
 *
 * // 仅验证有效性
 * if (Jwt::verify($token)) {
 *     // Token 有效
 * }
 *
 * // 在 AuthMiddleware 中配置 JWT 验证器
 * FLEA::setAppInf('authValidator', function(string $token): bool {
 *     return Jwt::verify($token);
 * });
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @link    https://tools.ietf.org/html/rfc7519
 */
class Jwt
{
    /**
     * 签发 JWT Token
     *
     * 自动添加 iat（签发时间）和 exp（过期时间）声明。
     * 如果配置了 jwtIssuer，会自动添加 iss 声明。
     *
     * @param array $payload 自定义载荷数据
     * @param int|null $ttl 有效期（秒），null 使用配置默认值
     *
     * @return string JWT Token 字符串（格式：header.body.signature）
     */
    public static function encode(array $payload, ?int $ttl = null): string
    {
        $secret = self::secret();
        $now    = time();
        $ttl    = $ttl ?? (int)(\FLEA::getAppInf('jwtTtl') ?? 7200);

        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        $issuer = \FLEA::getAppInf('jwtIssuer');
        if ($issuer) {
            $payload['iss'] = $issuer;
        }

        $header    = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body      = self::base64url(json_encode($payload));
        $signature = self::base64url(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        return "{$header}.{$body}.{$signature}";
    }

    /**
     * 验证并解析 JWT Token
     *
     * 验证签名有效性、过期时间和签发者信息。
     *
     * @param string $token JWT Token 字符串
     *
     * @return array 解析后的 payload 数据
     *
     * @throws JwtException Token 格式错误、签名无效、已过期或签发者不匹配时抛出
     */
    public static function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new JwtException('Invalid token format');
        }

        [$header, $body, $signature] = $parts;

        // 验证签名
        $expected = self::base64url(hash_hmac('sha256', "{$header}.{$body}", self::secret(), true));
        if (!hash_equals($expected, $signature)) {
            throw new JwtException('Invalid signature');
        }

        $payload = json_decode(self::base64urlDecode($body), true);
        if (!is_array($payload)) {
            throw new JwtException('Invalid payload');
        }

        // 验证过期
        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new JwtException('Token expired');
        }

        // 验证签发者
        $issuer = \FLEA::getAppInf('jwtIssuer');
        if ($issuer && isset($payload['iss']) && $payload['iss'] !== $issuer) {
            throw new JwtException('Invalid issuer');
        }

        return $payload;
    }

    /**
     * 验证 JWT Token 是否有效
     *
     * 与 decode() 不同，此方法不会抛出异常。
     *
     * @param string $token JWT Token 字符串
     *
     * @return bool Token 有效返回 true，否则返回 false
     */
    public static function verify(string $token): bool
    {
        try {
            self::decode($token);
            return true;
        } catch (JwtException $e) {
            return false;
        }
    }

    /**
     * 获取 JWT 密钥
     *
     * @return string JWT 密钥
     *
     * @throws JwtException 未配置 jwtSecret 时抛出
     */
    private static function secret(): string
    {
        $secret = \FLEA::getAppInf('jwtSecret');
        if (!$secret) {
            throw new JwtException('jwtSecret is not configured');
        }
        return $secret;
    }

    /**
     * Base64 URL 安全编码
     *
     * 将标准 Base64 转换为 URL 安全格式（去填充、替换字符）。
     *
     * @param string $data 原始数据
     *
     * @return string URL 安全的 Base64 字符串
     */
    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL 安全解码
     *
     * 将 URL 安全的 Base64 字符串解码为原始数据。
     *
     * @param string $data URL 安全的 Base64 字符串
     *
     * @return string 解码后的原始数据
     */
    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
