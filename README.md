# FleaPHP

FleaPHP 是一个轻量级的 PHP 框架，提供了完整的 MVC 开发支持、数据库抽象层、缓存管理等功能。

## 版本信息

当前版本已全面迁移到 PSR-4 命名空间标准和 Composer 自动加载。

## 特性

- **轻量级**：核心代码精简，性能高效
- **MVC 架构**：支持模型-视图-控制器模式
- **数据库抽象层**：支持多种数据库，统一的操作接口
- **PSR-4 自动加载**：基于 Composer 的 PSR-4 标准自动加载
- **对象容器**：单例模式管理对象实例
- **缓存系统**：内置文件缓存支持
- **灵活配置**：支持调试和生产两种模式

## 系统要求

- PHP 7.0 或更高版本
- Composer（用于依赖管理和自动加载）
- 支持的数据库：MySQL, PostgreSQL, SQLite 等

## 安装

### 1. 克隆或下载项目

```bash
git clone <repository-url> fleaphp-ex
cd fleaphp-ex
```

### 2. 安装 Composer 依赖

```bash
composer install
```

这将根据 `composer.json` 配置生成 `vendor/autoload.php` 自动加载文件。

### 3. 配置应用

创建配置文件 `config.php`：

```php
<?php
return [
    // 数据库配置
    'dbDSN' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'login'    => 'username',
        'password' => 'password',
        'database' => 'your_database',
        'charset'  => 'utf8',
    ],
    'dbTablePrefix' => 'tbl_',

    // URL 配置
    'urlMode' => URL_PATHINFO, // 或 URL_STANDARD, URL_REWRITE
    'urlLowerChar' => false,
    'defaultController' => 'Index',
    'defaultAction' => 'index',

    // 字符集配置
    'defaultLanguage' => 'chinese-utf8',
    'responseCharset' => 'UTF-8',
    'databaseCharset' => 'UTF-8',

    // 缓存配置
    'internalCacheDir' => dirname(__FILE__) . '/Cache',
];
```

### 4. 初始化框架

在入口文件 `index.php` 中初始化框架：

```php
<?php
require('vendor/autoload.php');

// 加载应用程序配置
FLEA::loadAppInf('config.php');

// 运行 MVC 应用
FLEA::runMVC();
```

## PSR-4 命名空间迁移

### 主要变更

框架已从旧的类命名约定（如 `FLEA_Db_TableDataGateway`）迁移到 PSR-4 命名空间（如 `\FLEA\Db\TableDataGateway`）。

### 类名对照表

| 旧类名 | 新命名空间类名 |
|--------|----------------|
| `FLEA_Db_TableDataGateway` | `\FLEA\Db\TableDataGateway` |
| `FLEA_Controller_Action` | `\FLEA\Controller\Action` |
| `FLEA_Rbac` | `\FLEA\Rbac\Rbac` |
| `FLEA_Config` | `\FLEA\Config` |
| `FLEA_Helper_Array` | `\FLEA\Helper\Array` |

### 使用示例

#### 创建控制器

```php
class Controller_Index extends \FLEA\Controller\Action
{
    public function actionIndex()
    {
        echo 'Hello, World!';
    }
}
```

