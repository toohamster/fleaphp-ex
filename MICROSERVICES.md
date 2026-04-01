# FLEA 框架与微服务

## 框架定位

FLEA 是一个面向微服务开发的极简 PHP 框架，提供轻量级、快速部署的 MVC 基础设施。

## 已支持的微服务能力

| 能力 | 说明 |
|------|------|
| **轻量级** | 仅依赖 4 个包（PSR 日志/容器/缓存 + dotenv） |
| **HTTP 路由** | RESTful 路由、路由分组、中间件管道 |
| **统一响应** | JSON 响应格式 (`{"code": 0, "message": "ok", "data": {...}}`) |
| **JWT 认证** | 服务间认证支持 |
| **中间件** | CORS、限流、认证中间件 |
| **数据库** | TableDataGateway + 关联查询 |
| **缓存** | File/Redis 驱动 (PSR-16) |
| **日志** | PSR-3 + TraceID 请求追踪 |
| **配置管理** | 环境变量 + 配置文件 |

## 规划中能力

### 高优先级

- [ ] **健康检查端点** - `/health` 路由，用于容器编排（K8s/Docker）
- [ ] **优雅关闭处理** - 信号处理，确保请求完成后再退出
- [ ] **HTTP 客户端** - 简单的服务间 HTTP 调用工具

### 中优先级

- [ ] **配置中心集成** - Redis/DB 配置存储
- [ ] **链路追踪集成** - OpenTelemetry 对接
- [ ] **服务发现** - 简单的服务注册/发现机制

## 快速开始微服务

### 最小 API 服务

```php
// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';

\FLEA::loadEnv(__DIR__ . '/../.env');
\FLEA::loadAppInf(__DIR__ . '/../App/Config.php');

// 定义 API 路由
\FLEA\Router::get('/health', 'HealthController@check');
\FLEA\Router::get('/api/users', 'UserController@list');

\FLEA::runMVC();
```

### 配置示例（API 模式）

```php
// App/Config.php
return [
    'defaultController' => 'Health',
    'defaultAction' => 'check',

    // API 服务不需要视图
    'view' => \FLEA\View\NullView::class,

    // 强制 JSON 响应
    'forceJsonResponse' => true,

    // 中间件管道
    'middleware' => [
        \FLEA\Middleware\CorsMiddleware::class,
    ],
];
```

### 健康检查控制器

```php
namespace App\Controller;

use FLEA\Controller\Action;

class HealthController extends Action
{
    public function actionCheck(): void
    {
        \FLEA\Response::success([
            'status' => 'healthy',
            'time' => date('Y-m-d H:i:s'),
        ]);
    }
}
```

## 参考文档

- [SPEC.md](SPEC.md) - 框架规格说明书
- [USER_GUIDE.md](USER_GUIDE.md) - 用户手册
