# FleaPHP 框架规格说明书 v2.0

## 1. 概述

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 自动加载机制，支持 PHP 7.4+。

| 项目 | 说明 |
|------|------|
| **版本** | 2.0.0 |
| **命名空间** | `FLEA\` |
| **PHP 要求** | 7.4+ |
| **许可证** | MIT |

### 1.1 PSR 标准合规

| 组件 | PSR 标准 | 说明 |
|------|----------|------|
| `FLEA\Container` | PSR-11 | 依赖注入容器 |
| `FLEA\Cache` | PSR-16 | 缓存接口 |
| `FLEA\Log` | PSR-3 | 日志接口 |

---

## 2. 目录结构

### 2.1 框架目录

```
src/
├── FLEA.php                        # 框架入口文件（全局类）
├── Functions.php                   # 全局函数（flea_context 等）
└── FLEA/                           # 框架核心代码（命名空间 FLEA\Xxx 的根）
    ├── Auth/                       # 认证支持
    │   ├── Jwt.php                 # JWT 工具 (HS256)
    │   └── JwtException.php        # JWT 异常
    ├── Cache/                      # 缓存驱动
    │   ├── FileCache.php           # 文件缓存 (PSR-16)
    │   └── RedisCache.php          # Redis 缓存 (PSR-16)
    ├── Config/                     # 配置相关
    │   └── Defaults.php            # 默认配置
    ├── Context/                    # 上下文管理（请求级状态）
    │   ├── Context.php             # 核心类
    │   ├── DriverInterface.php     # 驱动接口
    │   ├── IdentityInterface.php   # 身份标识接口
    │   ├── Driver/                 # 存储驱动
    │   │   ├── SessionDriver.php   # Session 存储
    │   │   ├── RedisDriver.php     # Redis 存储
    │   │   ├── FileDriver.php      # 文件存储
    │   │   └── DatabaseSessionDriver.php  # 数据库存储
    │   └── Identity/               # 身份标识
    │       ├── SessionIdentity.php # Session ID
    │       ├── JwtIdentity.php     # JWT 用户
    │       ├── ApiKeyIdentity.php  # API Key
    │       └── RequestIdIdentity.php # Request ID
    ├── Controller/                 # 控制器基类
    │   └── Action.php              # 动作控制器基类
    ├── Db/                         # 数据库相关组件
    │   ├── Driver/                 # 数据库驱动
    │   │   ├── AbstractDriver.php  # 抽象基类
    │   │   └── Mysql.php           # MySQL 驱动
    │   ├── Exception/              # 数据库异常
    │   ├── TableLink/              # 表关联处理
    │   │   ├── HasOneLink.php
    │   │   ├── BelongsToLink.php
    │   │   ├── HasManyLink.php
    │   │   └── ManyToManyLink.php
    │   ├── SqlHelper.php           # SQL 辅助
    │   ├── SqlStatement.php        # SQL 语句处理
    │   ├── TableDataGateway.php    # 表数据入口 (CRUD)
    │   └── TableLink.php           # 表关联基类
    ├── Dispatcher/                 # 请求调度器
    │   ├── Exception/
    │   │   └── CheckFailed.php
    │   ├── Auth.php                # 认证调度器
    │   └── Simple.php              # 简单调度器
    ├── Error/                      # 错误处理
    │   ├── ErrorRenderer.php       # 错误渲染器
    │   └── views/
    │       └── 500.php
    ├── Exception/                  # 框架通用异常
    ├── Helper/                     # 辅助类
    │   ├── FileUploader/
    │   │   └── File.php
    │   ├── FileUploader.php        # 文件上传
    │   ├── Image.php               # 图像处理
    │   ├── ImgCode.php             # 验证码
    │   ├── Pager.php               # 分页器
    │   ├── SendFile.php            # 文件下载
    │   └── Verifier.php            # 数据验证
    ├── Middleware/                 # 中间件
    │   ├── MiddlewareInterface.php # 中间件接口
    │   ├── Pipeline.php            # 中间件管道
    │   ├── CorsMiddleware.php      # CORS 中间件
    │   ├── AuthMiddleware.php      # 认证中间件
    │   └── RateLimitMiddleware.php # 限流中间件
    ├── Rbac/                       # RBAC 子组件
    │   ├── Exception/
    │   │   ├── InvalidACT.php
    │   │   └── InvalidACTFile.php
    │   ├── RolesManager.php        # 角色管理
    │   └── UsersManager.php        # 用户管理
    ├── Acl/                        # ACL 子组件
    │   ├── Exception/
    │   │   └── UserGroupNotFound.php
    │   ├── Table/                  # ACL 数据表
    │   ├── Manager.php             # ACL 管理器
    │   ├── testACL.php
    │   └── testCreateData.php
    ├── View/                       # 视图引擎
    │   ├── ViewInterface.php       # 视图接口
    │   ├── Simple.php              # 简单模板引擎
    │   └── NullView.php            # 空视图
    ├── Cache.php                   # 缓存门面 (PSR-16)
    │── Config.php                  # 配置管理器 (单例)
    │── Container.php               # 对象容器 (PSR-11)
    │── Database.php                # 数据库连接管理
    │── Env.php                     # 环境检测工具
    │── Exception.php               # 基础异常类
    │── Language.php                # 多语言支持
    │── Log.php                     # 日志服务 (PSR-3)
    │── Request.php                 # HTTP 请求封装
    │── Response.php                # HTTP 响应封装
    │── Router.php                  # HTTP 路由器
    │── Route.php                   # 单条路由
    │── Rbac.php                    # RBAC 服务类
