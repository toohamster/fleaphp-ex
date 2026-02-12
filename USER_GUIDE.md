# FleaPHP 开发者使用手册

## 目录

1. [简介](#简介)
2. [快速开始](#快速开始)
3. [核心概念](#核心概念)
4. [配置管理](#配置管理)
5. [类加载与自动加载](#类加载与自动加载)
6. [对象注册与单例模式](#对象注册与单例模式)
7. [数据库操作](#数据库操作)
8. [MVC 模式](#mvc-模式)
9. [缓存管理](#缓存管理)
10. [异常处理](#异常处理)
11. [助手函数](#助手函数)
12. [URL 生成](#url-生成)
13. [最佳实践](#最佳实践)

---

## 简介

FleaPHP 是一个轻量级的 PHP 框架，提供了完整的 MVC 开发支持、数据库抽象层、缓存管理等功能。本手册将帮助开发者快速上手并充分利用 FleaPHP 的功能。

### 特性

- **轻量级**：核心代码精简，性能高效
- **MVC 架构**：支持模型-视图-控制器模式
- **数据库抽象层**：支持多种数据库，统一的操作接口
- **自动加载**：基于命名约定的类文件自动加载
- **对象容器**：单例模式管理对象实例
- **缓存系统**：内置文件缓存支持
- **灵活配置**：支持调试和生产两种模式

### 系统要求

- PHP 7.0 或更高版本
- 支持的数据库：MySQL, PostgreSQL, SQLite 等

---

## 快速开始

### 安装

将 FleaPHP 框架文件复制到你的项目目录中：

```
your-project/
├── FLEA/
│   ├── FLEA.php
│   └── FLEA/
│       ├── Config.php
│       └── ...
├── index.php
└── ...
```

### 基础配置

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

### 初始化框架

在入口文件 `index.php` 中初始化框架：

```php
<?php
require('FLEA/FLEA.php');

// 加载应用程序配置
FLEA::loadAppInf('config.php');

// 运行 MVC 应用
FLEA::runMVC();
```

---

## 核心概念

### 配置管理

FleaPHP 使用 `FLEA_Config` 单例类管理所有配置。框架在加载时会自动初始化配置管理器。

### 对象容器

框架维护一个对象容器，用于存储和管理单例对象实例。通过 `FLEA::register()` 和 `FLEA::registry()` 方法可以注册和获取对象。

### 类文件搜索路径

框架维护一组目录作为类文件的搜索路径，自动加载器会按照这些路径查找类文件。

### 数据库连接池

框架维护一个数据库连接池，相同的 DSN 会返回同一个数据库连接对象。

---

## 配置管理

### 获取配置项

使用 `FLEA::getAppInf()` 获取配置项：

```php
$charset = FLEA::getAppInf('responseCharset'); // 获取响应字符集
$controller = FLEA::getAppInf('defaultController'); // 获取默认控制器
```

可以指定默认值，当配置项不存在时返回该默认值：

```php
$timeout = FLEA::getAppInf('requestTimeout', 30);
```

### 设置配置项

使用 `FLEA::setAppInf()` 设置配置项：

```php
FLEA::setAppInf('siteTitle', '我的网站');

// 批量设置
FLEA::setAppInf([
    'siteTitle' => '我的网站',
    'siteUrl' => 'https://example.com',
]);
```

### 加载配置文件

使用 `FLEA::loadAppInf()` 加载配置文件：

```php
FLEA::loadAppInf('./config/database.php');
```

配置文件应该返回一个数组：

```php
<?php
return [
    'dbDSN' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        // ...
    ],
];
```

### 数组配置项操作

获取数组配置项中的特定键值：

```php
// 获取数组配置项的值
$maxSize = FLEA::getAppInfValue('upload', 'maxSize', 1048576);

// 设置数组配置项的值
FLEA::setAppInfValue('upload', 'allowedTypes', ['jpg', 'png', 'gif']);
```

### 配置常量

框架预定义了一些常量：

| 常量 | 说明 |
|------|------|
| `FLEA_VERSION` | FleaPHP 版本号 |
| `PHP5` | PHP 版本标识（true） |
| `PHP4` | PHP 版本标识（false） |
| `DS` | 目录分隔符简写 |
| `FLEA_DIR` | FLEA 框架目录 |
| `DEBUG_MODE` | 调试模式标识 |

URL 模式常量：

| 常量 | 值 | 说明 |
|------|-----|------|
| `URL_STANDARD` | URL_STANDARD | 标准 URL 模式 (?controller=...) |
| `URL_PATHINFO` | URL_PATHINFO | PATHINFO 模式 (/controller/action/) |
| `URL_REWRITE` | URL_REWRITE | URL 重写模式 (/controller/action/) |

---

## 类加载与自动加载

### 自动加载

框架使用 SPL 自动加载功能，自动根据类名加载对应的类文件。

类名中的下划线（`_`）会被转换为目录分隔符：

```php
// 自动加载 Table_Posts 类
// 查找文件：Table/Posts.php
$obj = new Table_Posts();
```

### 手动加载类

使用 `FLEA::loadClass()` 手动加载类：

```php
// 加载类文件
FLEA::loadClass('Helper_String');

// 第二个参数为 true 时，类文件不存在不抛出异常
FLEA::loadClass('Helper_String', true);
```

### 加载文件

使用 `FLEA::loadFile()` 加载任意文件：

```php
// 加载文件
FLEA::loadFile('config/routes.php');

// 第二个参数为 true 时，使用 require_once
FLEA::loadFile('lib/functions.php', true);
```

文件名中的下划线（`_`）也会被转换为目录分隔符：

```php
FLEA::loadFile('Helper_Array_Utils');
// 加载 Helper/Array/Utils.php
```

### 添加类搜索路径

使用 `FLEA::import()` 添加类文件搜索路径：

```php
// 添加搜索路径
FLEA::import(dirname(__FILE__) . '/APP');

// 现在可以在 APP 目录下查找类文件
FLEA::loadClass('Model_User'); // 查找 APP/Model/User.php
```

注意：应该添加类文件所在目录的父目录，而不是类文件所在目录本身。

例如，如果类文件位于 `./APP/Model/User.php`，则应该添加 `./APP` 目录：

```php
FLEA::import('./APP');
FLEA::loadClass('Model_User'); // 正确
```

### 搜索文件

使用 `FLEA::getFilePath()` 搜索文件：

```php
// 搜索文件，返回完整路径
$path = FLEA::getFilePath('Helper_String');

// 如果文件不存在，返回 false
if ($path) {
    require($path);
}
```

---

## 对象注册与单例模式

### 注册对象

使用 `FLEA::register()` 注册对象到对象容器：

```php
$cache = new Cache();
FLEA::register($cache, 'Cache');

// 不指定名称时，使用类名
$cache = new Cache();
FLEA::register($cache);
// 等同于：FLEA::register($cache, 'Cache');
```

### 获取对象

使用 `FLEA::registry()` 获取已注册的对象：

```php
// 根据名称获取对象
$cache = FLEA::registry('Cache');

// 不指定名称时，返回所有对象
$objects = FLEA::registry();
```

### 检查对象是否注册

使用 `FLEA::isRegistered()` 检查对象是否已注册：

```php
if (FLEA::isRegistered('Cache')) {
    $cache = FLEA::registry('Cache');
}
```

### 获取单例对象

使用 `FLEA::getSingleton()` 获取类的单例实例：

```php
// 第一次调用会创建并注册实例
$userModel = FLEA::getSingleton('Table_Users');

// 后续调用返回同一个实例
$userModel2 = FLEA::getSingleton('Table_Users');

// $userModel 和 $userModel2 是同一个对象
var_dump($userModel === $userModel2); // bool(true)
```

---

## 数据库操作

### 获取数据库连接

使用 `FLEA::getDBO()` 获取数据库连接对象：

```php
// 使用配置中的默认 DSN
$dbo = FLEA::getDBO();

// 使用指定的 DSN
$dsn = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'login' => 'username',
    'password' => 'password',
    'database' => 'test',
];
$dbo = FLEA::getDBO($dsn);

// 使用 DSN 字符串
$dbo = FLEA::getDBO('mysql://username:password@localhost/database');
```

### DSN 格式

DSN（Data Source Name）用于描述数据库连接信息。

**数组格式：**

```php
$dsn = [
    'driver'   => 'mysql',        // 数据库驱动
    'host'     => 'localhost',    // 主机地址
    'port'     => 3306,          // 端口号
    'login'    => 'username',    // 用户名
    'password' => 'password',     // 密码
    'database' => 'test_db',      // 数据库名
    'charset'  => 'utf8',         // 字符集
    'prefix'   => 'tbl_',        // 表前缀
    'schema'   => '',            // 模式（PostgreSQL）
    'options'  => '',            // 额外选项
];
```

**字符串格式：**

```
mysql://username:password@host:port/database?options
```

示例：

```php
$dsn = 'mysql://root:123456@localhost:3306/mydb';
$dsn = 'mysql://root:123456@localhost/mydb?charset=utf8';
```

### 连接池

相同的 DSN 会返回同一个数据库连接对象：

```php
// 第一次调用创建连接
$dbo1 = FLEA::getDBO();

// 第二次调用返回相同的连接
$dbo2 = FLEA::getDBO();

var_dump($dbo1 === $dbo2); // bool(true)
```

---

## MVC 模式

### 运行 MVC 应用

使用 `FLEA::runMVC()` 启动 MVC 应用：

```php
require('FLEA/FLEA.php');
FLEA::loadAppInf('config.php');

// 运行 MVC 应用
FLEA::runMVC();
```

### 控制器

控制器类应该继承自 `FLEA_Controller_Action`：

```php
class Controller_Index extends FLEA_Controller_Action
{
    public function actionIndex()
    {
        echo 'Hello, World!';
    }

    public function actionLogin()
    {
        // 处理登录逻辑
    }
}
```

### URL 路由

框架支持三种 URL 模式：

#### 1. 标准 URL 模式 (URL_STANDARD)

```
http://example.com/index.php?controller=Index&action=login
```

#### 2. PATHINFO 模式 (URL_PATHINFO)

```
http://example.com/index.php/Index/login
```

#### 3. URL 重写模式 (URL_REWRITE)

需要配置 Web 服务器的 URL 重写规则：

```
http://example.com/Index/login
```

### 初始化环境

使用 `FLEA::init()` 初始化运行环境：

```php
FLEA::init();

// 或者
FLEA::init(true); // 同时加载 MVC 相关文件
```

初始化过程包括：
- 设置时区
- 安装异常处理例程
- 载入日志服务
- 设置缓存目录
- 载入 URL 分析过滤器
- 载入 requestFilters
- 载入 autoLoad 文件
- 载入 session 服务提供程序
- 启动 session
- 设置响应字符集
- 载入多语言支持

---

## 缓存管理

### 写入缓存

使用 `FLEA::writeCache()` 写入缓存：

```php
$data = ['name' => 'John', 'age' => 30];
$cacheId = 'user_info_' . $userId;

FLEA::writeCache($cacheId, $data);
```

### 读取缓存

使用 `FLEA::getCache()` 读取缓存：

```php
$cacheId = 'user_info_' . $userId;

// 默认缓存时间 900 秒（15 分钟）
$data = FLEA::getCache($cacheId);

if ($data === false) {
    // 缓存不存在或已过期
    $data = fetchDataFromDatabase();
    FLEA::writeCache($cacheId, $data);
}
```

指定缓存时间：

```php
// 缓存时间 3600 秒（1 小时）
$data = FLEA::getCache($cacheId, 3600);

// 缓存不过期
$data = FLEA::getCache($cacheId, -1);
```

### 删除缓存

使用 `FLEA::purgeCache()` 删除缓存：

```php
$cacheId = 'user_info_' . $userId;
FLEA::purgeCache($cacheId);
```

### 缓存配置

在配置文件中设置缓存目录：

```php
return [
    'internalCacheDir' => dirname(__FILE__) . '/Cache',
];
```

如果未设置缓存目录，缓存功能将不可用。

---

## 异常处理

### 框架异常

FleaPHP 提供了多个异常类，都继承自 `FLEA_Exception`：

- `FLEA_Exception_ExpectedFile` - 文件不存在
- `FLEA_Exception_ExpectedClass` - 类不存在
- `FLEA_Exception_TypeMismatch` - 类型不匹配
- `FLEA_Exception_ExistsKeyName` - 对象名称已存在
- `FLEA_Exception_NotExistsKeyName` - 对象名称不存在
- `FLEA_Exception_CacheDisabled` - 缓存功能未启用
- `FLEA_Db_Exception_InvalidDSN` - 无效的 DSN

### 设置异常处理器

使用 `__SET_EXCEPTION_HANDLER()` 设置异常处理器：

```php
// 保存当前的异常处理器
$prevHandler = __SET_EXCEPTION_HANDLER('myExceptionHandler');

function myExceptionHandler($ex)
{
    // 自定义异常处理逻辑
    echo '发生异常: ' . $ex->getMessage();
}
```

### 异常捕获点

使用 `__TRY()`, `__CATCH()`, `__CANCEL_TRY()` 实现异常捕获：

```php
// 设置异常捕获点
__TRY();

try {
    // 可能抛出异常的代码
    FLEA::loadClass('NonExistentClass');
} catch (Exception $e) {
    // 将异常压入堆栈
    throw $e;
}

// 检查是否有异常
$ex = __CATCH();
if (__IS_EXCEPTION($ex)) {
    echo '捕获到异常: ' . $ex->getMessage();
}

// 或取消异常捕获点
__CANCEL_TRY();
```

### 检查异常类型

使用 `__IS_EXCEPTION()` 检查异常类型：

```php
if (__IS_EXCEPTION($ex, 'FLEA_Exception_ExpectedFile')) {
    echo '文件不存在异常';
}
```

---

## 助手函数

### 加载助手

使用 `FLEA::loadHelper()` 加载助手：

```php
// 加载助手
FLEA::loadHelper('array');
FLEA::loadHelper('image');

// 使用助手
$arrayHelper = new FLEA_Helper_Array();
```

助手配置在应用程序配置中，以 `helper.` 开头：

```php
return [
    'helper.array' => 'FLEA_Helper_Array',
    'helper.image' => 'FLEA_Helper_Image',
    // ...
];
```

### 初始化 WebControls

使用 `FLEA::initWebControls()` 初始化 WebControls：

```php
$webControls = FLEA::initWebControls();
```

可以自定义 WebControls 类：

```php
return [
    'webControlsClassName' => 'MyApp_WebControls',
];
```

### 初始化 Ajax

使用 `FLEA::initAjax()` 初始化 Ajax：

```php
$ajax = FLEA::initAjax();
```

可以自定义 Ajax 类：

```php
return [
    'ajaxClassName' => 'MyApp_Ajax',
];
```

---

## URL 生成

### 生成 URL

使用 `url()` 函数生成 URL：

```php
// 生成标准 URL
$url = url('Index', 'login');
// 输出: ?controller=Index&action=login

// 带参数
$url = url('Article', 'view', ['id' => 1]);
// 输出: ?controller=Article&action=view&id=1

// 带 anchor
$url = url('Article', 'view', ['id' => 1], '#comments');
// 输出: ?controller=Article&action=view&id=1#comments

// 使用默认控制器和动作
$url = url();
// 输出: ?controller=Index&action=index (使用配置中的默认值)
```

### URL 模式

根据配置中的 `urlMode` 生成不同格式的 URL：

**标准模式：**

```php
$url = url('User', 'profile', ['id' => 1]);
// 输出: /index.php?controller=User&action=profile&id=1
```

**PATHINFO 模式：**

```php
$url = url('User', 'profile', ['id' => 1]);
// 输出: /index.php/User/profile/id/1
```

**URL 重写模式：**

```php
$url = url('User', 'profile', ['id' => 1]);
// 输出: /User/profile/id/1
```

### URL 选项

```php
$url = url('User', 'profile', ['id' => 1], null, [
    'mode' => URL_REWRITE,        // 指定 URL 模式
    'lowerChar' => true,          // 转换为小写
    'bootstrap' => 'admin.php',   // 指定入口文件
    'parameterPairStyle' => '-',  // 参数分隔符
]);
```

### URL 回调

可以在配置中设置 URL 生成回调函数：

```php
return [
    'urlCallback' => function(&$controller, &$action, &$params, &$anchor, &$options) {
        // 修改 URL 生成参数
        $controller = strtolower($controller);
        $action = strtolower($action);
    },
];
```

---

## 最佳实践

### 1. 配置管理

- 将敏感信息（如数据库密码）存储在单独的配置文件中
- 使用环境变量覆盖配置项，便于不同环境的部署
- 在开发环境启用调试模式，在生产环境禁用

### 2. 类文件组织

- 遵循命名约定：类名中的下划线对应目录层级
- 将类文件放在合理的目录结构中
- 使用 `FLEA::import()` 添加搜索路径时，添加目录的父目录

### 3. 对象管理

- 对于需要多次使用的对象，使用 `FLEA::getSingleton()` 获取单例
- 对于服务类，在应用启动时注册到对象容器
- 避免在循环中创建不必要的对象

### 4. 数据库操作

- 合理使用数据库连接池，避免重复创建连接
- 使用表前缀避免表名冲突
- 使用 DSN 字符串或数组格式指定数据库连接信息

### 5. 缓存使用

- 对频繁访问但不常变化的数据使用缓存
- 为缓存设置合理的过期时间
- 及时清理不再使用的缓存

### 6. 异常处理

- 使用框架提供的异常类
- 设置自定义异常处理器
- 使用异常捕获点处理需要特殊处理的异常

### 7. URL 生成

- 始终使用 `url()` 函数生成 URL，而不是硬编码
- 合理配置 URL 模式，选择最适合项目的模式
- 使用 URL 选项自定义 URL 生成行为

### 8. 性能优化

- 合理配置类搜索路径，避免不必要的文件查找
- 使用缓存减少数据库查询
- 在生产环境禁用调试模式以提高性能

---

## 常见问题

### Q: 如何切换调试模式和生产模式？

A: 定义 `DEPLOY_MODE` 常量为 true 即可启用生产模式：

```php
define('DEPLOY_MODE', true);
require('FLEA/FLEA.php');
```

### Q: 如何自定义类文件搜索路径？

A: 使用 `FLEA::import()` 添加搜索路径：

```php
FLEA::import(dirname(__FILE__) . '/APP');
FLEA::import(dirname(__FILE__) . '/LIB');
```

### Q: 如何处理数据库连接失败？

A: 使用 try-catch 捕获异常：

```php
try {
    $dbo = FLEA::getDBO();
} catch (FLEA_Db_Exception_InvalidDSN $e) {
    echo '数据库连接失败: ' . $e->getMessage();
}
```

### Q: 如何清除所有缓存？

A: 遍历缓存目录删除所有文件：

```php
$cacheDir = FLEA::getAppInf('internalCacheDir');
$files = glob($cacheDir . '/*.php');
foreach ($files as $file) {
    unlink($file);
}
```

### Q: 如何重置对象容器？

A: 无法直接重置，需要重新加载框架。

---

## 附录

### 配置项参考

| 配置项 | 说明 | 默认值 |
|--------|------|--------|
| `dbDSN` | 数据库连接信息 | null |
| `dbTablePrefix` | 数据库表前缀 | '' |
| `urlMode` | URL 模式 | URL_STANDARD |
| `urlLowerChar` | URL 是否转换为小写 | false |
| `urlBootstrap` | 默认入口文件 | index.php |
| `urlAlwaysUseAccessor` | URL 始终使用参数名 | false |
| `urlParameterPairStyle` | URL 参数分隔符 | = |
| `controllerAccessor` | 控制器参数名 | controller |
| `actionAccessor` | 动作参数名 | action |
| `defaultController` | 默认控制器 | Default |
| `defaultAction` | 默认动作 | index |
| `defaultLanguage` | 默认语言 | chinese-utf8 |
| `responseCharset` | 响应字符集 | UTF-8 |
| `databaseCharset` | 数据库字符集 | UTF-8 |
| `internalCacheDir` | 缓存目录 | null |
| `logEnabled` | 是否启用日志 | false |
| `logProvider` | 日志服务提供程序 | null |
| `exceptionHandler` | 异常处理器 | __FLEA_EXCEPTION_HANDLER |
| `webControlsClassName` | WebControls 类名 | FLEA_WebControls |
| `ajaxClassName` | Ajax 类名 | FLEA_Ajax |
| `sessionProvider` | Session 服务提供程序 | null |
| `autoSessionStart` | 是否自动启动 session | false |
| `multiLanguageSupport` | 是否启用多语言支持 | false |
| `languageSupportProvider` | 多语言支持提供程序 | null |
| `languageFilesDir` | 语言文件目录 | null |
| `displayErrors` | 是否显示错误 | true |
| `friendlyErrorsMessage` | 是否显示友好错误信息 | false |
| `autoResponseHeader` | 是否自动输出响应头 | true |
| `autoLoad` | 自动加载的文件数组 | [] |
| `requestFilters` | 请求过滤器数组 | [] |
| `MVCPackageFilename` | MVC 包文件名 | '' |
| `defaultTimezone` | 默认时区 | Asia/Shanghai |
| `dispatcher` | 调度器类名 | FLEA_Dispatcher_Auth |
| `urlCallback` | URL 生成回调函数 | null |

### 内置助手

| 助手名称 | 类名 |
|----------|------|
| array | FLEA_Helper_Array |
| pager | FLEA_Helper_Pager |
| image | FLEA_Helper_Image |
| uploader | FLEA_Helper_Uploader |

### 相关资源

- FleaPHP 官方文档: [链接]
- 示例代码: [链接]
- 社区论坛: [链接]

---

*本文档最后更新于 2026-02-12*
