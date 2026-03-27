# FleaPHP 框架规格说明书 v2.0

## 1. 概述

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 自动加载机制，支持 PHP 7.4+。

| 项目 | 说明 |
|------|------|
| **版本** | 2.0.0 |
| **命名空间** | `FLEA\` |
| **PHP 要求** | 7.4+ |
| **许可证** | 查看 LICENSE.txt |

### 1.1 PSR 标准合规

| 组件 | PSR 标准 | 说明 |
|------|----------|------|
| `FLEA\Container` | PSR-11 | 依赖注入容器 |
| `FLEA\Cache` | PSR-16 | 缓存接口 |
| `FLEA\Log` | PSR-3 | 日志接口 |

---

## 2. 目录结构

```
FLEA/
├── FLEA.php                    # 框架入口文件
├── Functions.php               # 全局函数
└── FLEA/                       # 框架核心代码
    ├── Auth/                   # 认证支持
    │   ├── Jwt.php             # JWT 工具 (HS256)
    │   └── JwtException.php    # JWT 异常
    ├── Cache/                  # 缓存驱动
    │   ├── FileCache.php       # 文件缓存 (PSR-16)
    │   └── RedisCache.php      # Redis 缓存 (PSR-16)
    ├── Controller/             # 控制器基类
    │   └── Action.php          # 动作控制器基类
    ├── Db/                     # 数据库相关组件
    │   ├── Driver/             # 数据库驱动
    │   │   ├── AbstractDriver.php
    │   │   └── Mysql.php
    │   ├── Exception/          # 数据库异常
    │   ├── TableLink/          # 表关联处理
    │   │   ├── HasOneLink.php
    │   │   ├── BelongsToLink.php
    │   │   ├── HasManyLink.php
    │   │   └── ManyToManyLink.php
    │   ├── SqlHelper.php       # SQL 辅助
    │   ├── SqlStatement.php    # SQL 语句处理
    │   ├── TableDataGateway.php # 表数据入口 (CRUD)
    │   └── TableLink.php       # 表关联基类
    ├── Dispatcher/             # 请求调度器
    │   ├── Exception/
    │   │   └── CheckFailed.php
    │   ├── Auth.php            # 认证调度器
    │   └── Simple.php          # 简单调度器
    ├── Error/                  # 错误处理
    │   ├── ErrorRenderer.php   # 错误渲染器
    │   └── views/
    │       └── 500.php
    ├── Exception/              # 框架通用异常
    ├── Helper/                 # 辅助类
    │   ├── FileUploader/
    │   │   └── File.php
    │   ├── FileUploader.php    # 文件上传
    │   ├── Image.php           # 图像处理
    │   ├── ImgCode.php         # 验证码
    │   ├── Pager.php           # 分页器
    │   ├── SendFile.php        # 文件下载
    │   └── Verifier.php        # 数据验证
    ├── Middleware/             # 中间件
    │   ├── MiddlewareInterface.php
    │   ├── Pipeline.php        # 中间件管道
    │   ├── CorsMiddleware.php  # CORS 中间件
    │   ├── AuthMiddleware.php  # 认证中间件
    │   └── RateLimitMiddleware.php  # 限流中间件
    ├── Rbac/                   # RBAC 子组件
    │   ├── Exception/
    │   │   ├── InvalidACT.php
    │   │   └── InvalidACTFile.php
    │   ├── RolesManager.php    # 角色管理
    │   └── UsersManager.php    # 用户管理
    ├── Acl/                    # ACL 子组件
    │   ├── Exception/
    │   │   └── UserGroupNotFound.php
    │   ├── Table/              # ACL 数据表
    │   ├── Manager.php         # ACL 管理器
    │   ├── testACL.php
    │   └── testCreateData.php
    ├── View/                   # 视图引擎
    │   ├── ViewInterface.php   # 视图接口
    │   ├── Simple.php          # 简单模板引擎
    │   └── NullView.php        # 空视图
    ├── Session/                # Session 处理
    │   └── Db.php              # 数据库 Session
    ├── Cache.php               # 缓存门面 (PSR-16)
    ├── Config.php              # 配置管理器 (单例)
    ├── Container.php           # 对象容器 (PSR-11)
    ├── Database.php            # 数据库连接管理
    ├── Exception.php           # 基础异常类
    ├── Language.php            # 多语言支持
    ├── Log.php                 # 日志服务 (PSR-3)
    ├── Request.php             # HTTP 请求封装
    ├── Response.php            # HTTP 响应封装
    ├── Router.php              # HTTP 路由器
    ├── Route.php               # 单条路由
    └── Rbac.php                # RBAC 服务类