```

### 2.2 应用目录（示例）

```
demo/
├── .env                    # 环境变量（基础配置）
├── .env.local              # 本地开发配置（可选）
├── .env.production         # 生产环境配置（可选）
├── App/
│   ├── Config.php          # 应用配置
│   ├── Controller/         # 应用控制器
│   ├── Model/              # 应用模型
│   └── View/               # 应用视图
└── public/
    └── index.php           # Web 入口
```

---

## 3. 核心服务类

### 3.1 FLEA 类

框架主入口类，所有方法为静态方法，委托给各服务类：

```php
class FLEA
{
    // 配置管理
    public static function loadEnv(string $path): void
    public static function loadAppInf($config = null): void
    public static function getAppInf(string $option, $default = null)
    public static function setAppInf($option, $data = null): void
    public static function setAppInfValue(string $option, string $keyname, $value): void
    public static function getAppInfValue(string $option, string $keyname, $default = null)

    // 对象容器（PSR-11）
    public static function getSingleton(string $className): object
    public static function register(object $obj, ?string $name = null): object
    public static function isRegistered(string $name): bool

    // 数据库
    public static function getDBO($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    public static function parseDSN($dsn): ?array

    // 缓存
    public static function getCache(string $cacheId, int $time = 900, ...): mixed
    public static function writeCache(string $cacheId, $data): bool
    public static function purgeCache(string $cacheId): bool

    // 中间件
    public static function middleware(\FLEA\Middleware\MiddlewareInterface $mw): void

    // 应用启动
    public static function runMVC(): void
    public static function init(bool $loadMVC = false): void
}
```

### 3.2 Config (配置管理器)

单例模式，管理应用程序配置：

```php
namespace FLEA;

class Config
{
    public array $appInf = [];

    public static function getInstance(): self
    public function getAppInf(string $option, $default = null)
    public function setAppInf($option, $data = null): void
    public function getAppInfValue(string $option, string $keyname, $default = null)
    public function setAppInfValue(string $option, string $keyname, $value): void
    public function mergeAppInf(array $config): void
}
```

### 3.3 Container (对象容器)

实现 PSR-11 依赖注入容器：

```php
namespace FLEA;

class Container implements \Psr\Container\ContainerInterface
{
    public function get(string $id): mixed       // PSR-11: 获取对象
    public function has(string $id): bool        // PSR-11: 检查是否存在
    public function register(object $obj, ?string $name = null): object
    public function singleton(string $className): object
    public function all(): array
}
```

### 3.4 Database (数据库连接管理)

管理数据库连接池：

```php
namespace FLEA;

class Database
{
    public static function getInstance(): self
    public function connect($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    public function parseDSN($dsn): ?array
}
```

### 3.5 Cache (缓存门面)

PSR-16 缓存门面：

```php
namespace FLEA;

class Cache
{
    public static function provider(): \Psr\SimpleCache\CacheInterface
}
```

**配置项 `cacheProvider`**:
- `null` (默认) → `FLEA\Cache\FileCache`
- `\FLEA\Cache\RedisCache::class` → Redis

### 3.6 Log (日志服务)

实现 PSR-3 LoggerInterface：

```php
namespace FLEA;

class Log extends \Psr\Log\AbstractLogger
{
    public string $traceId;
    public bool $enabled = true;
    public ?string $logFileDir;
    public ?string $logFilename;

