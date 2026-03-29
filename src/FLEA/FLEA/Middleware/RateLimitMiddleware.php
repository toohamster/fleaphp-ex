<?php

namespace FLEA\Middleware;

class RateLimitMiddleware implements MiddlewareInterface
{
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
            \FLEA\Response::error('Too Many Requests', 429);
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