└── 3rd/                        # 第三方库
    └── Spyc/
        └── spyc.php            # YAML 解析器
```

---

## 3. 核心服务类

### 3.1 FLEA 类

框架主入口类，所有方法为静态方法，委托给各服务类：

```php
class FLEA
{
    // 配置管理
    public static function loadAppInf($config): void
    public static function getAppInf(string $option, $default = null)
    public static function setAppInf($option, $data = null): void
    public static function setAppInfValue(string $option, string $keyname, $value): void

    // 对象容器
    public static function getSingleton(string $className): object
    public static function register(object $obj, ?string $name = null): object
    public static function isRegistered(string $name): bool

    // 数据库
    public static function getDBO($dsn = 0): \FLEA\Db\Driver\AbstractDriver

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
    public function get(string $key, $default = null): mixed  // 向后兼容
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
    public ?array $errorLevel;

    public function log($level, $message, array $context = []): void  // PSR-3
    public function flush(): void
    public function getTraceId(): string
}
```

---

## 4. HTTP 组件

### 4.1 Request (请求封装)

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

### 4.2 Response (响应封装)

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

### 4.3 Router (路由器)

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

### 4.4 Route (单条路由)

```php
namespace FLEA;

class Route
{
    public function name(string $name): self  // 命名路由，支持链式调用
}
```

### 4.5 Middleware (中间件)

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

### 4.6 Auth/Jwt (JWT 认证)

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

## 5. MVC 架构

### 5.1 控制器 (Controller)

```php
namespace FLEA\Controller;

class Action
{
    protected string $controllerName;
    protected string $actionName;
    protected ?\FLEA\Dispatcher\Simple $dispatcher;
    public $components = [];
    protected array $renderCallbacks;

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

### 5.2 视图 (View)

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
    public array $vars;
    public array $cacheState;

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
    public function assign($key, $value = null): void
    public function display(string $template): void
    public function fetch(string $template, ?string $cacheId = null): string
}
```

### 5.3 调度器 (Dispatcher)

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

## 6. 数据库组件

### 6.1 TableDataGateway

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

### 6.2 Database Driver

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

### 6.3 SqlStatement

SQL 语句构建辅助类。

---

## 7. 权限管理

### 7.1 RBAC

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

### 7.2 ACL

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

## 8. 辅助组件

### 8.1 Pager (分页器)

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

### 8.2 Verifier (数据验证)

```php
namespace FLEA\Helper;

class Verifier
{
    public static function checkAll(array &$data, array &$rules, $skip = 0): array
    public static function check($value, &$rule): bool|string
}
```

### 8.3 Image (图像处理)

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

### 8.4 FileUploader (文件上传)

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

---

## 9. 配置系统

### 9.1 配置加载

1. `FLEA.php` 自动加载
2. 根据 `DEPLOY_MODE` 加载默认配置
3. `FLEA::loadAppInf()` 加载应用配置

### 9.2 关键配置项

```php
return [
    // 数据库
    'dbDSN' => 'mysql://user:pass@localhost/dbname',

    // 路由
    'dispatcher' => \FLEA\Dispatcher\Simple::class,
    'controllerAccessor' => 'controller',
    'actionAccessor' => 'action',

    // 视图
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => ['templateDir' => './View', 'cacheDir' => './cache'],

    // 缓存
    'cacheProvider' => \FLEA\Cache\FileCache::class,

    // Session
    'sessionProvider' => \FLEA\Session\Db::class,

    // 日志
    'logFileDir' => './logs',
    'logFilename' => 'app.log',

    // JWT
    'jwtSecret' => 'your-secret-key',
    'jwtTtl' => 7200,
];
```

---

## 10. 异常处理

### 10.1 通用异常

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

### 10.2 数据库异常

| 异常类 | 说明 |
|--------|------|
| `MissingDSN` | 缺少 DSN |
| `InvalidDSN` | 无效 DSN |
| `InvalidInsertID` | 无效插入 ID |
| `MissingPrimaryKey` | 缺少主键 |
| `PrimaryKeyExists` | 主键已存在 |
| `SqlQuery` | SQL 错误 |
| `MissingLink` | 关联不存在 |

---

## 11. 请求生命周期

```
1. require FLEA.php
   ↓
