# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 操作规范

- 操作语言：中文
- FLEA/ 目录有重要改动后，更新 `CHANGES.md`
- App/ 目录有重要改动后，更新 `APP_CHANGES.md`
- 每次代码改动完成后，将 git commit 说明追加到 `GIT_COMMIT.md`（最新记录在最前）
- 代码改动完成后等待用户 review，用户确认后再执行 git commit
- 明确需求后再操作，不确定先问，不猜测
- 只做用户明确要求的事，完成后立即停止，不自行添加"改进"

## Setup

```bash
# 初始化数据库
mysql -u root -p < blog.sql

# 安装/更新依赖
php74 ~/bin/composer.phar install

# 启动开发服务器
php74 -S 127.0.0.1:8081
# 访问：http://127.0.0.1:8081/index.php
```

Database config defaults (change in `App/Config.php`):
- Host: `127.0.0.1:3306`, DB: `blog`, User: `root`, Password: `11111111`

Run via web server: `http://127.0.0.1:8081/index.php`

PHP version: **7.4.32**（命令：`php74`）

## Architecture

This is a FLEA framework MVC application. The framework lives in `FLEA/` and the application code in `App/`.

**Request lifecycle:** `index.php` → `FLEA::runMVC()` → `FLEA\Dispatcher\Simple` parses `?controller=X&action=Y` → instantiates `App\Controller\XController` → calls `actionY()` → controller assigns vars to view → `FLEA\View\Simple` renders `App/View/x/y.php`.

**Key patterns:**
- Controllers extend `\FLEA\Controller\Action` and expose methods named `actionFoo()`
- Models extend `\FLEA\Db\TableDataGateway` with `$tableName` and `$primaryKey` properties
- Views are plain PHP files in `App/View/{controller}/{action}.php`
- URL format: `index.php?controller=Post&action=view&id=1`

**Framework components** (in `FLEA/FLEA/`):
- `Db/TableDataGateway.php` — base model class with query helpers
- `Dispatcher/Simple.php` — routes GET params to controller/action
- `View/Simple.php` — PHP template renderer with optional file caching
- `Helper/Pager.php` — pagination utility
- `Rbac/` — role-based access control (available but not wired into the blog app)

**App configuration** (`App/Config.php`): single PHP file returning an array. Controls DB connection, dispatcher class, view class, template/cache dirs, and error display settings.

**No test suite or linter is configured** for this project.
