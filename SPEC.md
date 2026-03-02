# FleaPHP 框架规格说明书

## 1. 概述

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 自动加载机制，支持 PHP 7.4+。

**版本**: 1.7.1524
**命名空间**: `FLEA\`
**许可证**: 查看 LICENSE.txt

---

## 2. 目录结构

```
FLEA/
├── FLEA/                      # 框架核心代码
│   ├── Acl/                   # ACL (访问控制列表) 管理
│   │   ├── Exception/         # ACL 相关异常
│   │   ├── Table/             # ACL 数据表入口
│   │   └── Manager.php        # ACL 管理器
│   ├── Ajax.php               # Ajax 支持类
│   ├── Config.php             # 配置管理器 (单例)
│   ├── Controller/            # 控制器基类
│   │   └── Action.php         # 动作控制器基类
│   ├── Db/                    # 数据库相关组件
│   │   ├── Driver/            # 数据库驱动
│   │   ├── Exception/         # 数据库异常
│   │   ├── TableLink/         # 表关联处理
│   │   ├── ActiveRecord.php   # ActiveRecord 模式实现
│   │   ├── SqlStatement.php   # SQL 语句处理
│   │   ├── TableDataGateway.php # 表数据入口 (CRUD)
│   │   └── TableLink.php      # 表关联基类
│   ├── Dispatcher/            # 请求调度器
│   │   ├── Exception/         # 调度器异常
│   │   ├── Auth.php           # 认证调度器
│   │   └── Simple.php         # 简单调度器
│   ├── Exception/             # 框架通用异常
│   ├── Helper/                # 辅助类
│   │   ├── FileUploader/      # 文件上传
│   │   ├── Image.php          # 图像处理
│   │   ├── ImgCode.php        # 验证码
│   │   └── Pager.php          # 分页器
│   ├── Language.php           # 多语言支持
│   ├── Log.php                # 日志服务 (PSR-3)
│   ├── Rbac/                  # RBAC (基于角色的访问控制)
│   │   ├── Exception/         # RBAC 异常
│   │   ├── RolesManager.php   # 角色管理
│   │   └── UsersManager.php   # 用户管理
│   ├── Rbac.php               # RBAC 服务类
│   ├── Session/               # Session 处理
│   │   └── Db.php             # 数据库 Session
│   ├── View/                  # 视图引擎
│   │   └── Simple.php         # 简单模板引擎
│   └── WebControls.php        # Web 控件库
├── FLEA.php                   # 框架入口文件
└── 3rd/                       # 第三方库
```

---

## 3. 核心组件

### 3.1 FLEA 类

框架的主入口类，提供静态方法管理框架服务：

- `FLEA::loadAppInf()` - 载入应用程序配置
- `FLEA::getAppInf()` - 获取配置值
- `FLEA::setAppInf()` - 设置配置值
- `FLEA::getSingleton()` - 获取单例实例
- `FLEA::register()` - 注册对象实例
- `FLEA::isRegistered()` - 检查对象是否已注册
- `FLEA::getDBO()` - 获取数据库访问对象
- `FLEA::runMVC()` - 运行 MVC 应用程序

### 3.2 Config (配置管理器)

单例模式管理框架配置：

```php
namespace FLEA;

class Config
{
    public $appInf = [];       // 应用程序配置
    public $objects = [];      // 对象实例容器
    public $dbo = [];          // 数据库访问对象
}
```

### 3.3 MVC 架构

#### 控制器 (Controller)

```php
namespace FLEA\Controller;

class Action
{
    protected string $controllerName = '';    // 当前控制器名
    protected string $actionName = '';        // 当前动作名
    protected ?\FLEA\Dispatcher\Simple $dispatcher = null;  // 调度器
    public $components = [];                  // 组件列表
    protected array $renderCallbacks = [];    // 渲染回调

    // 生命周期方法
    public function setController($controllerName, $actionName): void
    public function setDispatcher(\FLEA\Dispatcher\Simple $dispatcher): void
    public function beforeExecute($actionMethod): void
    public function afterExecute($actionMethod): void

