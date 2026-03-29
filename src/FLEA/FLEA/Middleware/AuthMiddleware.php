<?php

namespace FLEA\Middleware;

/**
 * Bearer Token 认证中间件
 *
 * 配置项：
 *   'authTokens'     => ['token1', 'token2']  // 静态 token 列表
 *   'authValidator'  => callable               // 自定义验证器，接收 token 返回 bool
 *   'authExclude'    => ['/health', '/ping']   // 不需要认证的路径
 *
 * 自定义验证器示例：
 *   FLEA::setAppInf('authValidator', function(string $token): bool {
 *       return JwtHelper::verify($token);
 *   });
 */
class AuthMiddleware implements MiddlewareInterface
{
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