    public function log($level, $message, array $context = []): void  // PSR-3
    public function flush(): void
    public function getTraceId(): string
}
```

### 3.7 Env (环境检测工具)

```php
namespace FLEA;

class Env
{
    public static function isEnv(string $env): bool
    public static function isLocal(): bool
    public static function isProduction(): bool
    public static function isDevelopment(): bool
}
```

---

## 4. Context 上下文组件

Context 提供请求级别的状态管理服务，支持多种存储驱动和身份标识，用于替代传统的 `$_SESSION`。

### 4.1 Context 核心类

```php
namespace FLEA\Context;

class Context
{
    public function get(string $key, mixed $default = null): mixed
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    public function remove(string $key): bool
    public function has(string $key): bool
}
```

### 4.2 驱动接口

```php
namespace FLEA\Context;

interface DriverInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    public function remove(string $key): bool;
    public function has(string $key): bool;
}
```

### 4.3 身份标识接口

```php
namespace FLEA\Context;

interface IdentityInterface
{
    public function getId(): string;
}
```

### 4.4 内置驱动

| 驱动 | 类 | 说明 |
|------|-----|------|
| SessionDriver | `FLEA\Context\Driver\SessionDriver` | 使用 `$_SESSION` 存储 |
| RedisDriver | `FLEA\Context\Driver\RedisDriver` | 使用 Redis 存储 |
| FileDriver | `FLEA\Context\Driver\FileDriver` | 使用文件系统存储 |
| DatabaseSessionDriver | `FLEA\Context\Driver\DatabaseSessionDriver` | 使用数据库存储 |

### 4.5 内置身份标识

| 身份标识 | 类 | 说明 |
|----------|-----|------|
| SessionIdentity | `FLEA\Context\Identity\SessionIdentity` | 使用 session_id() |
| JwtIdentity | `FLEA\Context\Identity\JwtIdentity` | 从 JWT 提取用户 ID |
| ApiKeyIdentity | `FLEA\Context\Identity\ApiKeyIdentity` | 使用 API Key 哈希 |
| RequestIdIdentity | `FLEA\Context\Identity\RequestIdIdentity` | 使用 X-Request-ID |

### 4.6 配置项

```php
return [
    // Context 驱动：session/redis/file/database
    'contextDriver' => env('CONTEXT_DRIVER', 'session'),

    // 身份标识：session/jwt/api-key/request-id
    'contextIdentity' => env('CONTEXT_IDENTITY', 'session'),

    // 各驱动的详细配置
    'context' => [
        'redis' => [
            'host' => env('CONTEXT_REDIS_HOST', '127.0.0.1'),
            'port' => (int) env('CONTEXT_REDIS_PORT', 6379),
            'password' => env('CONTEXT_REDIS_PASSWORD', ''),
            'prefix' => env('CONTEXT_REDIS_PREFIX', 'fleaphp:context:'),
        ],
        'file' => [
            'path' => env('CONTEXT_FILE_PATH', ''),
        ],
        'database' => [
            'tableName' => env('CONTEXT_DB_TABLE', 'contexts'),
            'fieldId' => env('CONTEXT_DB_FIELD_ID', 'context_id'),
            'fieldData' => env('CONTEXT_DB_FIELD_DATA', 'context_data'),
            'fieldActivity' => env('CONTEXT_DB_FIELD_ACTIVITY', 'activity'),
            'lifeTime' => (int) env('CONTEXT_DB_LIFETIME', 3600),
        ],
    ],
];
```

### 4.7 全局辅助函数

```php
// 获取 Context 实例
$context = flea_context();

// 读写数据
flea_context()->set('user_id', 123);
$user_id = flea_context()->get('user_id');
```

---

## 5. HTTP 组件

### 5.1 Request (请求封装)

```php
namespace FLEA;

class Request
{
    public static function current(): self