    // 辅助方法
    protected function getComponent(string $componentName): object
    protected function getDispatcher(): ?\FLEA\Dispatcher\Simple
    protected function url(?string $actionName = null, ?array $args = null, ?string $anchor = null): string
    protected function forward(?string $controllerName = null, ?string $actionName = null): void
    protected function getView(): \FLEA\View\ViewInterface
    protected function executeView(string $__flea_internal_viewName, ?array $data = null): void
    protected function isPost(): bool
    protected function isAjax(): bool
    protected function registerEvent(string $controlName, string $event, string $action, ?array $attribs = null): string
    protected function registerRenderCallback($callback): void
}
```

#### 模型 (Model)

```php
namespace FLEA\Db;

class TableDataGateway
{
    public string $tableName = '';          // 表名
    public string $fullTableName = '';     // 完整表名
    public $primaryKey = null;              // 主键 (string|array)
    public array $hasOne = [];             // 一对一关联
    public array $belongsTo = [];          // 从属关联
    public array $hasMany = [];            // 一对多关联
    public array $manyToMany = [];         // 多对多关联

    // CRUD 方法
    public function find($conditions, $sort = null, $fields = '*', $queryLinks = true): ?array
    public function findAll($conditions = null, $sort = null, $limit = null, $fields = '*', $queryLinks = true): array
    public function create(array &$row, bool $saveLinks = true): int
    public function update(array &$row, bool $saveLinks = true): bool
    public function remove(array &$row, bool $removeLink = true): bool
}
```

#### 视图 (View)

```php
namespace FLEA\View;

class Simple
{
    public ?string $templateDir = null;    // 模板目录
    public int $cacheLifetime;              // 缓存过期时间
    public bool $enableCache;               // 是否启用缓存
    public string $cacheDir;                // 缓存目录
    public array $vars = [];                // 模板变量

    public function assign($key, $value = null): void
    public function display($template): void
}
```

### 3.4 Dispatcher (调度器)

```php
namespace FLEA\Dispatcher;

class Simple
{
    protected array $request = [];           // 请求信息
    protected array $requestBackup = [];     // 原始请求

    public function __construct(array &$request)
    public function dispatching()              // 执行调度
    public function getControllerName(): string
    public function getActionName(): string
    public function setControllerName(string $controllerName): void
    public function setActionName(string $actionName): void
    public function parseUrl(string $url): array
    public function getControllerClass(string $controllerName): string

    protected function executeAction(string $controllerName, string $actionName, string $controllerClass)
    protected function loadController(string $controllerClass): bool
}
```

#### Auth (认证调度器)

```php
namespace FLEA\Dispatcher;

class Auth extends Simple
{
    protected ?\FLEA\Rbac $auth = null;       // 验证服务对象

    public function __construct(array $request)
    public function dispatching()
    public function getAuthProvider(): \FLEA\Rbac
    public function setAuthProvider(\FLEA\Rbac $auth): void
    public function setUser(array $userData, $rolesData = null): void
    public function getUser(): array
    public function getUserRoles(): array
    public function clearUser(): void
    public function check(string $controllerName, ?string $actionName = null, ?string $controllerClass = null): bool
    public function getControllerACT(string $controllerName, string $controllerClass): ?array
    public function getControllerACTFromDefaultFile(string $controllerName): ?array

    protected function loadACTFile(string $actFilename): array
}
```

---

## 4. 数据库组件

### 4.1 TableDataGateway

核心数据访问类，提供完整的 CRUD 操作：

**查询方法**:
- `find()` - 查询单条记录
- `findByField()` - 按字段查询
- `findByPkv()` - 按主键查询
- `findAll()` - 查询多条记录
- `findCount()` - 统计记录数

**操作方法**:
- `create()` - 创建记录 (返回 insert ID)
- `save()` - 保存记录
- `update()` - 更新记录
- `updateByConditions()` - 按条件更新
- `remove()` - 删除记录
- `removeByPkv()` - 按主键删除

**关联类型**:
| 常量 | 值 | 说明 |
|------|-----|------|
| HAS_ONE | 1 | 一对一关联 |
| BELONGS_TO | 2 | 从属关联 |
| HAS_MANY | 3 | 一对多关联 |
| MANY_TO_MANY | 4 | 多对多关联 |

### 4.2 ActiveRecord

ActiveRecord 模式实现：

```php
namespace FLEA\Db;

