# FleaPHP 博客系统

一个基于 FleaPHP 框架开发的博客系统，演示了框架的核心功能。

## 功能特性

- ✅ 文章列表展示（分页）
- ✅ 文章详情查看
- ✅ 创建新文章
- ✅ 编辑文章
- ✅ 删除文章
- ✅ 评论功能
- ✅ 响应式设计

## 环境要求

- **PHP**: 7.4+
- **MySQL**: 5.0+
- **Composer**: 依赖管理

## 安装步骤

### 1. 克隆项目

```bash
git clone <repository-url>
cd fleaphp-ex
```

### 2. 安装依赖

```bash
php74 ~/bin/composer.phar install
```

### 3. 创建数据库

```bash
mysql -u root -p < blog.sql
```

或手动执行 `blog.sql` 中的 SQL 语句创建数据库和表。

### 4. 配置数据库连接

编辑 `App/Config.php` 文件，配置数据库连接信息：

```php
'dbDSN' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'login' => 'root',
    'password' => '11111111',  // 修改为你的密码
    'database' => 'blog',
    'charset' => 'utf8mb4',
],
```

### 5. 设置缓存目录权限

```bash
chmod -R 777 cache/
```

### 6. 启动开发服务器

```bash
php74 -S 127.0.0.1:8081
```

### 7. 访问应用

打开浏览器访问：http://127.0.0.1:8081/index.php

## 项目结构

```
fleaphp-ex/
├── App/
│   ├── Config.php              # 应用配置文件
│   ├── Controller/
│   │   └── PostController.php  # 文章控制器
│   ├── Model/
│   │   ├── Post.php            # 文章模型
│   │   └── Comment.php         # 评论模型
│   └── View/
│       └── post/
│           ├── index.php       # 文章列表页
│           ├── view.php        # 文章详情页
│           ├── create.php      # 创建文章页
│           └── edit.php        # 编辑文章页
├── FLEA/                       # FleaPHP 框架核心
│   ├── FLEA.php               # 框架入口文件
│   └── FLEA/                  # 框架组件
├── cache/                      # 缓存目录
├── vendor/                     # Composer 依赖
├── blog.sql                    # 数据库初始化脚本
├── composer.json               # Composer 配置
├── index.php                   # 应用入口文件
├── USER_GUIDE.md               # 用户手册
├── SPEC.md                     # 框架规格说明
└── README.md                   # 本文件
```

## 使用说明

### 访问首页（文章列表）

```
http://127.0.0.1:8081/index.php
或
http://127.0.0.1:8081/index.php?controller=Post&action=index
```

### 查看文章详情

```
http://127.0.0.1:8081/index.php?controller=Post&action=view&id=1
```

### 创建文章

```
http://127.0.0.1:8081/index.php?controller=Post&action=create
```

### 编辑文章

```
http://127.0.0.1:8081/index.php?controller=Post&action=edit&id=1
```

### 删除文章

```
http://127.0.0.1:8081/index.php?controller=Post&action=delete&id=1
```

## 数据库表结构

### posts (文章表)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PRIMARY KEY | 主键 |
| title | VARCHAR(255) | 文章标题 |
| content | TEXT | 文章内容 |
| author | VARCHAR(100) | 作者 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |
| status | TINYINT | 状态 (0-草稿，1-发布) |

### comments (评论表)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PRIMARY KEY | 主键 |
| post_id | INT | 文章 ID (外键) |
| author | VARCHAR(100) | 评论者 |
| email | VARCHAR(255) | 邮箱 |
| content | TEXT | 评论内容 |
| created_at | DATETIME | 创建时间 |
| status | TINYINT | 状态 (0-待审核，1-已审核) |

## 开发指南

### 添加新的控制器

在 `App/Controller/` 目录下创建新的控制器类：

```php
<?php
namespace App\Controller;

use \FLEA\Controller\Action;

class MyController extends Action
{
    public function __construct()
    {
        parent::__construct('My');
    }

    public function actionIndex()
    {
        // 处理逻辑
        $this->view->display('my/index.php');
    }
}
```

### 添加新的模型

在 `App/Model/` 目录下创建新的模型类：

```php
<?php
namespace App\Model;

use \FLEA\Db\TableDataGateway;

class MyModel extends TableDataGateway
{
    public string $tableName = 'my_table';
    public $primaryKey = 'id';

    public function getActiveRecords()
    {
        return $this->findAll(['status' => 1]);
    }
}
```

### 创建视图模板

在 `App/View/` 目录下创建对应的视图文件：

```php
<!-- App/View/my/index.php -->
<!DOCTYPE html>
<html>
<head>
    <title>我的页面</title>
</head>
<body>
    <h1>欢迎访问</h1>
</body>
</html>
```

## 技术栈

- **框架**: FleaPHP (PSR-4 标准)
- **PHP**: 7.4+
- **数据库**: MySQL
- **模板引擎**: 原生 PHP
- **CSS**: 自定义响应式样式

## 配置说明

### 主要配置项 (App/Config.php)

```php
return [
    // 数据库配置
    'dbDSN' => [...],

    // 控制器配置
    'controllerAccessor' => 'controller',
    'actionAccessor' => 'action',
    'defaultController' => 'Post',
    'defaultAction' => 'index',

    // URL 配置
    'urlMode' => URL_STANDARD,  // URL_STANDARD, URL_PATHINFO, URL_REWRITE

    // 视图配置
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,
    ],

    // 日志配置
    'logEnabled' => false,

    // 错误显示（开发环境）
    'displayErrors' => true,
];
```

## 常见问题

### 1. 数据库连接失败

检查 `App/Config.php` 中的数据库配置是否正确，确保 MySQL 服务已启动。

### 2. 缓存目录权限问题

确保 `cache/` 目录可写：

```bash
chmod -R 777 cache/
```

### 3. PHP 版本不兼容

确保使用 PHP 7.4+ 版本：

```bash
php74 -v
```

## 许可证

MIT License

## 相关文档

- [USER_GUIDE.md](USER_GUIDE.md) - 用户手册
- [SPEC.md](SPEC.md) - 框架规格说明书
- [CHANGES.md](CHANGES.md) - 框架修改记录
- [GIT_COMMIT.md](GIT_COMMIT.md) - Git 提交记录

## 作者

FleaPHP 框架团队
