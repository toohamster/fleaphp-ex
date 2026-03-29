<?php

namespace FLEA\Middleware;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(callable $next): void
    {
        if (headers_sent()) {
            $next();
            return;
        }

        $origin  = \FLEA::getAppInf('corsAllowOrigin')  ?? '*';
        $methods = \FLEA::getAppInf('corsAllowMethods') ?? 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
        $headers = \FLEA::getAppInf('corsAllowHeaders') ?? 'Content-Type,Authorization,X-Requested-With';
        $maxAge  = \FLEA::getAppInf('corsMaxAge')       ?? 86400;

        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Methods: {$methods}");
        header("Access-Control-Allow-Headers: {$headers}");
        header("Access-Control-Max-Age: {$maxAge}");

        // OPTIONS 预检请求直接返回
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next();
    }
}
