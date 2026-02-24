# FleaPHP 开发者使用手册

## 目录

1. [简介](#简介)
2. [快速开始](#快速开始)
3. [核心概念](#核心概念)
4. [配置管理](#配置管理)
5. [类加载与自动加载](#类加载与自动加载)
6. [对象注册与单例模式](#对象注册与单例模式)
7. [数据库操作](#数据库操作)
8. [TableDataGateway - 表数据入口](#tabledatagateway---表数据入口)
9. [MVC 模式](#mvc-模式)
10. [缓存管理](#缓存管理)
11. [RBAC 权限控制](#rbac-权限控制)
12. [异常处理](#异常处理)
13. [助手函数](#助手函数)
14. [URL 生成](#url-生成)
15. [最佳实践](#最佳实践)

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

## TableDataGateway - 表数据入口

FleaPHP 提供了 `FLEA_Db_TableDataGateway` 类（表数据入口），用于封装数据表的 CRUD（创建、读取、更新、删除）操作。开发者应该从该类派生自己的数据访问类。

### 定义数据表入口类

创建数据表入口类，继承自 `FLEA_Db_TableDataGateway`：

```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    /**
     * 数据表名（不包含前缀）
     */
    public $tableName = 'users';

    /**
     * 主键字段名
     */
    public $primaryKey = 'user_id';
}
```

使用该类：

```php
$userTable = FLEA::getSingleton('Table_Users');
```

### 表关系定义

FleaPHP 支持四种表关系类型：

| 关系类型 | 常量 | 说明 |
|---------|------|------|
| 一对一 | `HAS_ONE` | 一个记录拥有另一个关联的记录 |
| 一对多 | `HAS_MANY` | 一个记录拥有多个关联的记录 |
| 从属 | `BELONGS_TO` | 一个记录属于另一个记录 |
| 多对多 | `MANY_TO_MANY` | 两个数据表的数据互相引用 |

#### 一对一关系（HAS_ONE）

一个用户对应一个详细资料：

```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * 定义一对一关系
     */
    public $hasOne = [
        'Profile' => [
            'tableClass' => 'Table_UserProfiles',
            'foreignKey' => 'user_id',
            'mappingName' => 'profile',
        ],
    ];
}
```

使用示例：

```php
$userTable = FLEA::getSingleton('Table_Users');
$user = $userTable->find(1);

// 访问关联的数据
$profile = $user['profile'];
```

#### 一对多关系（HAS_MANY）

一个部门拥有多个员工：

```php
class Table_Departments extends FLEA_Db_TableDataGateway
{
    public $tableName = 'departments';
    public $primaryKey = 'dept_id';

    /**
     * 定义一对多关系
     */
    public $hasMany = [
        'Employees' => [
            'tableClass' => 'Table_Employees',
            'foreignKey' => 'dept_id',
            'mappingName' => 'employees',
            'sort' => 'employee_id DESC',
        ],
    ];
}
```

使用示例：

```php
$deptTable = FLEA::getSingleton('Table_Departments');
$dept = $deptTable->find(1);

// 访问关联的员工列表
$employees = $dept['employees'];
```

#### 从属关系（BELONGS_TO）

一个用户属于一个角色：

```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * 定义从属关系
     */
    public $belongsTo = [
        'Role' => [
            'tableClass' => 'Table_Roles',
            'foreignKey' => 'role_id',
            'mappingName' => 'role',
        ],
    ];
}
```

使用示例：

```php
$userTable = FLEA::getSingleton('Table_Users');
$user = $userTable->find(1);

// 访问所属的角色
$role = $user['role'];
```

#### 多对多关系（MANY_TO_MANY）

学生与课程是多对多关系，通过中间表关联：

```php
class Table_Students extends FLEA_Db_TableDataGateway
{
    public $tableName = 'students';
    public $primaryKey = 'student_id';

    /**
     * 定义多对多关系
     */
    public $manyToMany = [
        'Courses' => [
            'tableClass' => 'Table_Courses',
            'joinTable' => 'student_courses', // 中间表
            'foreignKey' => 'student_id',    // 中间表中指向本表的字段
            'assocForeignKey' => 'course_id', // 中间表中指向关联表的字段
            'mappingName' => 'courses',
        ],
    ];
}
```

使用示例：

```php
$studentTable = FLEA::getSingleton('Table_Students');
$student = $studentTable->find(1);

// 访问选修的课程列表
$courses = $student['courses'];
```

### 查询数据

#### 查找单条记录（find）

```php
// 根据主键查找
$user = $userTable->find(1);

// 根据条件查找
$user = $userTable->find(['username' => 'john']);

// 指定排序
$user = $userTable->find(['status' => 'active'], 'user_id DESC');

// 指定查询字段
$user = $userTable->find(1, null, 'user_id, username, email');

// 不查询关联数据
$user = $userTable->find(1, null, '*', false);
```

#### 查找多条记录（findAll）

```php
// 查询所有记录
$users = $userTable->findAll();

// 根据条件查询
$users = $userTable->findAll(['status' => 'active']);

// 指定排序和分页
$users = $userTable->findAll(
    ['status' => 'active'],
    'user_id DESC',
    10,    // 限制 10 条
    0       // 从第 0 条开始
);

// 使用数组形式指定分页
$users = $userTable->findAll(
    null,
    null,
    [10, 0], // array(length, offset)
);

// 指定查询字段
$users = $userTable->findAll(null, null, null, 'user_id, username');
```

#### 根据字段查找（findByField / findAllByField）

```php
// 查找单条记录
$user = $userTable->findByField('username', 'john');

// 查找多条记录
$users = $userTable->findAllByField('status', 'active', 'user_id DESC');

// 带分页
$users = $userTable->findAllByField('status', 'active', null, [10, 0]);
```

#### 根据多个主键查找（findAllByPkvs）

```php
// 根据多个主键值查找
$users = $userTable->findAllByPkvs([1, 2, 3, 4]);

// 带条件查询
$users = $userTable->findAllByPkvs([1, 2, 3], ['status' => 'active']);
```

#### 使用 SQL 查询（findBySql）

```php
// 使用自定义 SQL 查询
$sql = "SELECT * FROM users WHERE status = 'active'";
$users = $userTable->findBySql($sql);

// 带分页
$users = $userTable->findBySql($sql, 10); // 前 10 条
$users = $userTable->findBySql($sql, [10, 0]); // 第 0-10 条
```

### 条件表达式

#### 简单条件

```php
// 字段 = 值
$users = $userTable->findAll(['username' => 'john']);

// 多个条件（AND 关系）
$users = $userTable->findAll([
    'status' => 'active',
    'age' => 25,
]);
```

#### OR 条件

```php
$users = $userTable->findAll([
    'or',
    'status' => 'active',
    'status' => 'pending',
]);
```

#### IN 条件

```php
$users = $userTable->findAll([
    'user_id' => ['in()' => [1, 2, 3, 4]],
]);

// 等价于 SQL: WHERE user_id IN (1, 2, 3, 4)
```

#### LIKE 条件

```php
$users = $userTable->findAll([
    'username' => ['like' => 'john%'],
]);

// 等价于 SQL: WHERE username LIKE 'john%'
```

#### 比较条件

```php
$users = $userTable->findAll([
    'age' => ['>' => 18],
    'created_at' => ['<=' => '2024-01-01'],
]);

// 等价于 SQL: WHERE age > 18 AND created_at <= '2024-01-01'
```

#### 复杂条件

```php
$users = $userTable->findAll([
    'or',
    [
        'and',
        'status' => 'active',
        'age' => ['>' => 18],
    ],
    [
        'and',
        'status' => 'vip',
        'age' => ['>' => 25],
    ],
]);

// 等价于 SQL: WHERE (status = 'active' AND age > 18) OR (status = 'vip' AND age > 25)
```

### 创建记录（create）

```php
// 创建单条记录
$row = [
    'username' => 'john',
    'email' => 'john@example.com',
    'status' => 'active',
];

$newUserId = $userTable->create($row);

// $newUserId 包含新插入记录的主键值
echo "新用户 ID: " . $newUserId;

// 创建时自动填充时间字段
// 如果数据表有 CREATED, CREATED_ON, CREATED_AT 字段
// 会自动填充当前时间
```

#### 创建多条记录（createRowset）

```php
$rows = [
    [
        'username' => 'user1',
        'email' => 'user1@example.com',
    ],
    [
        'username' => 'user2',
        'email' => 'user2@example.com',
    ],
];

$userTable->createRowset($rows);
```

#### 不处理关联创建

```php
// 创建记录时处理关联数据
$userTable->create($row, true);  // 处理关联（默认）

// 不处理关联
$userTable->create($row, false);
```

### 更新记录（update）

```php
// 根据主键更新
$row = [
    'user_id' => 1,
    'email' => 'newemail@example.com',
    'status' => 'active',
];

$userTable->update($row);
```

#### 根据条件更新（updateByConditions）

```php
$conditions = ['status' => 'pending'];
$row = ['status' => 'active'];

$userTable->updateByConditions($conditions, $row);

// 等价于 SQL: UPDATE users SET status = 'active' WHERE status = 'pending'
```

#### 更新单个字段（updateField）

```php
$conditions = ['user_id' => 1];
$userTable->updateField($conditions, 'email', 'newemail@example.com');

// 等价于 SQL: UPDATE users SET email = 'newemail@example.com' WHERE user_id = 1
```

#### 更新多条记录（updateRowset）

```php
$rows = [
    ['user_id' => 1, 'status' => 'active'],
    ['user_id' => 2, 'status' => 'active'],
];

$userTable->updateRowset($rows);
```

### 删除记录（remove）

```php
// 根据主键删除
$row = $userTable->find(1);
$userTable->remove($row);

// 或者直接根据主键值删除
$userTable->removeByPkv(1);
```

#### 根据条件删除（removeByConditions）

```php
$conditions = ['status' => 'deleted'];
$userTable->removeByConditions($conditions);

// 等价于 SQL: DELETE FROM users WHERE status = 'deleted'
```

#### 根据多个主键删除（removeByPkvs）

```php
$userTable->removeByPkvs([1, 2, 3, 4]);

// 等价于 SQL: DELETE FROM users WHERE user_id IN (1, 2, 3, 4)
```

#### 删除所有记录（removeAll / removeAllWithLinks）

```php
// 删除所有记录（不处理关联）
$userTable->removeAll();

// 删除所有记录（处理关联）
$userTable->removeAllWithLinks();
```

#### 删除时处理关联

```php
// 删除记录时处理关联数据
$userTable->remove($row, true);  // 处理关联（默认）

// 不处理关联
$userTable->remove($row, false);
```

### 保存记录（save）

`save()` 方法自动判断是创建新记录还是更新现有记录：

```php
$row = [
    'username' => 'john',
    'email' => 'john@example.com',
];

// 第一次调用会创建记录
$userTable->save($row);
// $row 现在包含主键值
echo "用户 ID: " . $row['user_id'];

// 修改后再保存会更新记录
$row['email'] = 'newemail@example.com';
$userTable->save($row);
```

#### 保存多条记录（saveRowset）

```php
$rows = [
    [
        'username' => 'user1',
        'email' => 'user1@example.com',
    ],
    [
        'username' => 'user2',
        'email' => 'user2@example.com',
    ],
];

$userTable->saveRowset($rows);
```

### 关联操作

#### 启用/禁用关联

```php
// 禁用所有关联
$userTable->disableLinks();

// 启用所有关联
$userTable->enableLinks();

// 启用指定的关联
$userTable->enableLinks(['profile', 'role']);

// 禁用指定的关联
$userTable->disableLinks(['profile', 'role']);
```

#### 动态创建关联

```php
// 创建关联
$defines = [
    'tableClass' => 'Table_UserProfiles',
    'foreignKey' => 'user_id',
    'mappingName' => 'profile',
];

$userTable->createLink($defines, HAS_ONE);

// 删除关联
$userTable->removeLink('profile');
```

### 数据验证

#### 启用自动验证

```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * 启用自动验证
     */
    public $autoValidating = true;

    /**
     * 验证规则
     */
    public $validateRules = [
        'username' => [
            'required' => true,
            'minLength' => 3,
            'maxLength' => 20,
        ],
        'email' => [
            'required' => true,
            'email' => true,
        ],
    ];
}
```

#### 验证数据

```php
$row = [
    'username' => 'john',
    'email' => 'invalid-email',
];

$result = $userTable->create($row);

if (!$result) {
    // 获取验证错误
    $errors = $userTable->lastValidationResult;
    print_r($errors);
}
```

### 自动填充时间字段

如果数据表包含以下字段，会自动填充当前时间：

```php
class Table_Users extends FLEA_Db_TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * 创建记录时自动填充的字段
     */
    public $createdTimeFields = ['CREATED', 'CREATED_ON', 'CREATED_AT'];

    /**
     * 创建和更新记录时自动填充的字段
     */
    public $updatedTimeFields = ['UPDATED', 'UPDATED_ON', 'UPDATED_AT'];
}
```

使用示例：

```php
// 创建记录时，CREATED 字段会自动填充
$row = ['username' => 'john'];
$userTable->create($row);

// 更新记录时，UPDATED 字段会自动填充
$row['email'] = 'newemail@example.com';
$userTable->update($row);
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

## RBAC 权限控制

FleaPHP 提供了完整的 RBAC（基于角色的访问控制）支持，通过 `FLEA_Rbac` 类实现权限检查功能。

### RBAC 常量

框架预定义了几个 RBAC 相关的常量：

| 常量 | 说明 |
|------|------|
| `RBAC_EVERYONE` | 任何用户（不管该用户是否具有角色信息） |
| `RBAC_HAS_ROLE` | 具有任何角色的用户 |
| `RBAC_NO_ROLE` | 不具有任何角色的用户 |
| `RBAC_NULL` | 该设置没有值 |
| `ACTION_ALL` | 控制器中的所有动作 |

### 初始化 RBAC

创建 RBAC 实例：

```php
$rbac = new FLEA_Rbac();
```

或者在控制器中使用：

```php
$rbac = FLEA::getSingleton('FLEA_Rbac');
```

### 配置 RBAC

在配置文件中设置 RBAC 相关选项：

```php
return [
    // RBAC Session 键名
    'RBACSessionKey' => 'MY_APP_RBAC_USER',
];
```

### 用户管理

#### 设置用户信息

使用 `setUser()` 方法将用户信息保存到 session 中：

```php
$rbac = new FLEA_Rbac();

// 设置用户信息
$userData = [
    'user_id' => 1,
    'username' => 'john',
    'email' => 'john@example.com',
];

// 设置角色数据
$rolesData = ['ADMIN', 'EDITOR'];

$rbac->setUser($userData, $rolesData);
```

只设置用户信息，不设置角色：

```php
$rbac->setUser($userData);
```

#### 获取用户信息

使用 `getUser()` 方法获取 session 中的用户信息：

```php
$user = $rbac->getUser();

if ($user) {
    echo "当前用户: " . $user['username'];
}
```

#### 获取用户角色

使用 `getRoles()` 方法获取用户的角色：

```php
$roles = $rbac->getRoles();

// 返回可能是一个字符串（如 "ADMIN,EDITOR"）
// 或者是一个数组
```

使用 `getRolesArray()` 方法确保返回数组：

```php
$roles = $rbac->getRolesArray();

print_r($roles);
// 输出: Array ( [0] => ADMIN [1] => EDITOR )
```

#### 清除用户信息

使用 `clearUser()` 方法清除 session 中的用户信息（通常用于登出）：

```php
$rbac->clearUser();

// 用户已登出
```

### 权限检查

#### 访问控制表（ACT）

ACT（Access Control Table）是一个数组，包含以下键：

- `allow`: 允许访问的角色列表或特殊常量
- `deny`: 拒绝访问的角色列表或特殊常量

ACT 示例：

```php
// 允许所有用户访问
$ACT = [
    'allow' => RBAC_EVERYONE,
    'deny' => RBAC_NULL,
];

// 只允许管理员访问
$ACT = [
    'allow' => ['ADMIN'],
    'deny' => RBAC_NULL,
];

// 允许管理员和编辑器，但拒绝普通用户
$ACT = [
    'allow' => ['ADMIN', 'EDITOR'],
    'deny' => ['USER'],
];

// 要求用户必须有角色
$ACT = [
    'allow' => RBAC_HAS_ROLE,
    'deny' => RBAC_NULL,
];

// 要求用户不能有任何角色
$ACT = [
    'allow' => RBAC_NO_ROLE,
    'deny' => RBAC_NULL,
];
```

#### 检查权限

使用 `check()` 方法检查访问权限：

```php
$rbac = new FLEA_Rbac();

// 设置用户及其角色
$userData = ['user_id' => 1, 'username' => 'john'];
$rolesData = ['ADMIN', 'EDITOR'];
$rbac->setUser($userData, $rolesData);

// 定义访问控制表
$ACT = [
    'allow' => ['ADMIN', 'EDITOR'],
    'deny' => RBAC_NULL,
];

// 检查权限
$roles = $rbac->getRolesArray();
if ($rbac->check($roles, $ACT)) {
    echo "允许访问";
} else {
    echo "拒绝访问";
}
```

#### 准备 ACT

使用 `prepareACT()` 方法对原始 ACT 进行分析和整理：

```php
// 原始 ACT（可能包含字符串格式的角色列表）
$rawACT = [
    'allow' => 'ADMIN,EDITOR',
    'deny' => 'USER,BLOCKED',
];

// 准备 ACT
$ACT = $rbac->prepareACT($rawACT);

// 输出整理后的 ACT
print_r($ACT);
// 输出:
// Array (
//     [allow] => Array ( [0] => ADMIN [1] => EDITOR )
//     [deny] => Array ( [0] => USER [1] => BLOCKED )
// )
```

### 权限检查示例

#### 示例 1：简单角色检查

```php
$ACT = [
    'allow' => ['ADMIN'],
    'deny' => RBAC_NULL,
];

// 检查用户是否为管理员
if ($rbac->check($roles, $ACT)) {
    // 用户是管理员
    // 执行管理员操作
}
```

#### 示例 2：多角色支持

```php
$ACT = [
    'allow' => ['ADMIN', 'EDITOR', 'MODERATOR'],
    'deny' => RBAC_NULL,
];

// 用户具有 ADMIN, EDITOR, MODERATOR 其中任一角色即可访问
if ($rbac->check($roles, $ACT)) {
    // 用户有权限
}
```

#### 示例 3：拒绝特定角色

```php
$ACT = [
    'allow' => RBAC_EVERYONE,
    'deny' => ['BLOCKED', 'BANNED'],
];

// 所有用户都可以访问，除了被阻止或被封禁的用户
if ($rbac->check($roles, $ACT)) {
    // 用户未被阻止
}
```

#### 示例 4：必须具有角色

```php
$ACT = [
    'allow' => RBAC_HAS_ROLE,
    'deny' => RBAC_NULL,
];

// 用户必须至少有一个角色才能访问
if ($rbac->check($roles, $ACT)) {
    // 用户有角色
}
```

#### 示例 5：必须没有角色

```php
$ACT = [
    'allow' => RBAC_NO_ROLE,
    'deny' => RBAC_NULL,
];

// 用户必须没有任何角色才能访问
if ($rbac->check($roles, $ACT)) {
    // 用户没有角色
}
```

### 在控制器中使用 RBAC

#### 登录时设置用户和角色

```php
class Controller_Login extends FLEA_Controller_Action
{
    public function actionLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // 验证用户
        $userTable = FLEA::getSingleton('Table_Users');
        $user = $userTable->findByField('username', $username);

        if ($user && $user['password'] === md5($password)) {
            // 获取用户角色
            $roleTable = FLEA::getSingleton('Table_UserRoles');
            $roles = $roleTable->getUserRoles($user['user_id']);

            // 设置用户和角色
            $rbac = new FLEA_Rbac();
            $rbac->setUser($user, $roles);

            // 跳转到首页
            redirect(url('Index', 'index'));
        } else {
            echo "用户名或密码错误";
        }
    }

    public function actionLogout()
    {
        $rbac = new FLEA_Rbac();
        $rbac->clearUser();

        redirect(url('Login', 'index'));
    }
}
```

#### 在控制器中检查权限

```php
class Controller_Admin extends FLEA_Controller_Action
{
    public function actionIndex()
    {
        $rbac = new FLEA_Rbac();
        $roles = $rbac->getRolesArray();

        // 定义访问控制表
        $ACT = [
            'allow' => ['ADMIN'],
            'deny' => RBAC_NULL,
        ];

        // 检查权限
        if (!$rbac->check($roles, $ACT)) {
            echo "无权访问";
            return;
        }

        // 执行管理员操作
        echo "欢迎，管理员";
    }
}
```

#### 使用 RBAC 中间件

创建 RBAC 检查中间件：

```php
function checkPermission($requiredRoles)
{
    $rbac = new FLEA_Rbac();
    $roles = $rbac->getRolesArray();

    $ACT = [
        'allow' => $requiredRoles,
        'deny' => RBAC_NULL,
    ];

    if (!$rbac->check($roles, $ACT)) {
        js_alert('无权访问', '', url('Index', 'index'));
        exit;
    }
}
```

在控制器中使用中间件：

```php
class Controller_Admin extends FLEA_Controller_Action
{
    public function actionIndex()
    {
        // 检查管理员权限
        checkPermission(['ADMIN']);

        // 执行操作
    }

    public function actionEdit()
    {
        // 检查编辑权限
        checkPermission(['ADMIN', 'EDITOR']);

        // 执行操作
    }
}
```

### RBAC 最佳实践

1. **集中管理 ACT**：将访问控制表定义在配置文件或常量中，便于维护

```php
// config/permissions.php
return [
    'ACT_ADMIN' => [
        'allow' => ['ADMIN'],
        'deny' => RBAC_NULL,
    ],
    'ACT_EDITOR' => [
        'allow' => ['ADMIN', 'EDITOR'],
        'deny' => RBAC_NULL,
    ],
];
```

2. **角色命名规范**：使用大写字母，便于识别

3. **权限继承**：通过组合多个角色实现更复杂的权限控制

4. **日志记录**：记录权限检查失败的情况，便于审计

```php
if (!$rbac->check($roles, $ACT)) {
    log_message("权限检查失败: 用户 " . $user['username'] . " 试图访问受保护的资源");
    js_alert('无权访问');
}
```

5. **最小权限原则**：只赋予用户所需的最小权限

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