    // 请求方法
    public function method(): string
    public function isGet(): bool
    public function isPost(): bool
    public function isPut(): bool
    public function isDelete(): bool
    public function isAjax(): bool
    public function isJson(): bool

    // 数据获取
    public function input(string $key, $default = null): mixed
    public function json(string $key = null, $default = null): mixed
    public function get(string $key, $default = null): mixed
    public function post(string $key, $default = null): mixed
    public function param(string $key, $default = null): mixed
    public function all(): array

    // 请求头/认证
    public function header(string $name, $default = null): ?string
    public function bearerToken(): ?string
    public function ip(): string
    public function uri(): string
}
```

### 5.2 Response (响应封装)

```php
namespace FLEA;

class Response
{
    public static function make(): self
    public function code(int $code): self
    public function header(string $name, string $value): self
    public function json($data): void
    public function text(string $content): void

    // 快捷方法
    public static function success($data = null, string $message = 'ok', int $httpCode = 200): void
    public static function error(string $message, int $httpCode = 400, int $errCode = -1): void
    public static function paginate(array $items, int $total, int $page, int $pageSize): void
    public static function send($data, int $code = 200): void
}
```

**统一响应结构**:
```json
// 成功
{"code": 0, "message": "ok", "data": {...}}

// 错误
{"code": -1, "message": "error message", "data": null}
```

### 5.3 Router (路由器)

```php
namespace FLEA;

class Router
{
    // 路由注册
    public static function get(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function post(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function put(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function patch(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function delete(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function any(string $path, string $handler, array $middlewares = []): \FLEA\Route
    public static function group(string $prefix, callable $callback, array $middlewares = []): void

    // RESTful 资源路由
    public static function resource(string $name, string $controller, array $options = []): void

    // 命名路由
    public static function urlFor(string $name, array $params = []): string

    // 路由匹配
    public static function dispatch(): bool
    public static function getMatchedMiddlewares(): array
    public static function routes(): array
}
```

**路由语法**:
```php
Router::get('/users', 'UserController@index');
Router::get('/users/{id:\d+}', 'UserController@show');
Router::post('/users', 'UserController@store', [new AuthMiddleware()]);
Router::group('/admin', fn() => {
    Router::get('/stats', 'AdminController@stats');
}, [new AuthMiddleware()]);
```

**RESTful 资源路由**:
```php
// 生成全部 7 条路由
Router::resource('post', 'PostController');

// 只保留部分方法
Router::resource('post', 'PostController', ['only' => ['index', 'show']]);

// 排除部分方法
Router::resource('post', 'PostController', ['except' => ['create', 'edit']]);
```

生成的路由表：

| 方法 | URI | 处理器 | 路由名 |
|------|-----|--------|--------|
| GET | /{name} | {controller}@index | {name}.index |
| GET | /{name}/create | {controller}@create | {name}.create |
| POST | /{name} | {controller}@store | {name}.store |
| GET | /{name}/{id} | {controller}@show | {name}.show |
| GET | /{name}/{id}/edit | {controller}@edit | {name}.edit |
| PUT | /{name}/{id} | {controller}@update | {name}.update |
| PUT | /{name}/{id} | {controller}@update | {name}.update.post (fallback) |
| DELETE | /{name}/{id} | {controller}@destroy | {name}.destroy |
| POST | /{name}/{id} | {controller}@destroy | {name}.destroy.post (fallback) |

**说明**：
- `resource()` 方法一行代码生成 7 条 RESTful 路由
- 支持 `only`（白名单）和 `except`（黑名单）选项过滤路由
- update 和 destroy 额外注册 POST fallback 路由，兼容 HTML 表单只支持 GET/POST 的限制
- 路由名格式：`{name}.{action}`（如 `post.index`、`post.update.post`）

### 5.4 Route (单条路由)

```php
namespace FLEA;

class Route
{
    public function name(string $name): self  // 命名路由，支持链式调用
}
```

### 5.5 Middleware (中间件)

```php
namespace FLEA\Middleware;

interface MiddlewareInterface
{
    public function handle(callable $next): void;
}
```

**管道实现**:
```php
namespace FLEA\Middleware;

class Pipeline
{
    public static function create(): self
    public function pipe(MiddlewareInterface $middleware): self
    public function run(callable $destination): void
}
```

**已实现中间件**:
- `CorsMiddleware` - CORS 跨域支持
- `AuthMiddleware` - JWT 认证
- `RateLimitMiddleware` - 请求限流

### 5.6 Auth/Jwt (JWT 认证)

```php
namespace FLEA\Auth;

class Jwt
{
    public static function encode(array $payload, ?int $ttl = null): string
    public static function decode(string $token): array
    public static function verify(string $token): bool
}
```

**配置项**:
- `jwtSecret`: 签名密钥（必须）
- `jwtTtl`: 有效期（秒），默认 7200
- `jwtIssuer`: 签发者（可选）

---

## 6. MVC 架构

### 6.1 控制器 (Controller)

```php
namespace FLEA\Controller;

class Action
{
    protected string $controllerName;
    protected string $actionName;
    protected ?\FLEA\Dispatcher\Simple $dispatcher;
    public $components = [];

    // 生命周期
    public function setController(string $controllerName, string $actionName): void
    public function setDispatcher(\FLEA\Dispatcher\Simple $dispatcher): void
    public function beforeExecute($actionMethod): void
    public function afterExecute($actionMethod): void

    // 辅助方法
    protected function getComponent(string $componentName): object
    protected function getDispatcher(): ?\FLEA\Dispatcher\Simple
    protected function url(?string $actionName = null, ?array $args = null, ?string $anchor = null): string
    protected function forward(?string $controllerName = null, ?string $actionName = null): void
    protected function getView(): \FLEA\View\ViewInterface
    protected function executeView(string $viewName, ?array $data = null): void
    protected function isPost(): bool
    protected function isAjax(): bool
}
```

### 6.2 视图 (View)

```php
namespace FLEA\View;

interface ViewInterface
{
    public function assign($key, $value = null): void;
    public function display(string $template): void;
    public function fetch(string $template, ?string $cacheId = null): string;
}
```

**Simple 视图实现**:
```php
namespace FLEA\View;

class Simple implements ViewInterface
{
    public ?string $templateDir;
    public int $cacheLifetime;
    public bool $enableCache;
    public string $cacheDir;

    public function assign($name, $value = null): void
    public function display(string $file, ?string $cacheId = null): void
    public function fetch(string $file, ?string $cacheId = null): string
    public function isCached(string $file, ?string $cacheId = null): bool
    public function cleanCache(string $file, ?string $cacheId = null): void
    public function cleanAllCache(): void
}
```

**NullView (空视图)**:
```php
namespace FLEA\View;

class NullView implements ViewInterface
{
    public function assign($key, $value = null): void {}
    public function display(string $template): void {}
    public function fetch(string $template, ?string $cacheId = null): string { return ''; }
}
```

### 6.3 调度器 (Dispatcher)

```php
namespace FLEA\Dispatcher;

class Simple
{
    public function __construct(array &$request)
    public function dispatching()
    public function getControllerName(): string
    public function getActionName(): string
    public function setControllerName(string $controllerName): void
    public function setActionName(string $actionName): void
    public function getControllerClass(string $controllerName): string

    protected function executeAction(string $controllerName, string $actionName, string $controllerClass)
    protected function loadController(string $controllerClass): bool
}
```

---

## 7. 数据库组件

### 7.1 TableDataGateway

```php
namespace FLEA\Db;

class TableDataGateway
{
    public string $schema;
    public string $tableName;
    public string $fullTableName;
    public $primaryKey;  // string|array|null
    public array $hasOne;
    public array $belongsTo;
    public array $hasMany;
    public array $manyToMany;
    public array $meta;
    public bool $autoValidating;
    public ?\FLEA\Helper\Verifier $verifier;

    // CRUD
    public function find($conditions, $sort = null, $fields = '*', $queryLinks = true): ?array
    public function findAll($conditions = null, $sort = null, $limit = null, $fields = '*', $queryLinks = true): array
    public function findCount($conditions = null): int
    public function create(array &$row, bool $saveLinks = true): int
    public function update(array &$row, bool $saveLinks = true): bool
    public function remove(array &$row, bool $removeLink = true): bool
    public function save(array &$row): bool
    public function removeByPkv($id): bool
}
```

**关联常量**:
| 常量 | 值 | 说明 |
|------|-----|------|
| `HAS_ONE` | 1 | 一对一关联 |
| `BELONGS_TO` | 2 | 从属关联 |
| `HAS_MANY` | 3 | 一对多关联 |
| `MANY_TO_MANY` | 4 | 多对多关联 |

### 7.2 Database Driver

```php
namespace FLEA\Db\Driver;

abstract class AbstractDriver
{
    public array $dsn;
    public ?\PDO $connection;

    public function connect(): void
    public function disconnect(): void
    public function execute(string $sql): int|bool
    public function getOne(string $sql): mixed
    public function getAll(string $sql): array
    public function insert(string $table, array $fields, array $values): int
    public function update(string $table, array $fields, array $values, string $where): bool
    public function delete(string $table, string $where): bool
    public function qstr(string $str): string
    public function qfield(string $field): string
    public function qtable(string $table): string
    public function insertID(): int
    public function affectedRows(): int
}
```

### 7.3 SqlStatement

SQL 语句构建辅助类。

---

## 8. 权限管理

### 8.1 RBAC

```php
namespace FLEA;

class Rbac
{
    public string $sessionKey;
    public string $rolesKey;

    public function setUser(array $userData, $rolesData = null): void
    public function getUser(): ?array
    public function clearUser(): void
    public function getRoles(): mixed
    public function getRolesArray(): array
    public function check(array &$roles, array &$ACT): bool
    public function prepareACT(array $ACT): array
}
```

**注意**：Rbac 内部使用 `flea_context()` 存储用户数据，支持 Session/JWT 等多种存储方式。

### 8.2 ACL

```php
namespace FLEA\Acl;

class Manager
{
    public array $tableClass;

    public function __construct(array $tableClass = [])
    public function getUserWithPermissions($conditions): ?array
}
```

---

## 9. 辅助组件

### 9.1 Pager (分页器)

```php
namespace FLEA\Helper;

class Pager
{
    public $source;
    public ?\FLEA\Db\Driver\AbstractDriver $dbo;
    public $conditions;
    public ?string $sortby;
    public int $basePageIndex;
    public int $pageSize;
    public int $totalCount;
    public int $pageCount;
    public int $currentPage;

    public function __construct($source, $currentPage, $pageSize = 20, $conditions = null, $sortby = null, $basePageIndex = 0)
    public function findAll($fields = '*', bool $queryLinks = true): array
    public function getPagerData(bool $returnPageNumbers = true): array
}
```

### 9.2 Verifier (数据验证)

```php
namespace FLEA\Helper;

class Verifier
{
    public static function checkAll(array &$data, array &$rules, $skip = 0): array
    public static function check($value, &$rule): bool|string
}
```

### 9.3 Image (图像处理)

```php
namespace FLEA\Helper;

class Image
{
    public static function createFromFile(string $file): self
    public function saveAsJpeg(string $file, int $quality = 80): void
    public function saveAsPng(string $file): void
    public function saveAsGif(string $file): void
}
```

### 9.4 FileUploader (文件上传)

```php
namespace FLEA\Helper;

class FileUploader
{
    public array $files;
    public int $count;

    public function existsFile(string $inputName): bool
    public function getFile(string $inputName): ?File
    public function check(string $inputName, array $rules): array
    public function move(string $inputName, string $targetDir): ?string
    public function batchMove(string $inputName, string $targetDir): array
}
```

### 9.5 ImgCode (验证码)

```php
namespace FLEA\Helper;

class ImgCode
{
    public string $code;
    public int $expired;

    public function image(int $type = 0, int $length = 4, int $lefttime = 900): void
    public function check(string $code): bool
    public function checkCaseSensitive(string $code): bool
    public function clear(): void
}
```

**注意**：ImgCode 内部使用 `flea_context()` 存储验证码，支持 Session/Redis 等多种存储方式。

---

## 10. 配置系统

### 10.1 配置加载顺序

1. `FLEA\Config\Defaults` 加载框架默认配置
2. `FLEA::loadEnv()` 加载 `.env` 环境变量
3. `FLEA::loadAppInf()` 加载应用配置（覆盖默认配置）
4. 环境变量覆盖应用配置

### 10.2 关键配置项

```php
return [
    // 数据库
    'dbDSN' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'login' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'database' => env('DB_DATABASE', ''),
    ],

    // 路由
    'dispatcher' => \FLEA\Dispatcher\Simple::class,
    'defaultController' => 'Index',
    'defaultAction' => 'index',
    'actionMethodPrefix' => 'action',
    'actionMethodSuffix' => '',

    // 视图
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => ['templateDir' => 'App/View', 'cacheDir' => 'cache'],

    // 缓存
    'cacheProvider' => \FLEA\Cache\FileCache::class,

    // Context（请求上下文）
    'contextDriver' => env('CONTEXT_DRIVER', 'session'),
    'contextIdentity' => env('CONTEXT_IDENTITY', 'session'),

    // 日志
    'logEnabled' => env('LOG_ENABLED', false),
    'logFilename' => env('LOG_FILENAME', 'app.log'),

    // JWT
    'jwtSecret' => env('JWT_SECRET', ''),
    'jwtTtl' => (int) env('JWT_TTL', 7200),
];
```

---

## 11. 异常处理

### 11.1 通用异常

| 异常类 | 说明 |
|--------|------|
| `InvalidArguments` | 无效参数 |
| `MissingArguments` | 缺少参数 |
| `ExpectedClass` | 期望的类不存在 |
| `ExpectedFile` | 期望的文件不存在 |
| `MissingAction` | 动作方法不存在 |
| `MissingController` | 控制器不存在 |
| `NotImplemented` | 方法未实现 |
| `TypeMismatch` | 类型不匹配 |
| `ValidationFailed` | 验证失败 |
| `ExistsKeyName` | 键已存在 |
| `NotExistsKeyName` | 键不存在 |

### 11.2 数据库异常

| 异常类 | 说明 |
|--------|------|
| `MissingDSN` | 缺少 DSN |
| `InvalidDSN` | 无效 DSN |
| `InvalidInsertID` | 无效插入 ID |
| `MissingPrimaryKey` | 缺少主键 |
| `PrimaryKeyExists` | 主键已存在 |
| `SqlQuery` | SQL 错误 |
| `MissingLink` | 关联不存在 |

### 11.3 调度器异常

| 异常类 | 说明 |
|--------|------|
| `CheckFailed` | 参数检查失败 |

### 11.4 RBAC 异常

| 异常类 | 说明 |
|--------|------|
| `InvalidACT` | 无效的动作 |
| `InvalidACTFile` | 无效的 ACT 文件 |

### 11.5 ACL 异常

| 异常类 | 说明 |
|--------|------|
| `UserGroupNotFound` | 用户组未找到 |

---

## 12. 请求生命周期

```
1. require FLEA.php
   ↓
2. FLEA::loadEnv() 加载环境变量
   ↓
3. FLEA::loadAppInf() 加载应用配置
   ↓
4. FLEA::runMVC()
   - FLEA::init() 初始化服务
     - 设置时区
     - 注册异常处理器
     - 初始化缓存目录
     - 绑定 Context 到容器
     - 设置响应头
   ↓
5. Router::dispatch() 匹配路由
   - 匹配成功：设置 handler 和 middlewares
   - 匹配失败：返回 404
   ↓
6. 中间件管道执行
   - 全局中间件（FLEA::middleware() 注册）
   - 路由级中间件（Router::get/post 等注册）
   ↓
7. Dispatcher 解析 controller/action
   ↓
8. 实例化控制器 → beforeExecute() → actionXxx() → afterExecute()
   ↓
9. 视图渲染
   ↓
10. 输出响应（包含 X-Trace-Id 头）
```

---

## 13. 扩展点

### 13.1 自定义控制器

```php
namespace App\Controller;

use FLEA\Controller\Action;

class PostController extends Action
{
    public function actionIndex(): void
    {
        $this->getView()->assign('posts', $posts);
        $this->getView()->display('post/index.php');
    }
}
```

### 13.2 自定义模型

```php
namespace App\Model;

use FLEA\Db\TableDataGateway;

class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public string $primaryKey = 'id';

    public function getPublishedPosts(int $limit = 10, int $offset = 0): array
    {
        return $this->findAll(['status' => 1], 'created_at DESC', [$limit, $offset]);
    }
}
```

### 13.3 自定义中间件

```php
use FLEA\Middleware\MiddlewareInterface;

class MyMiddleware implements MiddlewareInterface
{
    public function handle(callable $next): void
    {
        // 前置处理
        $next();  // 调用下一个中间件或处理器
        // 后置处理
    }
}
```

### 13.4 自定义视图引擎

```php
use FLEA\View\ViewInterface;

class TwigView implements ViewInterface
{
    public function assign($key, $value = null): void {}
    public function display(string $template): void {}
    public function fetch(string $template, ?string $cacheId = null): string {}
}
```

### 13.5 自定义 Context 驱动

```php
use FLEA\Context\DriverInterface;

class CustomDriver implements DriverInterface
{
    public function get(string $key, mixed $default = null): mixed { }
    public function set(string $key, mixed $value, ?int $ttl = null): bool { }
    public function remove(string $key): bool { }
    public function has(string $key): bool { }
}
```

---

## 14. 约定规范

### 14.1 命名约定

| 类型 | 约定 | 示例 |
|------|------|------|
| 控制器 | 首字母大写 + Controller | `PostController` |
| 模型 | 表名单数形式 | `Post` |
| 动作方法 | action + 驼峰 | `actionIndex()` |
| 视图文件 | `{controller}/{action}.php` | `post/index.php` |

### 14.2 URL 格式

```
标准模式：index.php?controller=Post&action=view&id=1
PATHINFO 模式：index.php/Post/view/id/1
URL 重写模式：/Post/view/id/1
```

### 14.3 数据库约定

| 类型 | 约定 | 示例 |
|------|------|------|
| 时间戳 | `created_at`, `updated_at` |
| 主键 | `id` 或 `{table}_id` |
| 外键 | `{table}_id` |

---

## 15. PHP 7.4 特性

框架使用了以下 PHP 7.4 特性：

- **属性类型声明**: `public string $tableName`
- **可空类型**: `public ?string $sort = null`
- **箭头函数**: `fn($x) => $x * 2`
- **空合并运算符**: `$value ?? $default`
- **联合类型**: `int|bool`

---

## 16. 依赖项

| 依赖 | 版本 | 说明 |
|------|------|------|
| PHP | 7.4+ | 运行环境 |
| psr/log | ^1.0 | 日志接口 |
| psr/container | ^2.0 | 容器接口 |
| psr/simple-cache | ^1.0 | 缓存接口 |
| vlucas/phpdotenv | ^5.5 | 环境变量加载 |

---

## 17. 版本历史

### v2.0.0 (当前版本)

**重大重构**:
- 新增 PSR-11 容器 (`Container`)
- 新增 PSR-16 缓存 (`Cache`)
- 新增路由器 (`Router`/`Route`)
- 新增中间件系统 (`Middleware`/`Pipeline`)
- 新增 HTTP 封装 (`Request`/`Response`)
- 新增 JWT 认证 (`Auth/Jwt`)
- 新增 Context 上下文组件（替代 Session）
- 移除 `Ajax.php`
- 移除 `WebControls.php`
- 移除 `ActiveRecord.php`
- 移除 `Session/Db.php`

**配置变更**:
- 新增 `cacheProvider` 配置项
- 新增 `jwtSecret`/`jwtTtl` 配置项
- 新增 `contextDriver`/`contextIdentity` 配置项

**目录结构变更**:
- `src/FLEA/FLEA/` → `src/FLEA/`
- `src/FLEA/FLEA.php` → `src/FLEA.php`
- `src/FLEA/Functions.php` → `src/Functions.php`

### v1.7.1524

- PSR-4 自动加载
- PHP 7.4 类型声明
- PSR-3 日志接口
- `create()` 方法返回类型改为 `int`

---

## 18. 参考文档

- [CLAUDE.md](CLAUDE.md) - 开发规范
- [CHANGES.md](CHANGES.md) - FLEA 目录代码修改记录
- [APP_CHANGES.md](APP_CHANGES.md) - App 目录代码修改记录
- [GIT_COMMIT.md](GIT_COMMIT.md) - Git 提交记录
