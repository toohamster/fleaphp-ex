<?php

namespace FLEA\Middleware;

/**
 * Bearer Token 认证中间件
 *
 * 验证请求中的 Bearer Token，实现基于 Token 的认证机制。
 * 支持静态 Token 列表和自定义验证器两种方式。
 *
 * 配置项（通过 FLEA::setAppInf 设置）：
 * - authTokens: 静态 token 列表，['token1', 'token2']
 * - authValidator: 自定义验证器回调，接收 token 参数返回 bool
 * - authExclude: 不需要认证的路径列表，['/health', '/ping']
 *
 * 用法示例：
 * ```php
 * // 使用静态 token 列表
 * FLEA::setAppInf('authTokens', ['my-secret-token']);
 *
 * // 使用 JWT 验证器
 * FLEA::setAppInf('authValidator', function(string $token): bool {
 *     return JwtHelper::verify($token);
 * });
 *
 * // 排除健康检查路径
 * FLEA::setAppInf('authExclude', ['/health', '/ping']);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * 处理认证请求
     *
     * 验证请求中的 Bearer Token，未通过认证返回 401 错误。
     *
     * @param callable $next 下一个中间件或请求处理器
     *
     * @return void
     * @throws \FLEA\Exception\HttpException 认证失败时抛出 401 异常
     */
    public function handle(callable $next): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // 检查排除路径
        $exclude = (array)\FLEA::getAppInf('authExclude');
        foreach ($exclude as $path) {
            if ($uri === $path || str_starts_with($uri, rtrim($path, '/') . '/')) {
                $next();
                return;
            }
        }

        $token = \FLEA\Request::current()->bearerToken();

        if (!$token || !$this->validate($token)) {
            \FLEA\Response::error('Unauthorized', 401);
        }

        $next();
    }

    private function validate(string $token): bool
    {
        // 优先使用自定义验证器
        $validator = \FLEA::getAppInf('authValidator');
        if (is_callable($validator)) {
            return (bool)$validator($token);
        }

        // 静态 token 列表
        $tokens = (array)\FLEA::getAppInf('authTokens');
        return in_array($token, $tokens, true);
    }
}