class ActiveRecord
{
    public $_table;           // TableDataGateway 实例
    public $_idname;          // 主键属性名
    public $_mapping = false; // 字段映射
    public $init = false;     // 是否已初始化

    public function __construct($conditions = null)
    public function init(): void
    public function load($conditions): void
}
```

---

## 5. 权限管理

### 5.1 RBAC (基于角色的访问控制)

```php
namespace FLEA;

class Rbac
{
    public $_sessionKey = 'RBAC_USERDATA';  // Session 键名
    public $_rolesKey = 'RBAC_ROLES';       // 角色数据键名

    public function setUser(array $userData, $rolesData = null): void
    public function getUser(): ?array
    public function checkAccess($act, $roles = null): bool
}
```

### 5.2 ACL (访问控制列表)

```php
namespace FLEA\Acl;

class Manager
{
    public $_tableClass = [
        'users' => \FLEA\Acl\Table\Users::class,
        'roles' => \FLEA\Acl\Table\Roles::class,
        'userGroups' => \FLEA\Acl\Table\UserGroups::class,
        'permissions' => \FLEA\Acl\Table\Permissions::class,
        // ...
    ];

    public function getUserWithPermissions($conditions): ?array
    public function checkPermission($user, $permission): bool
}
```

---

## 6. 辅助组件

### 6.1 Pager (分页器)

```php
namespace FLEA\Helper;

class Pager
{
    public $source;           // 数据源 (TableDataGateway 或 SQL)
    public $dbo = null;       // 数据库访问对象
    public int $pageSize = -1;     // 每页记录数
    public int $totalCount = -1;   // 总记录数
    public int $pageCount = -1;    // 总页数

    public function __construct($source, $conditions = null, $sort = null)
    public function setPage($page): void
    public function getPage(): int
    public function exec(): array
}
```

### 6.2 Ajax

```php
namespace FLEA;

class Ajax
{
    public $events;           // 已注册事件
    public $paramsType = [];  // 参数类型定义

    public function registerEvent($control, $event, $url, $attribs = null): string
    public function dumpJs($return = false, $wrapper = true): ?string
}
```

### 6.3 Log (日志服务)

实现 PSR-3 LoggerInterface：

```php
namespace FLEA;

class Log extends AbstractLogger
{
    public string $_log = '';           // 运行期间日志
    public string $dateFormat = 'Y-m-d H:i:s';
    public ?string $_logFileDir = null; // 日志目录
    public ?string $_logFilename = null;// 日志文件
    public bool $_enabled = true;       // 是否启用
    public ?array $_errorLevel = null;  // 错误级别

    public function log($level, $message, array $context = []): void
}
```

### 6.4 Session (数据库存储)

```php
namespace FLEA\Session;

class Db
{
    public $dbo = null;              // 数据库访问对象
    public $tableName = null;        // Session 表名
    public $fieldId = null;          // Session ID 字段
    public $fieldData = null;        // Session 数据字段
    public $fieldActivity = null;    // 活动时间字段
    public int $lifeTime = 0;        // 有效期

    public function __construct()
    public function open($savePath, $sessionName): bool
    public function close(): bool
    public function read($id): string
    public function write($id, $data): bool
    public function destroy($id): bool
    public function gc($maxlifetime): int
}
```

---

## 7. 配置系统

### 7.1 配置加载顺序

1. `FLEA.php` 自动加载
2. 根据 `DEPLOY_MODE` 常量加载默认配置
   - 调试模式: `Config/DEBUG_MODE_CONFIG.php`
   - 部署模式: `Config/DEPLOY_MODE_CONFIG.php`
3. 应用程序配置通过 `FLEA::loadAppInf()` 加载

### 7.2 关键配置项

```php
// 数据库配置
'	dbDSN' => 'mysql://user:pass@localhost/dbname',

// 调度器配置
'dispatcher' => \FLEA\Dispatcher\Simple::class,
'controllerAccessor' => 'controller',
'actionAccessor' => 'action',

// 视图配置
'view' => \FLEA\View\Simple::class,
'viewConfig' => [
    'templateDir' => './View',
    'cacheDir' => './cache',
    'cacheLifeTime' => 900,
    'enableCache' => false,
],

