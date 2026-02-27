# GIT_COMMIT.md

记录每次代码改动的 git commit 说明。

---

### fix: 修复 UsersManager.php 中多余的 & 引用

移除 `fetchRoles()` 方法中多余的 `&` 引用符号。

**修改的文件:**
- `FLEA/FLEA/Rbac/UsersManager.php`: 第 406 行 `$link =& $this->getLink(...)` → `$link = $this->getLink(...)`

---

### refactor: 移除 Controller\Action 中的下划线命名前缀

移除 `FLEA\Controller\Action` 类中属性和方法的单下划线 `_` 前缀，改用 `protected` 访问修饰符并添加类型声明和默认值，采用现代 PHP 命名规范和 PSR-12 编码规范。

**修改的文件:**
- `FLEA/FLEA/Controller/Action.php`: 属性和方法移除下划线前缀，添加类型声明 (`public $_controllerName` → `protected string $controllerName = ''`, `public $_actionName` → `protected string $actionName = ''`, `public $_dispatcher` → `protected ?\FLEA\Dispatcher\Auth $dispatcher = null`, `public $_renderCallbacks` → `protected array $renderCallbacks = []`, `_getComponent()` → `getComponent()`, `_getDispatcher()` → `getDispatcher()`, `_url()` → `url()`, `_forward()` → `forward()`, `_getView()` → `getView()`, `_executeView()` → `executeView()`, `_isPOST()` → `isPost()`, `_isAjax()` → `isAjax()`, `_registerEvent()` → `registerEvent()`, `_registerRenderCallback()` → `registerRenderCallback()`)
- `App/Controller/PostController.php`: 方法调用更新 (`$this->_getView()` → `$this->getView()`)
- `FLEA/FLEA/Helper/ImgCode.php`: 文档注释更新 (`$this->_url('imgcode')` → `$this->url('imgcode')`)

---

## 2026-02-27

### refactor: 移除 Dispatcher 目录中的下划线命名前缀

移除 `FLEA\Dispatcher\Simple` 和 `FLEA\Dispatcher\Auth` 类中属性和方法的单下划线 `_` 前缀，改用 `protected` 访问修饰符并添加类型声明和默认值，采用现代 PHP 命名规范和 PSR-12 编码规范。

**修改的文件:**
- `FLEA/FLEA/Dispatcher/Simple.php`: 属性和方法移除下划线前缀，添加类型声明 (`public $_request` → `protected array $request = []`, `public $_requestBackup` → `protected array $requestBackup = []`, `_executeAction()` → `executeAction()`, `_loadController()` → `loadController()`)
- `FLEA/FLEA/Dispatcher/Auth.php`: 属性和方法移除下划线前缀，添加类型声明 (`public $_auth` → `protected ?\FLEA\Rbac $auth = null`, `_loadACTFile()` → `loadACTFile(string): array`), 方法调用更新，其他方法添加类型声明 (`clearUser(): void`, `check(string, ?string, ?string): bool`, `getControllerACT(string, string): ?array`, `getControllerACTFromDefaultFile(string): ?array`)

---

## 2026-02-27

### docs: 完善 USER_GUIDE.md 用户手册

扩充用户手册内容，添加更多详细示例和说明。

**修改的文件:**
- `USER_GUIDE.md`: 添加分页功能、Ajax 支持、RBAC 权限控制、ACL 访问控制列表、Session 管理、日志服务、辅助类、常见问题等内容

---

## 2026-02-27

### docs: 更新 USER_GUIDE.md 和 README.md

根据框架当前代码状态重新生成用户手册和项目说明文档。

**修改的文件:**
- `USER_GUIDE.md`: 重新生成用户手册，基于当前框架实际代码
- `README.md`: 更新项目说明，反映当前博客系统实际功能
- `SPEC.md`: 新增框架规格说明书

---

## 2026-02-27

### refactor: 移除构造函数中的 @return 注解

构造函数不返回值，移除其 docblock 注释中的 `@return` 注解。