2. 根据 DEPLOY_MODE 加载默认配置
   ↓
3. FLEA::loadAppInf() 加载应用配置
   ↓
4. FLEA::runMVC()
   - 初始化服务（时区、异常处理、缓存、Session）
   - 输出 X-Trace-Id 响应头
   ↓
5. Router::dispatch() 匹配路由
   ↓
6. 中间件管道执行
   ↓
7. Dispatcher 解析 controller/action
   ↓
8. 实例化控制器 → beforeExecute() → actionXxx() → afterExecute()
   ↓
9. 视图渲染
   ↓
10. 输出响应
```

---

## 12. 扩展点

### 12.1 自定义控制器

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

### 12.2 自定义模型

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

### 12.3 自定义中间件

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

### 12.4 自定义视图引擎

```php
use FLEA\View\ViewInterface;

class TwigView implements ViewInterface
{
    public function assign($key, $value = null): void {}
    public function display(string $template): void {}
    public function fetch(string $template, ?string $cacheId = null): string {}
}
```

---

## 13. 约定规范

### 13.1 命名约定

| 类型 | 约定 | 示例 |
|------|------|------|
| 控制器 | 首字母大写 + Controller | `PostController` |
| 模型 | 表名单数形式 | `Post` |
| 动作方法 | action + 驼峰 | `actionIndex()` |
| 视图文件 | `{controller}/{action}.php` | `post/index.php` |

### 13.2 URL 格式

```
标准模式：index.php?controller=Post&action=view&id=1
PATHINFO 模式：index.php/Post/view/id/1
URL 重写模式：/Post/view/id/1
```

### 13.3 数据库约定

| 类型 | 约定 | 示例 |
|------|------|------|
| 时间戳 | `created_at`, `updated_at` |
| 主键 | `id` 或 `{table}_id` |
| 外键 | `{table}_id` |

---

## 14. PHP 7.4 特性

框架使用了以下 PHP 7.4 特性：

- **属性类型声明**: `public string $tableName`
- **可空类型**: `public ?string $sort = null`
- **箭头函数**: `fn($x) => $x * 2`
- **空合并运算符**: `$value ?? $default`
- **联合类型**: `int|bool`

---

## 15. 依赖项

| 依赖 | 版本 | 说明 |
|------|------|------|
| PHP | 7.4+ | 运行环境 |
| psr/log | ^1.1 | 日志接口 |
| psr/container | ^1.1 | 容器接口 |
| psr/simple-cache | ^1.0 | 缓存接口 |

---

## 16. 版本历史

### v2.0.0 (当前版本)

**重大重构**:
- 新增 PSR-11 容器 (`Container`)
- 新增 PSR-16 缓存 (`Cache`)
- 新增路由器 (`Router`/`Route`)
- 新增中间件系统 (`Middleware`/`Pipeline`)
- 新增 HTTP 封装 (`Request`/`Response`)
- 新增 JWT 认证 (`Auth/Jwt`)
- 移除 `Ajax.php`
- 移除 `WebControls.php`
- 移除 `ActiveRecord.php`

**配置变更**:
- 新增 `cacheProvider` 配置项
- 新增 `jwtSecret`/`jwtTtl`/`jwtIssuer` 配置项

### v1.7.1524

- PSR-4 自动加载
- PHP 7.4 类型声明
- PSR-3 日志接口
- `create()` 方法返回类型改为 `int`

---

## 17. 参考文档

- [CLAUDE.md](CLAUDE.md) - 开发规范
- [CHANGES.md](CHANGES.md) - FLEA 目录代码修改记录
- [APP_CHANGES.md](APP_CHANGES.md) - App 目录代码修改记录
- [GIT_COMMIT.md](GIT_COMMIT.md) - Git 提交记录
