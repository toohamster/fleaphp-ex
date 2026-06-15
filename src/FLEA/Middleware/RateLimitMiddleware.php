<?php

namespace FLEA\Middleware;

/**
 * 请求频率限制中间件
 *
 * 基于缓存实现请求频率限制，防止接口被滥用。
 * 支持按 IP、Token 等方式进行限流。
 *
 * 配置项（通过 FLEA::setAppInf 设置）：
 * - rateLimitMax: 最大请求次数，默认 60 次
 * - rateLimitWindow: 时间窗口（秒），默认 60 秒
 * - rateLimitBy: 限流依据，支持 'ip' 或 'token'，默认 'ip'
 *
 * 响应头：
 * - X-RateLimit-Limit: 最大请求次数
 * - X-RateLimit-Remaining: 剩余请求次数
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * 处理频率限制请求
     *
     * 检查请求频率是否超出限制，超出则返回 429 错误。
     *
     * @param callable $next 下一个中间件或请求处理器
     *
     * @return void
     */
    public function handle(callable $next): void
    {
        $max    = (int)(\FLEA::getAppInf('rateLimitMax')    ?? 60);
        $window = (int)(\FLEA::getAppInf('rateLimitWindow') ?? 60);
        $by     = \FLEA::getAppInf('rateLimitBy') ?? 'ip';

        $key    = 'rate:' . $this->resolveKey($by);
        $cache  = \FLEA\Cache::provider();

        $count  = (int)($cache->get($key) ?? 0);

        if ($count >= $max) {
            if (!headers_sent()) {
                header('X-RateLimit-Limit: ' . $max);
                header('X-RateLimit-Remaining: 0');
            }
            \FLEA\Response::current()
                ->withStatus(429)
                ->setView(\FLEA\View::json([
                    'code'    => -1,
                    'message' => 'Too Many Requests',
                    'data'    => null,
                ], 429));
            return;
        }

        // 首次请求设置 TTL，后续递增
        if ($count === 0) {
            $cache->set($key, 1, $window);
        } else {
            $cache->set($key, $count + 1, $window);
        }

        if (!headers_sent()) {
            header('X-RateLimit-Limit: ' . $max);
            header('X-RateLimit-Remaining: ' . ($max - $count - 1));
        }

        $next();
    }

    private function resolveKey(string $by): string
    {
        switch ($by) {
            case 'token':
                return \FLEA\Request::current()->bearerToken() ?? 'anonymous';
            default:
                return \FLEA\Request::current()->ip();
        }
    }
}