**修改的文件:**
- `FLEA/FLEA/Ajax.php`
- `FLEA/FLEA/Controller/Action.php`
- `FLEA/FLEA/Db/ActiveRecord.php`
- `FLEA/FLEA/Db/TableDataGateway.php`
- `FLEA/FLEA/Db/TableLink.php`
- `FLEA/FLEA/Db/TableLink/BelongsToLink.php`
- `FLEA/FLEA/Db/TableLink/ManyToManyLink.php`
- `FLEA/FLEA/Dispatcher/Auth.php`
- `FLEA/FLEA/Dispatcher/Simple.php`
- `FLEA/FLEA/Session/Db.php`
- `FLEA/FLEA/View/Simple.php`
- `FLEA/FLEA/WebControls.php`
- `FLEA/FLEA/Language.php`
- `FLEA/FLEA/Rbac.php`
- `FLEA/FLEA/Rbac/RolesManager.php`
- `FLEA/FLEA/Db/Exception/*.php` (7 个异常类)
- `FLEA/FLEA/Dispatcher/Exception/CheckFailed.php`
- `FLEA/FLEA/Exception/*.php` (14 个异常类)
- `FLEA/FLEA/Rbac/Exception/*.php` (2 个异常类)
- `FLEA/FLEA/Helper/FileUploader.php`
- `FLEA/FLEA/Helper/FileUploader/File.php`
- `FLEA/FLEA/Helper/Image.php`

---

## 2026-02-27

### refactor: create() 方法返回类型改为 int

将 `create()` 方法的返回类型从 `int|false` 改为 `: int`，失败时返回 `0` 而不是 `false`。

**修改的文件:**
- `FLEA/FLEA/Db/TableDataGateway.php`: `create()` 返回类型改为 `: int`，4 处 `return false` 改为 `return 0`；`save()` 返回类型从 `: bool` 改为 `: int`；`createRowset()` 返回类型从 `: bool` 改为无类型声明，失败时返回 `0`
- `FLEA/FLEA/Rbac/UsersManager.php`: `create()` 添加返回类型 `: int`
- `FLEA/FLEA/Acl/Table/UserGroups.php`: `create()` 添加返回类型 `: int`，`return false` 改为 `return 0`
- `App/Model/Post.php`: `createPost()` docblock 从 `@return int|false` 改为 `@return int`
- `App/Model/Comment.php`: `createComment()` docblock 从 `@return int|false` 改为 `@return int`

---

## 2026-02-26

### refactor: TableDataGateway 及子类添加类型声明

为 TableDataGateway 基类和所有子类添加 PHP 类型声明，提高类型安全性和 IDE 支持。

**基类修改 (FLEA/FLEA/Db/TableDataGateway.php):**
- `$tableName`: `string` (初始值改为 `''`)
- `$fullTableName`: `?string`
- `$primaryKey`: 无类型 (支持 string|array)
- `$hasOne`, `$belongsTo`, `$hasMany`, `$manyToMany`: `?array`
- `$schema`: `string`

**FLEA 子类修改:**
- `Rbac/RolesManager.php`: `$tableName` 添加 `string`
- `Rbac/UsersManager.php`: `$tableName` 添加 `string`，`create()` 和 `update()` 方法签名与父类对齐
- `Acl/Table/Roles.php`: `$tableName` 添加 `string`, `$manyToMany` 添加 `?array`
- `Acl/Table/Permissions.php`: `$tableName` 添加 `string`
- `Acl/Table/UserGroups.php`: `$tableName` 添加 `string`, `$manyToMany` 添加 `?array`
- `Acl/Table/UserGroupsHasRoles.php`: `$tableName` 添加 `string`
- `Acl/Table/UserGroupsHasPermissions.php`: `$tableName` 添加 `string`
- `Acl/Table/UsersHasRoles.php`: `$tableName` 添加 `string`
- `Acl/Table/UsersHasPermissions.php`: `$tableName` 添加 `string`

