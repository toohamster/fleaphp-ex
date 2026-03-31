# FleaPHP v2.0

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://www.php.net/)

轻量级 PHP MVC 框架，采用 PSR 标准，支持 Router、Middleware、JWT 认证和 Context 上下文管理。

---

## 特性

- **PSR 标准**：PSR-11 容器、PSR-16 缓存、PSR-3 日志
- **MVC 架构**：清晰的模型 - 视图 - 控制器分离
- **路由器**：RESTful 路由、路由分组、命名路由
- **中间件**：洋葱模型管道，支持 CORS/认证/限流
- **JWT 认证**：HS256 签名，支持 Token 刷新
- **Context 上下文**：可插拔的状态管理（Session/Redis/File/Database）
- **TableDataGateway**：简洁的数据库 CRUD 和关联查询
- **RBAC/ACL**：基于角色的权限控制和访问控制列表

---

## 快速开始

### 方式一：通过 Composer 安装

```bash
composer require toohamster/fleaphp-ex
```

### 方式二：克隆项目

```bash
git clone https://github.com/toohamster/fleaphp-ex.git
cd fleaphp-ex
```

### 安装依赖

```bash
php74 ~/bin/composer.phar install
```

### 3. 配置环境变量

```bash
# 复制示例配置
cp demo/.env.example demo/.env

# 编辑 demo/.env 配置数据库等信息
```

### 4. 初始化数据库

```bash
mysql -u root -p < demo/blog.sql
```

### 5. 启动开发服务器

```bash
# 在项目根目录执行
php bin/flea-cli --project-dir=demo

# 访问 http://127.0.0.1:8081/index.php
```

---

## 目录结构

```
fleaphp-ex/
├── src/
│   ├── FLEA.php            # 框架入口
│   ├── Functions.php       # 全局函数
│   └── FLEA/               # 框架核心代码
│       ├── Auth/           # JWT 认证
│       ├── Cache/          # 缓存驱动
│       ├── Config/         # 配置管理
│       ├── Context/        # 上下文组件（新增）
│       ├── Controller/     # 控制器基类
│       ├── Db/             # 数据库组件
│       ├── Dispatcher/     # 调度器
│       ├── Error/          # 错误处理
│       ├── Exception/      # 异常类
│       ├── Helper/         # 辅助类
│       ├── Middleware/     # 中间件（新增）
│       ├── Rbac/           # RBAC 权限
│       ├── Acl/            # ACL 访问控制
│       ├── View/           # 视图引擎
│       ├── Request.php     # HTTP 请求（新增）
│       ├── Response.php    # HTTP 响应（新增）
│       ├── Router.php      # 路由器（新增）
│       └── Log.php         # 日志服务
├── demo/
│   ├── .env                # 环境配置
│   ├── .env.example        # 配置示例
│   ├── App/
│   │   ├── Config.php      # 应用配置
│   │   ├── Controller/     # 应用控制器
│   │   ├── Model/          # 应用模型
│   │   └── View/           # 应用视图
│   ├── public/
│   │   └── index.php       # Web 入口
│   └── blog.sql            # 数据库脚本
├── docs-book/              # 图书项目预留目录
├── LICENSE                 # MIT 许可证
├── README.md               # 本文件
├── SPEC.md                 # 框架规格说明书
└── USER_GUIDE.md           # 用户手册
```

---

## 核心组件

| 组件 | 标准 | 说明 |
|------|------|------|
| `FLEA\Container` | PSR-11 | 依赖注入容器 |
| `FLEA\Cache` | PSR-16 | 缓存门面（FileCache/RedisCache） |
| `FLEA\Log` | PSR-3 | 日志服务 |
| `FLEA\Database` | - | 数据库连接池 |
| `FLEA\Config` | - | 配置管理（单例） |
| `FLEA\Context` | - | 请求上下文（新增） |
| `FLEA\Router` | - | HTTP 路由器（新增） |
| `FLEA\Middleware` | - | 中间件管道（新增） |

---

## 基础用法

### 路由定义

```php
// 基本路由
Router::get('/users', 'UserController@index');
Router::post('/users', 'UserController@store');

// 带参数路由
Router::get('/users/{id:\d+}', 'UserController@show');

// 路由分组
Router::group('/admin', function() {
    Router::get('/stats', 'AdminController@stats');
}, [new AuthMiddleware()]);
```

### 中间件

```php
// 注册全局中间件
\FLEA::middleware(new CorsMiddleware());
\FLEA::middleware(new AuthMiddleware());

// 自定义中间件
class MyMiddleware implements \FLEA\Middleware\MiddlewareInterface
{
    public function handle(callable $next): void
    {
        // 前置处理
        $next();
        // 后置处理
    }
}
```

### Context 上下文

```php
// 存储数据
flea_context()->set('user_id', 123);

// 读取数据
$user_id = flea_context()->get('user_id');

// 检查键是否存在
if (flea_context()->has('user_id')) {
    // ...
}
```

### JWT 认证

```php
use FLEA\Auth\Jwt;

// 签发 Token
$token = Jwt::encode(['user_id' => 123], 7200);

// 验证 Token
$payload = Jwt::decode($token);

// 验证有效性
if (Jwt::verify($token)) {
    // Token 有效
}
```

---

## 配置说明

### 数据库配置（.env）

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=your_password
DB_DATABASE=blog
```

### Context 配置

```env
# 驱动：session/redis/file/database
CONTEXT_DRIVER=session

# 身份标识：session/jwt/api-key/request-id
CONTEXT_IDENTITY=session
```

### JWT 配置

```env
JWT_SECRET=your-secret-key
JWT_TTL=7200
```

---

## 环境要求

- **PHP**: 7.4+
- **Composer**: 依赖管理
- **数据库**: MySQL 5.0+ 或 PDO 支持的其他数据库

---

## 文档

| 文档 | 说明 |
|------|------|
| [SPEC.md](SPEC.md) | 框架规格说明书 |
| [USER_GUIDE.md](USER_GUIDE.md) | 用户手册 |
| [demo/APP_USAGE_GUIDE.md](demo/APP_USAGE_GUIDE.md) | 博客应用使用手册 |
| [demo/BLOG_SETUP.md](demo/BLOG_SETUP.md) | 博客安装指南 |
| [CHANGES.md](CHANGES.md) | 框架修改记录 |

---

## 许可证

本项目采用 [MIT 许可证](LICENSE) 开源。

**注意**：框架代码采用 MIT 许可，但 `docs-book/` 目录下的图书项目版权归作者所有，未经许可不得用于商业出版。

---

## 作者

FleaPHP 框架团队
