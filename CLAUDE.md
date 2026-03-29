# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 操作规范

- 操作语言：中文
- **修改任何代码前必须先给方案，用户确认后再执行，禁止擅自修改代码**
- **每次开始新任务前，必须重新阅读本规则**
- **修改文件内容时必须逐个文件阅读和编辑，禁止使用正则批量替换**
- `SPEC.md` 是框架的规格说明书，作为后续开发任务的参考基准
- FLEA/ 目录代码有变更时，在提交前用最新代码更新 `SPEC.md`（保持框架规格说明书与代码同步）
- FLEA/ 目录有重要改动后，更新根目录下的 `CHANGES.md`
- demo/ 目录有重要改动后，更新 `demo/APP_CHANGES.md`
- 每次代码改动完成后，将 git commit 说明追加到 `GIT_COMMIT.md`（最新记录在最前）
- 代码改动完成后等待用户 review，用户确认后再执行 git commit
- 明确需求后再操作，不确定先问，不猜测
- 只做用户明确要求的事，完成后立即停止，不自行添加"改进"
- 发起 Merge Request 和打 Tag 使用 GitHub API（`curl`）直接操作，不使用 `gh` CLI
- **修改框架代码前必须先给方案，用户确认后再执行，禁止擅自批量修改**
- **禁止使用正则批量替换文件内容，必须逐个文件阅读和编辑**

## Setup

```bash
# 初始化数据库
mysql -u root -p < demo/blog.sql

# 安装/更新依赖
php74 ~/bin/composer.phar install

# 启动开发服务器
php demo/bin/serve
# 访问：http://127.0.0.1:8081/index.php
```

Database config defaults (change in `demo/App/Config.php`):
- Host: `127.0.0.1:3306`, DB: `blog`, User: `root`, Password: `11111111`

Run via web server: `http://127.0.0.1:8081/index.php`

PHP version: **7.4.32**（命令：`php74`）

## Architecture

FLEA 框架 MVC 演示应用。框架代码在 `src/FLEA/`，演示应用代码在 `demo/App/`。

### 请求生命周期

```
1. require FLEA.php → 加载默认配置
2. FLEA::loadAppInf() 加载应用配置
3. FLEA::runMVC()
   - 初始化服务（时区、异常处理、缓存、Session）
   - Router::dispatch() 匹配路由（可选）
   - 中间件管道执行
   - Dispatcher 解析 controller/action
   - 控制器：beforeExecute() → actionXxx() → afterExecute()
   - 视图渲染
```

### 核心组件（PSR 标准）

| 组件 | 标准 | 说明 |
|------|------|------|
| `FLEA\Container` | PSR-11 | 对象容器 |
| `FLEA\Cache` | PSR-16 | 缓存门面（FileCache/RedisCache） |
| `FLEA\Log` | PSR-3 | 日志服务 |
| `FLEA\Database` | - | 数据库连接池 |
| `FLEA\Config` | - | 配置管理（单例） |

### 新增特性

- **Router**: RESTful 路由、路由分组、命名路由、中间件支持
- **Middleware**: 洋葱模型管道（Cors/Auth/RateLimit）
- **Request/Response**: HTTP 封装、JSON body 解析、统一响应结构
- **Auth/Jwt**: JWT 签发与验证（HS256）

### 配置项（demo/App/Config.php）

```php
return [
    'dbDSN' => 'mysql://root:pass@localhost/blog',
    'dispatcher' => \FLEA\Dispatcher\Simple::class,
    'view' => \FLEA\View\Simple::class,
    'cacheProvider' => \FLEA\Cache\FileCache::class,  // PSR-16
    'jwtSecret' => 'your-secret-key',                  // JWT 密钥
    'jwtTtl' => 7200,                                  // JWT 有效期
];
```

**无测试套件和 linter。**