**App 子类修改:**
- `App/Model/Post.php`: `$tableName` 添加 `string`, `$hasMany` 添加 `?array`
- `App/Model/Comment.php`: `$tableName` 添加 `string`, `$belongsTo` 添加 `?array`

---

## 2026-02-26

### fix: 修复 ManyToManyLink.php 中 DELETE 语句多余的括号

第 214 行 DELETE 语句不需要闭合括号，移除多余的 . ')'

**修改:**
- `Db/TableLink/ManyToManyLink.php`: DELETE 语句移除多余的 `.')'`

---

## 2026-02-26

### fix: 修复数组语法错误，将 ); 改为 ];

修复之前将 array() 替换为 [] 时遗留的括号不匹配问题

**修改的文件:**
- `Config/DEBUG_MODE_CONFIG.php`: 结尾 `);` → `];`
- `Config/DEPLOY_MODE_CONFIG.php`: 结尾 `);` → `];`
- `_Errors/default/ErrorMessage.php`: 结尾 `);` → `];`
- `_Errors/chinese-utf8/ErrorMessage.php`: 结尾 `);` → `];`
- `Db/TableLink/ManyToManyLink.php`: 修复 `execute()` 调用中的括号
- `Acl/testCreateData.php`: 多处数组闭合括号 `);` → `];`

---

## 2026-02-26

### refactor: Acl 目录使用 PSR-4 命名空间和更新旧注释

**代码改动:**
- `FLEA/FLEA/Acl/Manager.php`: `$_tableClass` 属性从旧式类名字符串改为 `\FLEA\Acl\Table\*::class`
- `FLEA/FLEA/Acl/Table/UserGroups.php`: `$manyToMany` 数组改用 ::class 常量
- `FLEA/FLEA/Acl/Table/Users.php`: `$belongsTo` 和 `$manyToMany` 数组改用 ::class 常量
- `FLEA/FLEA/Acl/Table/Roles.php`: `$manyToMany` 数组改用 ::class 常量
- 其他 Acl 文件：更新注释中的旧式类名为 PSR-4 格式

**注释更新:**
- `Manager.php`: `\FLEA\Acl_Manager` → `\FLEA\Acl\Manager`
- `Table/UserGroups.php`: `\FLEA\Acl_Table_UserGroups` → `\FLEA\Acl\Table\UserGroups`
- `Table/Users.php`: `\FLEA\Acl_Table_Users` → `\FLEA\Acl\Table\Users`
- `Table/Roles.php`: `\FLEA\Acl_Table_Roles` → `\FLEA\Acl\Table\Roles`
- `Table/Permissions.php`: `\FLEA\Acl_Table_Permissions` → `\FLEA\Acl\Table\Permissions`
- `Table/UserGroupsHasRoles.php`: `\FLEA\Acl_Table_UserGroupsHasRoles` → `\FLEA\Acl\Table\UserGroupsHasRoles`
- `Table/UserGroupsHasPermissions.php`: `\FLEA\Acl_Table_UserGroupsHasPermissions` → `\FLEA\Acl\Table\UserGroupsHasPermissions`
- `Table/UsersHasRoles.php`: `\FLEA\Acl_Table_UsersHasRoles` → `\FLEA\Acl\Table\UsersHasRoles`
- `Table/UsersHasPermissions.php`: `\FLEA\Acl_Table_UsersHasPermissions` → `\FLEA\Acl\Table\UsersHasPermissions`
- `Exception/UserGroupNotFound.php`: `\FLEA\Acl_Exception_UserGroupNotFound` → `\FLEA\Acl\Exception\UserGroupNotFound`

---

## 2026-02-26

### refactor: 将 array() 替换为短数组语法 []

批量将代码中的 array() 替换为 PHP 5.4+ 支持的短数组语法 []
同时优化部分 isset 三元表达式为 ?? 运算符

**App 目录**
- App/Config.php: 配置数组使用 [] 语法
- App/Controller/PostController.php: 1 处 ?? 运算符优化
- App/Model/Post.php: 数组定义和查询条件使用 [] 语法
- App/Model/Comment.php: 数组定义和查询条件使用 [] 语法