// Session 配置
'sessionProvider' => \FLEA\Session\Db::class,
'sessionDbTableName' => 'sessions',

// 日志配置
'logFileDir' => './logs',
'logFilename' => 'app.log',
'logErrorLevel' => [LogLevel::ERROR, LogLevel::WARNING],

// RBAC 配置
'RBACSessionKey' => 'USER_DATA',
```

---

## 8. 异常处理

### 8.1 通用异常

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

### 8.2 数据库异常

| 异常类 | 说明 |
|--------|------|
| `MissingDSN` | 缺少 DSN 配置 |
| `InvalidDSN` | 无效的 DSN |
| `InvalidInsertID` | 无效的插入 ID |
| `MissingPrimaryKey` | 缺少主键 |
| `PrimaryKeyExists` | 主键已存在 |
| `SqlQuery` | SQL 查询错误 |
| `MissingLink` | 关联不存在 |

---

## 9. 请求生命周期

```
1. 载入 FLEA.php
   ↓
2. 初始化配置 (Config)
   ↓
3. FLEA::runMVC()
   ↓
4. Dispatcher 解析请求 (controller/action)
   ↓
5. 实例化控制器
   ↓
6. 执行 _beforeExecute()
   ↓
7. 执行动作方法 (actionXxx)
   ↓
8. 执行 _afterExecute()
   ↓
9. 渲染视图
   ↓
10. 输出响应
```

---

## 10. 扩展点

### 10.1 自定义控制器

```php
namespace App\Controller;

use FLEA\Controller\Action;

class MyController extends Action
{
    public function __construct()
    {
        parent::__construct('My');
    }

    public function actionIndex(): void
    {
        // 处理逻辑
        $this->view->assign('data', $result);
        $this->view->display('my/index.php');
    }
}
```

### 10.2 自定义模型

```php
namespace App\Model;

use FLEA\Db\TableDataGateway;

class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public $primaryKey = 'id';

    public function getPublishedPosts($limit = 10)
    {
        return $this->findAll(
            ['status' => 1],
            'created_at DESC',
            [$limit, 0]
        );
    }
}
```

### 10.3 自定义调度器

继承 `FLEA\Dispatcher\Simple` 并重写 `_executeAction()` 方法。

### 10.4 自定义视图引擎

实现与 `FLEA\View\Simple` 相同的接口。

---

## 11. 约定规范

### 11.1 命名约定

- **控制器**: `XxxController` (首字母大写)
- **模型**: `Xxx` (表名单数形式)
- **动作方法**: `actionXxx()` (驼峰式)
- **视图文件**: `{controller}/{action}.php`

### 11.2 URL 格式

```
标准模式：index.php?controller=Post&action=view&id=1
PATHINFO 模式：index.php/Post/view/id/1
URL 重写：/Post/view/id/1
```

### 11.3 数据库约定

- 时间戳字段: `created_at`, `updated_at`
- 主键字段: `id` 或 `{tablename}_id`
- 外键字段: `{tablename}_id`

---

## 12. PHP 7.4 特性

框架使用了以下 PHP 7.4 特性：

- **属性类型声明**: `public string $tableName`
- **可空类型**: `public ?string $sort = null`
- **箭头函数**: `fn($x) => $x * 2`
- **解构赋值**: `[$a, $b] = $array`
- **空合并运算符**: `$value ?? $default`

---

## 13. 依赖项

- **PHP**: 7.4+
- **PSR-3**: `psr/log:^1.1` (日志接口)
- **数据库**: MySQL 5.0+ (或其他 PDO 支持的数据库)
- **Web 服务器**: Apache/Nginx (可选，用于 URL 重写)

---

## 14. 版本历史

- **当前版本**: 1.7.1524
- **主要更新**:
  - PSR-4 自动加载
  - PHP 7.4 类型声明
  - PSR-3 日志接口
  - 移除构造函数中的 `@return` 注解
  - `create()` 方法返回类型改为 `int`

---

## 15. 参考

- [CLAUDE.md](CLAUDE.md) - 开发规范
- [CHANGES.md](CHANGES.md) - 代码修改记录
- [GIT_COMMIT.md](GIT_COMMIT.md) - Git 提交记录
