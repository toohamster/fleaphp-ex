# FleaPHP 用户手册 v2.0

## 目录

1. [简介](#简介)
2. [安装与配置](#安装与配置)
3. [核心概念](#核心概念)
4. [HTTP 基础](#http-基础)
5. [中间件开发](#中间件开发)
6. [控制器开发](#控制器开发)
7. [模型与数据库](#模型与数据库)
8. [视图开发](#视图开发)
9. [JWT 认证](#jwt-认证)
10. [Context 上下文](#context-上下文)
11. [日志与缓存](#日志与缓存)
12. [最佳实践](#最佳实践)

---

## 简介

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 命名空间标准和 Composer 自动加载机制，支持 PHP 7.4+。

### 主要特性

- **PSR 标准**：PSR-11 容器、PSR-16 缓存、PSR-3 日志
- **MVC 架构**：模型 - 视图 - 控制器清晰分离
- **路由器**：RESTful 路由、路由分组、命名路由
- **中间件**：洋葱模型管道
- **JWT 认证**：HS256 签名
- **Context 上下文**：可插拔的状态管理
- **TableDataGateway**：简洁的数据库 CRUD
- **关联查询**：HAS_ONE、HAS_MANY、BELONGS_TO、MANY_TO_MANY

### 系统要求

- **PHP**: 7.4+
- **Composer**: 依赖管理
- **数据库**: MySQL 5.0+ 或 PDO 支持的其他数据库

---

## 安装与配置

### 1. 安装依赖

```bash
composer install
```

### 2. 配置环境变量

复制示例配置文件：

```bash
cp .env.example .env
```

编辑 `.env` 文件：

```env
# 应用环境
APP_ENV=local
APP_DEBUG=true

# 数据库配置
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=your_password
DB_DATABASE=blog

# Context 配置
CONTEXT_DRIVER=session
CONTEXT_IDENTITY=session

# JWT 配置
JWT_SECRET=your-secret-key-change-this
JWT_TTL=7200

# 日志配置
LOG_ENABLED=false
LOG_LEVEL=debug
```

### 3. 初始化数据库

```bash
mysql -u root -p < blog.sql
```

### 4. 启动开发服务器

```bash
# 在项目根目录执行
php bin/flea-cli --project-dir=demo

# 访问 http://127.0.0.1:8081/index.php
```

---

## 核心概念

### FLEA 类

框架主入口类，提供静态方法访问核心功能：

```php
// 加载环境变量
\FLEA::loadEnv(__DIR__ . '/../.env');

// 加载应用配置
\FLEA::loadAppInf(__DIR__ . '/../App/Config.php');

// 获取配置项
$controller = \FLEA::getAppInf('defaultController');

// 获取单例对象
$container = \FLEA::getSingleton(\FLEA\Container::class);

// 启动 MVC
\FLEA::runMVC();
```

### 容器（Container）

实现 PSR-11 标准的依赖注入容器：

```php
use FLEA\Container;

$container = Container::getInstance();

// 注册对象
$container->register(new MyService(), 'myService');

// 获取单例
$service = $container->singleton(MyService::class);

// 检查是否存在
if ($container->has('myService')) {
    $service = $container->get('myService');
}
```

### 配置管理

配置加载顺序（优先级从高到低）：

1. `.env.{APP_ENV}` 环境文件
2. `.env` 基础配置
3. `App/Config.php` 应用配置
4. `FLEA\Config\Defaults` 框架默认配置

```php
// 获取配置
$dbHost = \FLEA::getAppInfValue('dbDSN', 'host');
$jwtSecret = \FLEA::getAppInf('jwtSecret', '');

// 设置配置
\FLEA::setAppInfValue('dbDSN', 'host', '192.168.1.100');
```

---

## HTTP 基础

### Request 请求封装

```php
use FLEA\Request;

$request = Request::current();

// 请求方法
$method = $request->method();
$isPost = $request->isPost();
$isAjax = $request->isAjax();

// 获取参数
$id = $request->input('id');
$name = $request->get('name', 'default');
$data = $request->post('data');

// JSON 请求体
$json = $request->json();
$userId = $request->json('user_id');

// 请求头
$token = $request->header('Authorization');
$ip = $request->ip();
$uri = $request->uri();
```

### Response 响应封装

```php
use FLEA\Response;

// 成功响应
Response::success([
    'id' => 1,
    'name' => 'John'
]);

// 错误响应
Response::error('资源未找到', 404);

// 自定义响应
Response::make()
    ->code(200)
    ->header('X-Custom', 'value')
    ->json($data);

// 分页响应
Response::paginate($items, $total, $page, $pageSize);
```

**统一响应结构**：

```json
// 成功
{"code": 0, "message": "ok", "data": {...}}

// 错误
{"code": -1, "message": "error message", "data": null}
```

### Router 路由器

```php
use FLEA\Router;

// 基本路由
Router::get('/users', 'UserController@index');
Router::post('/users', 'UserController@store');
Router::put('/users/{id}', 'UserController@update');
Router::delete('/users/{id}', 'UserController@destroy');

// 带正则参数的路由
Router::get('/users/{id:\d+}', 'UserController@show');
Router::get('/posts/{slug:[a-z-]+}', 'PostController@showBySlug');

// 命名路由
Router::get('/users', 'UserController@index')->name('users.index');
$url = Router::urlFor('users.index');

// 路由分组
Router::group('/admin', function() {
    Router::get('/dashboard', 'AdminController@dashboard');
    Router::get('/settings', 'AdminController@settings');
}, [new AuthMiddleware()]);

// 任何方法
Router::any('/webhook', 'WebhookController@handle');

// RESTful 资源路由（一行生成 7 条路由）
Router::resource('post', 'PostController');

// 只保留部分方法
Router::resource('post', 'PostController', ['only' => ['index', 'show']]);

// 排除部分方法
Router::resource('post', 'PostController', ['except' => ['create', 'edit']]);
```

**Router::resource() 生成的路由表**：

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

---

## 中间件开发

### 中间件接口

```php
namespace FLEA\Middleware;

interface MiddlewareInterface
{
    public function handle(callable $next): void;
}
```

### 自定义中间件

```php
namespace App\Middleware;

use FLEA\Middleware\MiddlewareInterface;

class CheckAdminMiddleware implements MiddlewareInterface
{
    public function handle(callable $next): void
    {
        // 前置处理：检查用户是否为管理员
        $user = flea_context()->get('user');
        if (!$user || !$user['is_admin']) {
            \FLEA\Response::error('权限不足', 403);
            return;
        }

        // 调用下一个中间件或处理器
        $next();

        // 后置处理（如果需要）
    }
}
```

### 注册中间件

```php
// 全局中间件（对所有请求生效）
\FLEA::middleware(new \FLEA\Middleware\CorsMiddleware());
\FLEA::middleware(new \App\Middleware\CheckAdminMiddleware());

// 路由级中间件
Router::get('/admin/dashboard', 'AdminController@dashboard', [
    new \FLEA\Middleware\AuthMiddleware(),
    new \App\Middleware\CheckAdminMiddleware()
]);
```

### 内置中间件

| 中间件 | 说明 |
|--------|------|
| `CorsMiddleware` | CORS 跨域支持 |
| `AuthMiddleware` | JWT 认证检查 |
| `RateLimitMiddleware` | 请求限流 |

---

## 控制器开发

### 基本结构

```php
namespace App\Controller;

use FLEA\Controller\Action;
use App\Model\Post;

class PostController extends Action
{
    protected Post $postModel;

    public function __construct()
    {
        parent::__construct('Post');
        $this->postModel = new Post();
    }

    // 生命周期回调
    public function beforeExecute($actionMethod): void
    {
        // 在 action 之前执行
    }

    public function afterExecute($actionMethod): void
    {
        // 在 action 之后执行
    }
}
```

### 动作方法

```php
class PostController extends Action
{
    // 列表页
    public function actionIndex(): void
    {
        $posts = $this->postModel->findAll(['status' => 1]);
        $this->getView()->assign('posts', $posts);
        $this->getView()->display('post/index.php');
    }

    // 详情页
    public function actionView(): void
    {
        $id = $this->request->input('id');
        $post = $this->postModel->find($id);

        if (!$post) {
            \FLEA\Response::error('文章未找到', 404);
            return;
        }

        $this->getView()->assign('post', $post);
        $this->getView()->display('post/view.php');
    }

    // 创建（GET 显示表单，POST 处理提交）
    public function actionCreate(): void
    {
        if ($this->request->isPost()) {
            $data = [
                'title' => $this->request->post('title'),
                'content' => $this->request->post('content'),
            ];
            $id = $this->postModel->create($data);
            \FLEA\Response::success(['id' => $id]);
            return;
        }

        $this->getView()->display('post/create.php');
    }

    // 编辑
    public function actionEdit(): void
    {
        $id = $this->request->input('id');
        $post = $this->postModel->find($id);

        if ($this->request->isPost()) {
            $data = [
                'id' => $id,
                'title' => $this->request->post('title'),
                'content' => $this->request->post('content'),
            ];
            $this->postModel->update($data);
            \FLEA\Response::success();
            return;
        }

        $this->getView()->assign('post', $post);
        $this->getView()->display('post/edit.php');
    }

    // 删除
    public function actionDelete(): void
    {
        $id = $this->request->input('id');
        $this->postModel->remove($id);
        \FLEA\Response::success();
    }
}
```

### 辅助方法

```php
class PostController extends Action
{
    public function actionExample(): void
    {
        // 获取视图对象
        $view = $this->getView();

        // 获取调度器
        $dispatcher = $this->getDispatcher();

        // URL 生成
        $url = $this->url('view', ['id' => 1]);
        $url = $this->url(null, ['page' => 2]); // 当前控制器

        // 重定向
        $this->forward('Post', 'index');

        // 判断请求类型
        if ($this->isPost()) { }
        if ($this->isAjax()) { }
    }
}
```

---

## 模型与数据库

### TableDataGateway 基础

```php
namespace App\Model;

use FLEA\Db\TableDataGateway;

class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public string $primaryKey = 'id';

    // 自定义查询方法
    public function getPublishedPosts(int $limit = 10, int $offset = 0): array
    {
        return $this->findAll(
            ['status' => 1],
            'created_at DESC',
            [$limit, $offset]
        );
    }
}
```

### CRUD 操作

```php
$postModel = new Post();

// 查询
$post = $postModel->find(1);                          // 根据主键查询
$post = $postModel->find(['status' => 1], 'id DESC'); // 条件查询
$posts = $postModel->findAll();                       // 查询所有
$posts = $postModel->findAll(['status' => 1], 'id DESC', [10, 0]); // 分页
$count = $postModel->findCount(['status' => 1]);      // 计数

// 创建
$id = $postModel->create([
    'title' => 'New Post',
    'content' => 'Content here',
    'status' => 1,
]);

// 更新
$postModel->update([
    'id' => 1,
    'title' => 'Updated Title',
]);

// 删除
$postModel->remove(1);
$postModel->removeByPkv(1);
```

### 关联关系

```php
class Post extends TableDataGateway
{
    public string $tableName = 'posts';

    // HAS_MANY: 一篇文章有多条评论
    public array $hasMany = [
        [
            'tableClass' => Comment::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'comments',
        ],
    ];

    // HAS_ONE: 一篇文章有一个作者
    public array $hasOne = [
        [
            'tableClass' => User::class,
            'foreignKey' => 'user_id',
            'mappingName' => 'author',
        ],
    ];
}

class Comment extends TableDataGateway
{
    public string $tableName = 'comments';

    // BELONGS_TO: 评论属于一篇文章
    public array $belongsTo = [
        [
            'tableClass' => Post::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'post',
        ],
    ];
}

// 使用关联查询
$post = $postModel->find(1, null, '*', true); // true = 加载关联
$comments = $post['comments'];  // HAS_MANY 关联
$author = $post['author'];      // HAS_ONE 关联
```

### MANY_TO_MANY 关联

```php
class User extends TableDataGateway
{
    public string $tableName = 'users';

    // 多对多：用户 - 角色
    public array $manyToMany = [
        [
            'tableClass' => Role::class,
            'foreignKey' => 'user_id',
            'associationTable' => 'user_roles',
            'associationForeignKey' => 'role_id',
            'mappingName' => 'roles',
        ],
    ];
}
```

---

## 视图开发

### Simple 视图引擎

```php
// 控制器中
$view = $this->getView();

// 赋值
$view->assign('title', '页面标题');
$view->assign('posts', $posts);
$view->assign(['user' => $user, 'roles' => $roles]);

// 渲染
$view->display('post/index.php');

// 或获取 HTML 内容
$html = $view->fetch('post/index.php');
```

### 模板文件

```php
<!-- App/View/post/index.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
    <h1>文章列表</h1>

    <?php foreach ($posts as $post): ?>
        <div class="post">
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <p><?php echo nl2br($post['content']); ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

### NullView（无视图）

用于 API 等不需要视图的场景：

```php
// Config.php
return [
    'view' => \FLEA\View\NullView::class,
];

// 或控制器中
public function actionApi(): void
{
    \FLEA\Response::success($data);
}
```

---

## JWT 认证

### 签发 Token

```php
use FLEA\Auth\Jwt;

// 基本签发
$token = Jwt::encode([
    'user_id' => 123,
    'username' => 'john',
]);

// 指定有效期（秒）
$token = Jwt::encode(['user_id' => 123], 3600);

// 完整配置
$token = Jwt::encode([
    'user_id' => 123,
    'exp' => time() + 3600,  // 过期时间
    'iat' => time(),          // 签发时间
    'iss' => 'my-app',        // 签发者
]);
```

### 验证 Token

```php
use FLEA\Auth\Jwt;
use FLEA\Auth\JwtException;

try {
    // 解码并验证
    $payload = Jwt::decode($token);

    // 或者只验证不获取 payload
    if (Jwt::verify($token)) {
        // Token 有效
    }

} catch (JwtException $e) {
    // 验证失败：Token 过期、签名无效等
    \FLEA\Response::error('Token 无效', 401);
}
```

### AuthMiddleware 中间件

```php
// 路由中使用
Router::get('/api/profile', 'UserController@profile', [
    new \FLEA\Middleware\AuthMiddleware()
]);

// 或全局注册
\FLEA::middleware(new \FLEA\Middleware\AuthMiddleware());
```

### 在控制器中获取当前用户

```php
class UserController extends Action
{
    public function actionProfile(): void
    {
        // 从 Context 获取用户信息（由 AuthMiddleware 设置）
        $user = flea_context()->get('user');

        if (!$user) {
            \FLEA\Response::error('未登录', 401);
            return;
        }

        \FLEA\Response::success($user);
    }
}
```

---

## Context 上下文

Context 提供请求级别的状态管理，替代传统的 `$_SESSION`。

### 基本使用

```php
use FLEA\Context\Context;

// 通过容器获取
$context = \FLEA::getSingleton(Context::class);

// 或使用全局辅助函数
$context = flea_context();

// 存储数据
flea_context()->set('user_id', 123);
flea_context()->set('cart', ['item1', 'item2']);

// 读取数据
$user_id = flea_context()->get('user_id');
$cart = flea_context()->get('cart', []); // 带默认值

// 检查键是否存在
if (flea_context()->has('user_id')) {
    // ...
}

// 删除数据
flea_context()->remove('user_id');
```

### 配置 Context 驱动

```php
// App/Config.php
return [
    // 存储驱动：session/redis/file/database
    'contextDriver' => env('CONTEXT_DRIVER', 'session'),

    // 身份标识：session/jwt/api-key/request-id
    'contextIdentity' => env('CONTEXT_IDENTITY', 'session'),
];
```

### 不同驱动的配置

```env
# Session 驱动（默认）
CONTEXT_DRIVER=session

# Redis 驱动
CONTEXT_DRIVER=redis
CONTEXT_REDIS_HOST=127.0.0.1
CONTEXT_REDIS_PORT=6379
CONTEXT_REDIS_PASSWORD=
CONTEXT_REDIS_PREFIX=fleaphp:context:

# File 驱动
CONTEXT_DRIVER=file
CONTEXT_FILE_PATH=/path/to/context/data

# Database 驱动
CONTEXT_DRIVER=database
CONTEXT_DB_TABLE=contexts
CONTEXT_DB_FIELD_ID=context_id
CONTEXT_DB_FIELD_DATA=context_data
```

### 身份标识

```env
# Session ID（默认）
CONTEXT_IDENTITY=session

# JWT 用户
CONTEXT_IDENTITY=jwt
JWT_SECRET=your-secret-key

# API Key
CONTEXT_IDENTITY=api-key
CONTEXT_API_KEY_HEADER=X-API-Key

# Request ID
CONTEXT_IDENTITY=request-id
CONTEXT_REQUEST_ID_HEADER=X-Request-ID
```

---

## 日志与缓存

### 日志服务

```php
use FLEA;

// 获取日志实例
$log = FLEA::getSingleton(FLEA\Log::class);

// 记录日志
$log->debug('调试信息', ['user_id' => 123]);
$log->info('用户登录', ['username' => 'john']);
$log->warning('警告信息');
$log->error('错误发生', ['error' => $e->getMessage()]);
$log->critical('严重错误');

// 获取 Trace ID（用于请求追踪）
$traceId = $log->getTraceId();

// 响应头中返回 Trace ID
header('X-Trace-Id: ' . $traceId);
```

### 缓存服务

```php
// 获取缓存
$data = FLEA::getCache('user_123');
$data = FLEA::getCache('user_123', 3600); // 指定有效期

// 写入缓存
FLEA::writeCache('user_123', $userData);

// 删除缓存
FLEA::purgeCache('user_123');
```

### 缓存驱动配置

```php
// App/Config.php
return [
    // 缓存驱动
    'cacheProvider' => \FLEA\Cache\FileCache::class,

    // 或使用 Redis
    // 'cacheProvider' => \FLEA\Cache\RedisCache::class,
];
```

---

## 最佳实践

### 项目结构

```
your-app/
├── App/
│   ├── Config.php          # 应用配置
│   ├── Controller/         # 控制器
│   │   ├── Admin/          # 后台控制器
│   │   └── Api/            # API 控制器
│   ├── Model/              # 模型
│   │   └── User.php
│   ├── Middleware/         # 自定义中间件
│   │   └── CheckAdminMiddleware.php
│   └── View/               # 视图
│       ├── layouts/        # 布局模板
│       └── user/           # 用户相关视图
├── public/
│   └── index.php           # Web 入口
├── .env                    # 环境配置
└── vendor/                 # 依赖
```

### 命名约定

| 类型 | 约定 | 示例 |
|------|------|------|
| 控制器 | 复数 + Controller | `UsersController` |
| 模型 | 单数 | `User` |
| 动作方法 | action + 驼峰 | `actionUserProfile()` |
| 视图文件 | `{controller}/{action}.php` | `user/profile.php` |

### 错误处理

```php
// 在 Config.php 中配置
return [
    // 开发环境开启错误显示
    'displayErrors' => env('APP_DEBUG', false),

    // 生产环境使用友好错误
    'friendlyErrorsMessage' => true,
];

// 或在控制器中捕获异常
public function actionShow(): void
{
    try {
        $post = $this->postModel->find($id);
    } catch (\Exception $e) {
        \FLEA\Response::error('加载失败', 500);
        return;
    }
}
```

### 数据验证

```php
// 在模型中定义验证规则
class Post extends TableDataGateway
{
    public array $validateRules = [
        'title' => [
            'required' => true,
            'minLength' => 5,
            'maxLength' => 255,
        ],
        'content' => [
            'required' => true,
            'minLength' => 10,
        ],
    ];
}

// 自动验证（需在创建实例时启用）
$postModel = new Post(['autoValidating' => true]);
$postModel->create($data);
```

---

## 常见问题

### 1. 数据库连接失败

检查 `.env` 文件中的数据库配置是否正确，确保 MySQL 服务已启动。

### 2. 缓存目录权限

确保 `cache/` 目录可写：

```bash
chmod -R 777 cache/
```

### 3. 自动加载问题

运行以下命令重新生成自动加载文件：

```bash
composer dump-autoload
```

### 4. PHP 版本检查

确保使用 PHP 7.4+：

```bash
php74 -v
```

---

## 参考文档

- [SPEC.md](SPEC.md) - 框架规格说明书
- [README.md](README.md) - 项目主页
- [demo/APP_USAGE_GUIDE.md](demo/APP_USAGE_GUIDE.md) - 博客应用使用手册