**FLEA/FLEA 目录**
- Acl/*: 多个文件数组定义使用 [] 语法
- Ajax.php: 数组定义使用 [] 语法
- Config/*: 配置数组使用 [] 语法
- Db/*: 多个文件数组定义和返回值使用 [] 语法
- Dispatcher/*: 数组定义使用 [] 语法
- Helper/*: 多个文件数组定义和返回值使用 [] 语法
- Log.php: 回调函数使用 [] 语法
- Rbac.php: 数组定义使用 [] 语法
- Rbac/UsersManager.php: 数组定义使用 [] 语法
- Session/Db.php: session_set_save_handler 使用 [] 语法
- View/Simple.php: 数组定义使用 [] 语法
- WebControls.php: 多个 extractAttribs 调用使用 [] 语法
- _Errors/*: ErrorMessage 使用 [] 语法

---

## 2026-02-26

### refactor: 使用 PHP 7.4 数组解构和 null 合并运算符简化代码

**FLEA/FLEA/Ajax.php**
- 1 处 `isset()` 三元表达式改为 `??` 运算符

**FLEA/FLEA/Controller/Action.php**
- `_isAjax()`：1 处 `isset()` 三元表达式改为 `??` 运算符

**FLEA/FLEA/_Errors/_common/header.php**
- 2 处 `isset()` 三元表达式改为 `??` 运算符
- 1 处 `isset()` 三元表达式改为 `??` 条件表达式

---

## 2026-02-26

### fix: 修复关联查询中 TableDataGateway 类型错误

- `FLEA/FLEA/Db/TableDataGateway.php`：`$assocRowset = null` 改为 `[]`（匹配 array 类型）
- `FLEA/FLEA/Db/TableDataGateway.php`：3 处 `assemble()` 调用使用 `sql_statement()` 包装
- `FLEA/FLEA/Db/TableLink.php`：`init()` 添加 null 检查，防止 `isRegistered(null)` 错误

---

### feat: 建立 Post 和 Comment 模型关联关系并优化控制器查询

- `App/Model/Post.php`：添加 `$hasMany` 关联，一个文章有多个评论
- `App/Model/Comment.php`：添加 `$belongsTo` 关联，一个评论属于一个文章
- `App/Controller/PostController.php`：actionView() 利用关联减少数据库查询（3 次 → 1 次）
- `App/Model/Post.php`：getPublishedPosts() 禁用关联查询，避免不必要开销

---

### fix: 修复模型中 TableDataGateway 方法调用错误及移除冗余时间戳

- `App/Model/Post.php`：`updatePost()` 改用 `updateByConditions()`，代码更清晰
- `App/Model/Post.php`：`deletePost()` 改用 `removeByPkv()`，修复参数类型错误
- `App/Model/Post.php`：`createPost()` 移除冗余时间戳设置（框架自动处理）
- `App/Model/Comment.php`：`deleteComment()` 改用 `removeByPkv()`，修复参数类型错误
- `App/Model/Comment.php`：`createComment()` 移除冗余时间戳设置（框架自动处理）

---

### feat: 替换控制器中 die() 函数为框架异常处理

- 将 actionView() 方法中的 die() 调用替换为 \FLEA\Exception\InvalidArguments 异常
- 将 actionEdit() 方法中的 die() 调用替换为 \FLEA\Exception\InvalidArguments 异常
- 将 actionDelete() 方法中的 die() 调用替换为 \FLEA\Exception\InvalidArguments 异常
- 提升了错误处理的一致性和健壮性

---

### fix: 修复多处函数返回类型与实际返回值不一致

- `FLEA.php` `parseDSN()`：`return false` → `return null`（匹配 `?array`）
- `AbstractDriver.php` `affectedRows()`：`return false` → `return 0`（匹配 `: int`）
- `AbstractDriver.php` `startTrans()`：末尾补充 `return true`（匹配 `: bool`）
- `AbstractDriver.php` `completeTrans()`：else 分支后补充 `return true`（匹配 `: bool`）
- `AbstractDriver.php` `getPlaceholderPair()`：返回类型 `string` → `array`

---

### fix: 修复 TableDataGateway::insert() 中 execute() 调用错误

- `Db/TableDataGateway.php` 第 1090 行：`Execute($sql, ...)` 改为 `execute(sql_statement($sql), ...)`，与其他调用一致

---

### fix: 修复 AbstractDriver::getPlaceholder() 返回类型声明错误

- `Db/Driver/AbstractDriver.php`：`getPlaceholder()` 返回类型由错误的 `string` 改为 `array`

---

### refactor(log): 移除 AbstractDriver 中 log_message 的存在性检查

- `Db/Driver/AbstractDriver.php`：移除 `function_exists('log_message')` 检查，`log_message` 始终可用

---

### refactor(log): log_message() 调用处改用 LogLevel 常量

- `View/Simple.php`：`'debug'` → `LogLevel::DEBUG`
- `Db/Driver/Mysql.php`：`'debug'` → `LogLevel::DEBUG`

---

### refactor(log): logErrorLevel 改用 \Psr\Log\LogLevel 常量

- `DEPLOY_MODE_CONFIG.php` / `DEBUG_MODE_CONFIG.php`：`logErrorLevel` 从逗号字符串改为 `LogLevel` 常量数组
- `Log.php`：解析逻辑简化为 `array_flip((array)$config)`
- `Functions.php`：`log_message()` 默认级别改为 `LogLevel::DEBUG`
- 映射：原 `exception` → `LogLevel::CRITICAL`，原 `log` 移除

---

### refactor(log): 移除 appendLog，log_message() 直接调用 log()

- `\FLEA\Log` 移除 `appendLog()` 方法
- `Functions.php` 的 `log_message()` 保留 `$title` 参数和 `print_r` 处理逻辑，改为直接调用 `$instance->log($level, $message)`

---

### feat(log): 升级 \FLEA\Log 支持 PSR-3 LoggerInterface

- `composer require psr/log:^1.1`（PHP 7.4 兼容版本）
- `\FLEA\Log` 继承 `Psr\Log\AbstractLogger`，自动获得 `debug()`、`info()`、`warning()`、`error()` 等标准方法
- 实现 `log($level, $message, array $context)` 作为核心方法，支持 `{key}` 占位符插值
- 保留 `appendLog()` 向后兼容，供 `log_message()` 函数调用，行为不变

---



### fix(security): 修复 extract() 变量注入风险

> **注：此改动已被撤销，记录仅供参考**
> - `View/Simple.php`：`extract($this->vars)` → `extract($this->vars, EXTR_SKIP)`
> - `Controller/Action.php`：`extract($data)` → `extract($data, EXTR_SKIP)`

---

### feat(php74): 使用 PHP 7.4 新特性改进代码

**FLEA/FLEA.php**
- `parseDSN()`：8 处 `isset()` 三元表达式改为 `??=`
- `parseDSN()`：5 处 `isset()` 三元表达式改为 `??`，driver 字段简化为 `strtolower($parse['scheme'] ?? '')`

**FLEA/FLEA/Log.php**
- 属性加类型声明：`string $_log`、`string $dateFormat`、`?string $_logFileDir`、`?string $_logFilename`、`bool $_enabled`、`?array $_errorLevel`
- 3 处 `list()` 解构改为 `[]` 语法

**FLEA/FLEA/View/Simple.php**
- 属性加类型声明：`?string $templateDir`、`int $cacheLifetime`、`bool $enableCache`、`string $cacheDir`、`array $vars`、`array $cacheState`

**FLEA/FLEA/Ajax.php**
- 1 处 `list()` 解构改为 `[]` 语法

**FLEA/FLEA/Helper/Pager.php**
- 属性加类型声明：所有 `int` 分页属性、`?string $_sortby`、`?\FLEA\Db\Driver\AbstractDriver $dbo`