#### 创建数据模型

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';
}
```

#### 使用 RBAC

```php
$rbac = new \FLEA\Rbac\Rbac();
$userData = ['user_id' => 1, 'username' => 'john'];
$rolesData = ['ADMIN', 'EDITOR'];
$rbac->setUser($userData, $rolesData);
```

## 目录结构

```
fleaphp-ex/
├── FLEA/                    # 框架核心文件
│   ├── FLEA.php             # 框架入口文件
│   ├── FLEA/                # 框架类文件（PSR-4 命名空间）
│   │   ├── Config.php       # 配置管理
│   │   ├── Controller/      # 控制器类
│   │   ├── Db/             # 数据库类
│   │   ├── Rbac/           # RBAC 权限控制
│   │   ├── Helper/         # 助手类
│   │   └── ...
│   └── Functions.php        # 全局辅助函数
├── vendor/                  # Composer 依赖
├── composer.json            # Composer 配置
├── config.php              # 应用配置
├── index.php               # 入口文件
└── USER_GUIDE.md           # 详细使用手册
```

## 快速开始

### 1. 创建控制器

```php
<?php
class Controller_Hello extends \FLEA\Controller\Action
{
    public function actionWorld()
    {
        echo 'Hello, FleaPHP!';
    }
}
```

### 2. 访问应用

根据你的 URL 模式配置，访问：
- 标准模式：`http://localhost/index.php?controller=Hello&action=world`
- PATHINFO 模式：`http://localhost/index.php/Hello/world`
- URL 重写模式：`http://localhost/Hello/world`

### 3. 数据库操作

```php
// 获取用户表实例
$userTable = FLEA::getSingleton('Table_Users');

// 查询用户
$user = $userTable->find(1);

// 创建用户
$newUserId = $userTable->create([
    'username' => 'john',
    'email' => 'john@example.com',
]);

// 更新用户
$userTable->update([
    'user_id' => $newUserId,
    'email' => 'newemail@example.com',
]);

// 删除用户
$userTable->removeByPkv($newUserId);
```

## 文档

详细的开发文档请查看 [USER_GUIDE.md](USER_GUIDE.md)，包含以下内容：

- 核心概念
- 配置管理
- 类加载与自动加载
- 对象注册与单例模式
- 数据库操作
- TableDataGateway - 表数据入口
- MVC 模式
- 缓存管理
- RBAC 权限控制
- 异常处理
- 助手函数
- URL 生成
- 最佳实践

## 迁移指南

如果您正在从旧版本的 FleaPHP 迁移，请注意以下变更：

### 1. 类加载方式

**旧方式：**
```php
require('FLEA/FLEA.php');
$userTable = new FLEA_Db_TableDataGateway();
```

**新方式：**
```php
require('vendor/autoload.php');
$userTable = new \FLEA\Db\TableDataGateway();
```

### 2. 控制器继承

**旧方式：**
```php
class Controller_Index extends FLEA_Controller_Action
{
    // ...
}
```

**新方式：**
```php
class Controller_Index extends \FLEA\Controller\Action
{
    // ...
}
```

### 3. 数据模型继承

**旧方式：**
```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    // ...
}
```

**新方式：**
```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    // ...
}
```

## 配置 Composer

确保您的 `composer.json` 包含以下配置：

```json
{
    "name": "fleaphp/ex",
    "description": "FleaPHP Framework",
    "type": "project",
    "require": {
        "php": ">=7.0"
    },
    "autoload": {
        "psr-4": {
            "FLEA\\": "FLEA/FLEA/"
        },
        "files": [
            "FLEA/FLEA.php",
            "FLEA/Functions.php"
        ]
    }
}
```

修改后运行：

```bash
composer dump-autoload
```

## 最佳实践

1. **使用命名空间**：始终使用 PSR-4 命名空间引用类
2. **Composer 管理**：使用 Composer 管理所有依赖和自动加载
3. **单例模式**：对于需要多次使用的对象，使用 `FLEA::getSingleton()` 获取单例
4. **异常处理**：使用框架提供的异常类进行错误处理
5. **缓存使用**：合理使用缓存提高性能

## 贡献

欢迎提交 Issue 和 Pull Request！

## 许可证

请查看项目根目录下的 LICENSE 文件。

## 更新日志

查看 [CHANGES.md](CHANGES.md) 了解详细的更新记录。

## 支持

如有问题或建议，请：
- 提交 Issue
- 查阅 [USER_GUIDE.md](USER_GUIDE.md)
- 查看示例代码

---

**FleaPHP** - 轻量级 PHP 框架，让开发更简单！
