<?php

namespace FLEA\Auth;

/**
 * JWT 工具类（HS256）
 *
 * 配置项：
 *   'jwtSecret'  => 'your-secret-key'   // 必须，签名密钥
 *   'jwtTtl'     => 7200                // 可选，默认有效期（秒），默认 2 小时
 *   'jwtIssuer'  => 'your-app'          // 可选，签发者
 *
 * 用法：
 *   // 签发
 *   $token = Jwt::encode(['user_id' => 1, 'role' => 'admin']);
 *
 *   // 验证并解析
 *   $payload = Jwt::decode($token);       // 失败抛出 JwtException
 *
 *   // 在 AuthMiddleware 中配置
 *   FLEA::setAppInf('authValidator', fn($token) => (bool)Jwt::decode($token));
 */
class Jwt
{
    /**
     * 签发 JWT
     *
     * @param array $payload 自定义载荷
     * @param int|null $ttl 有效期（秒），null 使用配置默认值
     * @return string
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
     * 验证并解析 JWT
     *
     * @param string $token
     * @return array payload
     * @throws JwtException
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
     * 验证 token 是否有效（不抛异常版本）
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

    private static function secret(): string
    {
        $secret = \FLEA::getAppInf('jwtSecret');
        if (!$secret) {
            throw new JwtException('jwtSecret is not configured');
        }
        return $secret;
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
