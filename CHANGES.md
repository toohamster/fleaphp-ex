# 代码修改记录

本文档记录对 FleaPHP 框架的所有修改。

---

## 2026-02-28

### refactor: TableDataGateway/AbstractDriver 返回类型修正及代码质量优化

**TableDataGateway.php:**
- `find()`：`is_array()` + `false` 改为 `!empty()` + `return null`，消除 `false` 与 `?array` 返回类型不匹配
- `findBySql()`：移除死代码 `if ($result->getSql())` 分支，直接 return
- `findAll()`、`findBySql()`：返回类型 `?array` → `array`（实际永远返回数组）
- `$fullTableName`：`?string = null` → `string = ''`
- 6 个属性的 `@var` 注释从 `array|null` → `array`（与实际类型声明 `array = []` 同步）

**Driver/AbstractDriver.php:**
- `getAll()`、`getAllWithFieldRefs()`：返回类型 `?array` → `array`（内部始终返回数组）

**Db/SqlStatement.php:**
- `$isResource` 属性添加 `bool` 类型声明

---

## 2026-02-28

### refactor: Driver 层 PARAM_STYLE 改为 protected const，?array 属性改为 array = []

**Driver/AbstractDriver.php:**
- `$PARAM_STYLE` 从 `public` 实例属性改为 `protected const PARAM_STYLE = '?'`（字面量）
- `getPlaceholder()`、`getPlaceholderPair()` 中通过局部变量 `$paramStyle = static::PARAM_STYLE` 承接

**Driver/Mysql.php:**
- 删除冗余的 `$PARAM_STYLE = DBO_PARAM_QM` 声明（值与父类相同）

**TableDataGateway.php:**
- 6 个属性从 `?array = null` 改为 `array = []`：`$hasOne`、`$belongsTo`、`$hasMany`、`$manyToMany`、`$validateRules`、`$lastValidationResult`
- `is_array($this->validateRules)` 简化为 `!empty($this->validateRules)`
- `createLink()` 入口增加 `empty()` 守卫，防止空数组触发 MissingLinkOption 异常

**App/Model/Post.php、Comment.php:**
- `?array` → `array` 同步父类类型

**FLEA/Acl/Table/Roles.php、Users.php、UserGroups.php:**
- `?array` / 无类型 → `array` 同步父类类型

---

### refactor: FLEA/Db 目录 PSR-1/PSR-12 合规性修复及 PHP 7.4 风格优化

对 `FLEA/FLEA/Db/` 目录下所有 PHP 文件进行 PSR-1/PSR-12 合规性修复及 PHP 7.4 风格优化。

**Driver/AbstractDriver.php:**
- 属性去 `_` 前缀：`$_insertId` → `$insertId`、`$_transCount` → `$transCount`、`$_hasFailedQuery` → `$hasFailedQuery`、`$_savepointStack` → `$savepointStack`
- 内部钩子方法加 `do` 前缀：`_insertId()` → `doInsertId()`、`_affectedRows()` → `doAffectedRows()`、`_startTrans()` → `doStartTrans()`、`_completeTrans()` → `doCompleteTrans()`
- SQL 模板属性从 `?string = null` 改为 `string = ''`，`nextId()`/`createSeq()`/`dropSeq()` 入口增加空值校验，为空时抛 `NotImplemented` 异常
- 13 个大写属性（`TRUE_VALUE`、`FALSE_VALUE`、`NULL_VALUE`、5 个 SQL 模板、5 个 `HAS_*` 开关）从 `public` 实例属性改为 `protected const`，所有引用从 `$this->PROP` 改为 `static::PROP`
- 补属性类型声明：`int`、`string`、`bool`、`?array`、`array` 等

**Driver/Mysql.php:**
- 属性去 `_` 前缀：`$_mysqlVersion` → `$mysqlVersion`
- 钩子方法重命名：`_insertId()` → `doInsertId()`、`_affectedRows()` → `doAffectedRows()`
- 7 个大写属性覆盖改为 `protected const`，引用改为 `static::PROP`
- 补属性类型声明：`string`、`bool`、`?\PDO`、`?\PDOStatement`

**Driver/Mysqlt.php:**
- 方法重命名：`_startTrans()` → `doStartTrans()`、`_completeTrans()` → `doCompleteTrans()`
- 属性引用更新：`$this->_hasFailedQuery` → `$this->hasFailedQuery`
- `$HAS_TRANSACTION` 改为 `protected const`

**TableLink.php:**
- 属性去 `_` 前缀：`$_req` → `$req`、`$_optional` → `$optional`
- 方法去 `_` 前缀：`_getFindSQLBase()` → `getFindSQLBase()`、`_saveAssocDataBase()` → `saveAssocDataBase()`
- 补 `public` 可见性：`saveAssocData()`、`calcCount()`
- 移除对象参数 `&`：`createLink(..., &$mainTDG)` → `createLink(..., $mainTDG)`
- 补构造函数参数类型：`(array $define, int $type, TableDataGateway $mainTDG)`
- 补属性类型声明：`bool`、`string`、`array` 等

**TableLink/BelongsToLink.php、HasOneLink.php、HasManyLink.php:**
- 补属性类型声明：`bool $oneToOne`
- 补构造函数参数类型（BelongsToLink）
- `getFindSQL()` 补参数和返回类型
- 补 `public` 可见性：`saveAssocData()`
- 更新父类方法调用：`parent::getFindSQLBase()`、`$this->saveAssocDataBase()`

**TableLink/ManyToManyLink.php:**
- 补所有属性类型声明：`bool`、`?TableDataGateway`、`?string` 等
- 5 个方法补 `public` 可见性
- 补构造函数参数类型
- `getFindSQL()`、`saveAssocData()` 补参数类型
- 更新 `$this->optional` 引用和 `parent::getFindSQLBase()` 调用

**TableDataGateway.php（Bug 修复 + 回调替换）:**
- 修正错误调用：`$this->_setCreatedTimeFields()` → `$this->setCreatedTimeFields()`、`$this->_setUpdatedTimeFields()` → `$this->setUpdatedTimeFields()`
- 替换旧式回调：`array(& $this->dbo, 'qstr')` → `[$this->dbo, 'qstr']`

**SqlHelper.php:**
- 替换旧式回调：`array($table->dbo, 'qstr')` → `[$table->dbo, 'qstr']`

**ActiveRecord.php:**
- 属性去 `_` 前缀：`$_aggregation` → `$aggregation`、`$_table` → `$table`、`$_idname` → `$idname`、`$_mapping` → `$mapping`
- 补 `public` 可见性：`static function define()` → `public static function define()`

**Exception/ 全部文件（10 个）:**
- 4 个文件补 `public` 可见性：InvalidDSN、InvalidInsertID、InvalidLinkType、MissingDSN 的 `__construct`
- 补属性类型声明：`string $tableName`、`string $name`、`string $option`、`string $sql`、`string $primaryKey`

---

### fix: 修复 UsersManager.php 中多余的 & 引用

移除 `fetchRoles()` 方法中多余的 `&` 引用符号。

**修改的文件:**
- `FLEA/FLEA/Rbac/UsersManager.php`: 第 406 行 `$link =& $this->getLink(...)` → `$link = $this->getLink(...)`

---

## 2026-02-27

### refactor: 移除 Dispatcher 目录中的下划线命名前缀

移除 `FLEA\Dispatcher\Simple` 和 `FLEA\Dispatcher\Auth` 类中属性和方法的单下划线 `_` 前缀，改用 `protected` 访问修饰符并添加类型声明和默认值，采用现代 PHP 命名规范和 PSR-12 编码规范。

**修改的文件:**
- `FLEA/FLEA/Dispatcher/Simple.php`
- `FLEA/FLEA/Dispatcher/Auth.php`

**Simple.php 改动:**
- 属性 `public $_request` → `protected array $request = []`
- 属性 `public $_requestBackup` → `protected array $requestBackup = []`
- 方法 `_executeAction()` → `executeAction()`
- 方法 `_loadController()` → `loadController()`

**Auth.php 改动:**
- 属性 `public $_auth` → `protected ?\FLEA\Rbac $auth = null`
- 方法 `_executeAction()` → `executeAction()` (调用父类)
- 方法 `_loadController()` → `loadController()` (调用父类)
- 方法 `_loadACTFile()` → `loadACTFile(string): array`
- 方法 `clearUser()` → `clearUser(): void`
- 方法 `check()` → `check(string, ?string, ?string): bool`
- 方法 `getControllerACT()` → `getControllerACT(string, string): ?array`
- 方法 `getControllerACTFromDefaultFile()` → `getControllerACTFromDefaultFile(string): ?array`

---

### refactor: 移除 Controller\Action 中的下划线命名前缀

移除 `FLEA\Controller\Action` 类中属性和方法的单下划线 `_` 前缀，改用 `protected` 访问修饰符并添加类型声明和默认值，采用现代 PHP 命名规范和 PSR-12 编码规范。

**修改的文件:**
- `FLEA/FLEA/Controller/Action.php`
- `App/Controller/PostController.php`

**Action.php 改动:**
- 属性 `public $_controllerName` → `protected string $controllerName = ''`
- 属性 `public $_actionName` → `protected string $actionName = ''`
- 属性 `public $_dispatcher` → `protected ?\FLEA\Dispatcher\Auth $dispatcher = null`
- 属性 `public $_renderCallbacks` → `protected array $renderCallbacks = []`
- 方法 `_getComponent()` → `getComponent()`
- 方法 `_getDispatcher()` → `getDispatcher()`
- 方法 `_url()` → `url()`
- 方法 `_forward()` → `forward()`
- 方法 `_getView()` → `getView()`
- 方法 `_executeView()` → `executeView()`
- 方法 `_isPOST()` → `isPost()`
- 方法 `_isAjax()` → `isAjax()`
- 方法 `_registerEvent()` → `registerEvent()`
- 方法 `_registerRenderCallback()` → `registerRenderCallback()`

**PostController.php 改动:**
- `$this->_getView()` → `$this->getView()`

**ImgCode.php 改动:**
- 文档注释更新：`$this->_url('imgcode')` → `$this->url('imgcode')`

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

**FLEA/FLEA/Db/TableDataGateway.php (基类)**
- `create()`: 返回类型改为 `: int`，所有 `return false` 改为 `return 0`
- `save()`: 返回类型从 `: bool` 改为 `: int` (调用 `create()`)
- `createRowset()`: 返回类型从 `: bool` 改为无类型声明，失败时返回 `0`

**FLEA/FLEA/Rbac/UsersManager.php**
- `create()`: 添加返回类型 `: int`

**FLEA/FLEA/Acl/Table/UserGroups.php**
- `create()`: 添加返回类型 `: int`，`return false` 改为 `return 0`

**App/Model/Post.php**
- `createPost()`: docblock 从 `@return int|false` 改为 `@return int`

**App/Model/Comment.php**
- `createComment()`: docblock 从 `@return int|false` 改为 `@return int`

---

## 2026-02-26

### refactor: TableDataGateway 及子类添加类型声明

为 TableDataGateway 基类和所有子类添加 PHP 类型声明，提高类型安全性和 IDE 支持。

**FLEA/FLEA/Db/TableDataGateway.php (基类)**
- `$tableName`: `string` (初始值改为 `''`)
- `$fullTableName`: `?string`
- `$primaryKey`: 无类型 (支持 string|array)
- `$hasOne`: `?array`
- `$belongsTo`: `?array`
- `$hasMany`: `?array`
- `$manyToMany`: `?array`
- `$schema`: `string`

**FLEA/FLEA/Rbac/RolesManager.php**
- `$tableName`: `string`

**FLEA/FLEA/Rbac/UsersManager.php**
- `$tableName`: `string`
- `create()` 方法：添加参数 `bool $saveLinks = true` 和返回类型 `: bool`
- `update()` 方法：添加参数 `bool $saveLinks = true`

**FLEA/FLEA/Acl/Table/Roles.php**
- `$tableName`: `string`
- `$manyToMany`: `?array`

**FLEA/FLEA/Acl/Table/Permissions.php**
- `$tableName`: `string`

**FLEA/FLEA/Acl/Table/UserGroups.php**
- `$tableName`: `string`
- `$manyToMany`: `?array`

**FLEA/FLEA/Acl/Table/UserGroupsHasRoles.php**
- `$tableName`: `string`

**FLEA/FLEA/Acl/Table/UserGroupsHasPermissions.php**
- `$tableName`: `string`

**FLEA/FLEA/Acl/Table/UsersHasRoles.php**
- `$tableName`: `string`

**FLEA/FLEA/Acl/Table/UsersHasPermissions.php**
- `$tableName`: `string`

**App/Model/Post.php**
- `$tableName`: `string`
- `$hasMany`: `?array`

**App/Model/Comment.php**
- `$tableName`: `string`
- `$belongsTo`: `?array`

---

## 2026-02-26

### fix: 修复 ManyToManyLink.php 中 DELETE 语句多余的括号

**FLEA/FLEA/Db/TableLink/ManyToManyLink.php**
- 第 214 行 DELETE 语句移除多余的 `.')'`

问题分析:
- 第 196 行 INSERT 语句：`VALUES (` 需要 `')'` 闭合 - 正确
- 第 214 行 DELETE 语句：没有括号，不需要 `')'` - 多余，已删除

---

## 2026-02-26

### fix: 修复数组语法错误，将 ); 改为 ];

修复之前将 `array()` 批量替换为 `[]` 时遗留的括号不匹配问题

**FLEA/FLEA/Config/DEBUG_MODE_CONFIG.php**
- 结尾 `);` 改为 `];`

**FLEA/FLEA/Config/DEPLOY_MODE_CONFIG.php**
- 结尾 `);` 改为 `];`

**FLEA/FLEA/_Errors/default/ErrorMessage.php**
- 结尾 `);` 改为 `];`

**FLEA/FLEA/_Errors/chinese-utf8/ErrorMessage.php**
- 结尾 `);` 改为 `];`

**FLEA/FLEA/Db/TableLink/ManyToManyLink.php**
- 修复 `execute()` 调用中的括号匹配问题

**FLEA/FLEA/Acl/testCreateData.php**
- 多处数组闭合括号 `);` 改为 `];`

---

## 2026-02-26

### refactor: Acl 目录使用 PSR-4 命名空间和 ::class 常量

**FLEA/FLEA/Acl/Manager.php**
- `$_tableClass` 数组中的 8 个类名从 `'\FLEA\Acl_Table_*'` 改为 `\FLEA\Acl\Table\*::class`

**FLEA/FLEA/Acl/Table/UserGroups.php**
- `$manyToMany` 数组中的类名改用 `::class` 常量

**FLEA/FLEA/Acl/Table/Users.php**
- `$belongsTo` 和 `$manyToMany` 数组中的类名改用 `::class` 常量

**FLEA/FLEA/Acl/Table/Roles.php**
- `$manyToMany` 数组中的 `tableClass` 改用 `::class` 常量
- `joinTable` 保留为表名字符串（该表没有对应的实体类）

**FLEA/FLEA/Acl/Table/Permissions.php**
- 注释中的类名从 `\FLEA\Acl_Table_Permissions` 改为 `\FLEA\Acl\Table\Permissions`

**FLEA/FLEA/Acl/Table/UserGroupsHasRoles.php**
- 注释中的类名从 `\FLEA\Acl_Table_UserGroupsHasRoles` 改为 `\FLEA\Acl\Table\UserGroupsHasRoles`

**FLEA/FLEA/Acl/Table/UserGroupsHasPermissions.php**
- 注释中的类名从 `\FLEA\Acl_Table_UserGroupsHasPermissions` 改为 `\FLEA\Acl\Table\UserGroupsHasPermissions`

**FLEA/FLEA/Acl/Table/UsersHasRoles.php**
- 注释中的类名从 `\FLEA\Acl_Table_UsersHasRoles` 改为 `\FLEA\Acl\Table\UsersHasRoles`

**FLEA/FLEA/Acl/Table/UsersHasPermissions.php**
- 注释中的类名从 `\FLEA\Acl_Table_UsersHasPermissions` 改为 `\FLEA\Acl\Table\UsersHasPermissions`

**FLEA/FLEA/Acl/Exception/UserGroupNotFound.php**
- 注释中的类名从 `\FLEA\Acl_Exception_UserGroupNotFound` 改为 `\FLEA\Acl\Exception\UserGroupNotFound`

**FLEA/FLEA/Acl/Manager.php**
- 注释中的类名从 `\FLEA\Acl_Manager` 改为 `\FLEA\Acl\Manager`
- `@var` 类型注释同步更新

**FLEA/FLEA/Acl/Table/UserGroups.php**
- 注释中的类名从 `\FLEA\Acl_Table_UserGroups` 改为 `\FLEA\Acl\Table\UserGroups`

**FLEA/FLEA/Acl/Table/Users.php**
- 注释中的类名从 `\FLEA\Acl_Table_Users` 改为 `\FLEA\Acl\Table\Users`

**FLEA/FLEA/Acl/Table/Roles.php**
- 注释中的类名从 `\FLEA\Acl_Table_Roles` 改为 `\FLEA\Acl\Table\Roles`

优势:
- 符合 PSR-4 命名空间规范
- 使用 `::class` 常量提供类型安全
- IDE 可以提供更好的自动完成和重构支持
- 注释与实际的命名空间保持一致

---

## 2026-02-26

### refactor: TableLink 类使用 ::class 常量代替字符串类名

**FLEA/FLEA/Db/TableLink.php**
- `createLink()` 方法中的 `$typeMap` 数组使用 `::class` 常量

修改前:
```php
static $typeMap = [
    HAS_ONE => '\FLEA\Db\TableLink\HasOneLink',
    ...
];
```

修改后:
```php
static $typeMap = [
    HAS_ONE => \FLEA\Db\TableLink\HasOneLink::class,
    ...
];
```

优势:
- 类型安全，IDE 可以提供更好的支持
- 类名改变时 IDE 可自动重构
- 符合现代 PHP 最佳实践

---

## 2026-02-26

### refactor: 将 array() 替换为短数组语法 []

批量将代码中的 array() 替换为 PHP 5.4+ 支持的短数组语法 []

**修改的文件:**
- `Ajax.php`: 数组定义使用 [] 语法
- `Config/DEBUG_MODE_CONFIG.php`: 配置数组使用 [] 语法
- `Config/DEPLOY_MODE_CONFIG.php`: 配置数组使用 [] 语法
- `Db/ActiveRecord.php`: 数组定义和 call_user_func 参数使用 [] 语法
- `Db/Driver/AbstractDriver.php`: 返回值使用 [] 语法
- `Db/Driver/Mysql.php`: 静态数组使用 [] 语法
- `Db/SqlHelper.php`: 数组定义和返回值使用 [] 语法
- `Db/TableDataGateway.php`: 数组定义和返回值使用 [] 语法
- `Db/TableLink.php`: 数组定义使用 [] 语法
- `Db/TableLink/ManyToManyLink.php`: 数组定义使用 [] 语法
- `Dispatcher/Auth.php`: 数组定义使用 [] 语法
- `Dispatcher/Simple.php`: 数组定义和返回值使用 [] 语法
- `Helper/FileUploader/File.php`: 数组定义使用 [] 语法
- `Helper/Image.php`: 数组定义和返回值使用 [] 语法
- `Helper/ImgCode.php`: 数组定义和返回值使用 [] 语法
- `Helper/Pager.php`: 数组定义使用 [] 语法
- `Helper/Verifier.php`: 数组定义使用 [] 语法
- `Log.php`: 回调函数使用 [] 语法
- `Rbac.php`: 数组定义使用 [] 语法
- `Rbac/UsersManager.php`: 数组定义使用 [] 语法
- `Session/Db.php`: session_set_save_handler 参数使用 [] 语法
- `View/Simple.php`: 数组定义使用 [] 语法
- `WebControls.php`: extractAttribs 调用使用 [] 语法
- `_Errors/default/ErrorMessage.php`: 返回值使用 [] 语法
- `_Errors/chinese-utf8/ErrorMessage.php`: 返回值使用 [] 语法

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

- `Db/TableDataGateway.php` 第 448 行：`$assocRowset = null` 改为 `$assocRowset = []`（匹配 `array` 类型参数）
- `Db/TableDataGateway.php` 第 474、540、610 行：`assemble($sql, ...)` 改为 `assemble(sql_statement($sql), ...)`
- `Db/TableLink.php` 第 375 行：添加 null 检查，防止 `isRegistered(null)` 类型错误

### fix: 修复多处函数返回类型与实际返回值不一致

- `FLEA.php` `parseDSN()`：`return false` 改为 `return null`（匹配 `?array`）
- `AbstractDriver.php` `affectedRows()`：`return false` 改为 `return 0`（匹配 `: int`）
- `AbstractDriver.php` `startTrans()`：末尾补充 `return true`（匹配 `: bool`）
- `AbstractDriver.php` `completeTrans()`：else 分支后补充 `return true`（匹配 `: bool`）
- `AbstractDriver.php` `getPlaceholderPair()`：返回类型由 `string` 改为 `array`



- `Db/TableDataGateway.php` 第 1090 行：`Execute($sql, ...)` 改为 `execute(sql_statement($sql), ...)`，与其他调用一致



- `Db/Driver/AbstractDriver.php`：`getPlaceholder()` 返回类型由错误的 `string` 改为 `array`

### refactor(log): 移除 AbstractDriver 中 log_message 的存在性检查

- `Db/Driver/AbstractDriver.php`：移除 `function_exists('log_message')` 检查，`log_message` 始终可用



- `View/Simple.php`：`'debug'` → `LogLevel::DEBUG`
- `Db/Driver/Mysql.php`：`'debug'` → `LogLevel::DEBUG`



- `DEPLOY_MODE_CONFIG.php`：`logErrorLevel` 改为 `[LogLevel::WARNING, LogLevel::ERROR, LogLevel::CRITICAL]`（原 `exception` → `CRITICAL`，`log` 移除）
- `DEBUG_MODE_CONFIG.php`：`logErrorLevel` 改为 `[LogLevel::DEBUG, LogLevel::NOTICE, LogLevel::WARNING, LogLevel::ERROR, LogLevel::CRITICAL]`
- `Log.php`：解析逻辑从 `explode` 字符串改为 `array_flip((array)$config)`
- `Functions.php`：`log_message()` 默认级别从 `'log'` 改为 `LogLevel::DEBUG`



- `\FLEA\Log` 移除 `appendLog()` 方法
- `Functions.php` 的 `log_message()` 保留 `$title` 参数和 `print_r` 处理逻辑，改为直接调用 `$instance->log($level, $message)`



- 引入 `psr/log` 1.1.4（兼容 PHP 7.4）
- `\FLEA\Log` 继承 `Psr\Log\AbstractLogger`，实现 PSR-3 `LoggerInterface`
- 新增核心方法 `log($level, $message, array $context = [])` 支持 `{key}` 占位符插值
- 保留 `appendLog()` 向后兼容，供 `log_message()` 函数调用
- `__writeLog()` 补充返回类型声明 `void`

## 2026-02-26

### PHP 7.4 特性改进

**FLEA.php**
- `parseDSN()`：用 `??=` 替换 8 处 `isset()` 三元表达式（数组分支）
- `parseDSN()`：用 `??` 替换 5 处 `isset()` 三元表达式（parse_url 分支），`driver` 简化为 `strtolower($parse['scheme'] ?? '')`

**FLEA/Log.php**
- 属性类型声明：`string $_log`、`string $dateFormat`、`?string $_logFileDir`、`?string $_logFilename`、`bool $_enabled`、`?array $_errorLevel`
- 3 处 `list()` 解构改为 `[]` 语法

**FLEA/View/Simple.php**
- 属性类型声明：`?string $templateDir`、`int $cacheLifetime`、`bool $enableCache`、`string $cacheDir`、`array $vars`、`array $cacheState`

**FLEA/Ajax.php**
- 1 处 `list()` 解构改为 `[]` 语法

**FLEA/Helper/Pager.php**
- 属性类型声明：所有 `int` 分页属性、`?string $_sortby`、`?\FLEA\Db\Driver\AbstractDriver $dbo`
- `$source` 和 `$_conditions` 因类型复杂（联合类型/mixed）保持无类型声明

## 2026-02-25 - 配置文件现代化：移除无用配置项、使用 ::class 常量

### 修改文件
- `FLEA/FLEA/Config/DEPLOY_MODE_CONFIG.php` - 部署模式配置文件
- `FLEA/FLEA/Config/DEBUG_MODE_CONFIG.php` - 调试模式配置文件

### 移除的配置项

#### 1. MVCPackageFilename（过时的 MVC 包配置）
```php
// 删除了这个配置项
'MVCPackageFilename' => FLEA_DIR . '/Controller/Action.php'
```

**原因**：
- PSR-4 自动加载机制已经取代了手动加载 MVC 包文件
- 该配置项在代码中从未被使用
- 保留无用的配置项会误导用户

#### 2. 移除未实现的 Helper 配置项
```php
// 删除了以下不存在的 Helper 类配置：
'helper.encryption'         // 加密算法助手（类不存在）
'helper.array'              // 数组处理助手（类不存在）
'helper.yaml'               // YAML 助手（类不存在）
'helper.html'               // HTML 助手（类不存在）
```

**原因**：
- 这些配置项对应的 Helper 类文件不存在
- 保留 null 值的配置项没有实际意义
- 简化配置文件，只保留实际可用的功能

### 保留的 Helper 配置项

```php
'helper.verifier'           => '\FLEA\Helper\Verifier'  // 数据验证服务助手
'helper.file'               => '\FLEA\Helper\SendFile'  // 文件系统操作助手
'helper.image'              => '\FLEA\Helper\Image'     // 图像处理助手
'helper.pager'              => '\FLEA\Helper\Pager'     // 分页助手
'helper.uploader'           => '\FLEA\Helper\FileUploader' // 文件上传助手
```

### 类名引用现代化

#### 1. 使用 ::class 常量代替字符串

**之前（字符串格式）：**
```php
'dispatcher'                => '\FLEA\Dispatcher\Simple',
'ajaxClassName'             => '\FLEA\Ajax',
'webControlsClassName'      => '\FLEA\WebControls',
'languageSupportProvider'   => '\FLEA\Language',
'dispatcherAuthProvider'    => '\FLEA\Rbac',
'logProvider'               => '\FLEA\Log',
```

**现在（::class 常量）：**
```php
'dispatcher'                => \FLEA\Dispatcher\Simple::class,
'ajaxClassName'             => \FLEA\Ajax::class,
'webControlsClassName'      => \FLEA\WebControls::class,
'languageSupportProvider'   => \FLEA\Language::class,
'dispatcherAuthProvider'    => \FLEA\Rbac::class,
'logProvider'               => \FLEA\Log::class,
```

#### 2. Helper 配置也使用 ::class 常量

```php
'helper.verifier'           => \FLEA\Helper\Verifier::class,
'helper.file'               => \FLEA\Helper\SendFile::class,
'helper.image'              => \FLEA\Helper\Image::class,
'helper.pager'              => \FLEA\Helper\Pager::class,
'helper.uploader'           => \FLEA\Helper\FileUploader::class,
```

### 使用 ::class 常量的优势

1. **类型安全**
   - IDE 可以提供更好的自动完成和重构支持
   - 类不存在时会在编译时报错，而不是运行时报错

2. **可维护性**
   - 如果类名或命名空间改变，IDE 可以自动重构
   - 减少手动修改类名时出错的可能性

3. **一致性**
   - 与现代 PHP 开发标准一致（PHP 5.5+ 特性）
   - 符合 PHP 社区的最佳实践

4. **可读性**
   - 代码更清晰，明确表示这是一个类名
   - 便于理解代码的意图

### 对框架逻辑的影响

**结论：完全不影响框架的代码逻辑**

1. **::class 常量的值**
   - `\FLEA\Ajax::class` 返回 `'FLEA\Ajax'`（没有开头的反斜杠）
   - 这与字符串格式 `'\FLEA\Ajax'` 有细微差异

2. **PHP 的类名处理机制**
   - `class_exists($className)` 函数可以接受带或不带开头反斜杠的类名
   - `new $className()` 也可以接受带或不带开头反斜杠的类名
   - 两种格式在 PHP 中是完全等效的

3. **实际测试验证**
   - ✓ 所有配置项都能正常工作
   - ✓ 类可以正确加载
   - ✓ 实例可以正确创建
   - ✓ 框架代码无需任何修改

### 注释更新

更新了注释中的类名引用：
```php
// 之前
// {{{ FLEA_Dispatcher_Auth 和 RBAC 组件

// 现在
// {{{ \FLEA\Dispatcher\Auth 和 RBAC 组件
```

### 验证

- ✅ 两个配置文件都通过了 PHP 语法检查
- ✅ 所有类名都使用了 `::class` 常量
- ✅ 配置项现在更符合现代 PHP 最佳实践
- ✅ 框架代码无需修改，逻辑完全兼容

### 总结

此次配置文件现代化改进带来了以下好处：
1. 移除了过时和无用的配置项
2. 使用 `::class` 常量提高了代码质量
3. 配置文件更加简洁和易于维护
4. 完全不影响现有框架和应用的运行

---

## 2026-02-25 - 移除无效的 use 语句

### 修改文件
- `FLEA/Functions.php`
- `FLEA/FLEA/Db/Driver/AbstractDriver.php`
- `FLEA/FLEA/Db/Driver/Mysql.php`
- `FLEA/FLEA/Db/Driver/Sqlitepdo.php`

### 移除的 use 语句

#### 1. Functions.php
```php
// 移除了以下无效的 use 语句：
use FLEA;
use FLEA\Config;
```

**原因**：
- `Functions.php` 是一个全局函数文件（没有命名空间）
- `FLEA` 类本身就在全局命名空间中
- 在全局作用域中 `use FLEA;` 是没有意义的，会导致 PHP 警告

**警告信息**：
```
PHP Warning:  The use statement with non-compound name 'FLEA' has no effect 
in /path/to/Functions.php on line 13
```

#### 2. 数据库驱动文件中的 PDOStatement use 语句

从以下文件中移除了 `use PDOStatement;` 语句：
- `FLEA/FLEA/Db/Driver/AbstractDriver.php`
- `FLEA/FLEA/Db/Driver/Mysql.php`
- `FLEA/FLEA/Db/Driver/Sqlitepdo.php`

**原因**：
- 代码中已经使用了全限定名称 `\PDOStatement`
- 既然使用了全限定名称，就不需要 `use` 语句
- 避免了不一致性（两种方式混用）

### 使用全限定名称的规则

当在代码中使用 `\PDO`、`\PDOStatement`、`\PDOException` 时：
- 如果在代码中使用 `\` 前缀的全限定名称，**不使用** `use` 语句
- 如果在代码中使用 `use` 语句导入，则在代码中使用短名称
- **不要混用**两种方式

**一致性示例：**

✅ **正确方式 1（使用全限定名称）：**
```php
public function fetchRow(\PDOStatement $res): ?array
{
    return $res->fetch(\PDO::FETCH_NUM);
}
```

✅ **正确方式 2（使用 use 语句）：**
```php
use \PDOStatement;

public function fetchRow(PDOStatement $res): ?array
{
    return $res->fetch(\PDO::FETCH_NUM);
}
```

❌ **错误方式（混用）：**
```php
use \PDOStatement;  // 导入了但没用上

public function fetchRow(\PDOStatement $res): ?array  // 使用了全限定名称
{
    return $res->fetch(\PDO::FETCH_NUM);
}
```

### 验证

- ✅ 所有修改的文件都通过了 PHP 语法检查
- ✅ 没有 PHP 警告
- ✅ 代码更加一致和清晰

### 总结

移除无效的 use 语句后：
1. 消除了 PHP 警告
2. 代码更加一致（统一使用全限定名称）
3. 提高了代码的可读性
4. 避免了不必要的 `use` 语句

---

## 2026-02-25 - PDO/PDOStatement 全限定名称补全

### 修改文件
- `FLEA/FLEA/Db/Driver/AbstractDriver.php`
- `FLEA/FLEA/Db/Driver/Mysql.php`
- `FLEA/FLEA/Db/Driver/Mysqlt.php`
- `FLEA/FLEA/Db/Driver/Sqlitepdo.php`

### 修改内容

#### 1. AbstractDriver.php

在抽象方法签名中使用全限定名称：
```php
abstract public function fetchRow(\PDOStatement $res): ?array;
abstract public function fetchAssoc(\PDOStatement $res): ?array;
abstract public function freeRes(\PDOStatement $res): bool;
```

#### 2. Mysql.php

更新了所有 PDO 相关的引用：

**方法参数：**
```php
public function fetchRow(\PDOStatement $res): ?array
public function fetchAssoc(\PDOStatement $res): ?array
public function freeRes(\PDOStatement $res): bool
```

**PDO 常量：**
```php
\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
\PDO::ATTR_EMULATE_PREPARES => false,
```

**PDO 实例化：**
```php
$this->pdo = new \PDO($dsnString, $dsn['login'], $dsn['password'], $options);
```

**PDO fetch 调用：**
```php
$res->fetch(\PDO::FETCH_NUM);
$res->fetch(\PDO::FETCH_ASSOC);
```

**异常捕获：**
```php
catch (\PDOException $e) {
    // 处理异常
}
```

#### 3. Mysqlt.php

更新了 PDO 异常捕获：
```php
protected function _startTrans(): bool
{
    try {
        return $this->pdo->beginTransaction() !== false;
    } catch (\PDOException $e) {
        return false;
    }
}

protected function _completeTrans(bool $commitOnNoErrors = true): bool
{
    try {
        // ...
    } catch (\PDOException $e) {
        return false;
    }
}
```

#### 4. Sqlitepdo.php

更新了所有 PDO 相关引用：

**PDO 实例化：**
```php
$this->conn = new \PDO('sqlite2:' . $dsn['db']);
$this->conn = new \PDO('sqlite:' . $dsn['db']);
```

**PDO 异常：**
```php
catch (\PDOException $e)
```

**PDO fetch：**
```php
$res->fetch(\PDO::FETCH_ASSOC);
```

**方法参数：**
```php
public function fetchRow(\PDOStatement $res): ?array
public function fetchAssoc(\PDOStatement $res): ?array
public function freeRes(\PDOStatement $res): bool
```

### 修改原则

1. **全局类使用全限定名称**
   - PDO、PDOStatement、PDOException 等全局类必须使用 `\` 前缀
   - 确保命名空间的一致性和明确性

2. **use 语句与全限定名称不混用**
   - 既然在代码中使用了全限定名称，就不需要 use 语句
   - 统一使用一种方式

3. **不使用 ::class 常量**
   - 在配置文件中使用 ::class 常量
   - 在代码中直接使用全限定名称

### 验证

- ✅ 所有 4 个文件都通过了 PHP 语法检查
- ✅ 所有 PDO/PDOStatement 引用都使用了 `\` 前缀
- ✅ 没有遗漏的 PDO 实例化调用
- ✅ 没有遗漏的异常捕获

### 修改统计

- **AbstractDriver.php**: 3 处（抽象方法签名）
- **Mysql.php**: 16 处（方法参数、PDO 常量、实例化、异常、fetch）
- **Mysqlt.php**: 2 处（异常捕获）
- **Sqlitepdo.php**: 9 处（实例化、异常、方法参数、fetch）

**总计**: 30 处修改

### 总结

此次修改确保了数据库驱动代码中所有全局类引用的一致性：
1. 所有 PDO、PDOStatement、PDOException 都使用 `\` 前缀
2. 代码更加清晰和明确
3. 避免了潜在的命名冲突
4. 符合现代 PHP 最佳实践

---

## 2026-02-25 - 创建博客示例应用

### 新增文件
- `App/Config.php` - 应用配置文件
- `App/Controller/Post.php` - 文章控制器
- `App/Model/Post.php` - 文章模型
- `App/Model/Comment.php` - 评论模型
- `App/View/post/index.php` - 文章列表页
- `App/View/post/view.php` - 文章详情页
- `App/View/post/create.php` - 创建文章页
- `App/View/post/edit.php` - 编辑文章页
- `index.php` - 应用入口文件
- `blog.sql` - 数据库初始化脚本
- `BLOG_SETUP.md` - 安装和使用说明
- `README.md` - 项目说明（已更新）
- `cache/` - 缓存目录

### 数据库设置

#### 创建的表

1. **posts 表（文章表）**
   - id (主键, 自增)
   - title (文章标题)
   - content (文章内容)
   - author (作者)
   - created_at (创建时间)
   - updated_at (更新时间)
   - status (状态: 0-草稿, 1-发布)

2. **comments 表（评论表）**
   - id (主键, 自增)
   - post_id (文章ID, 外键)
   - author (评论者)
   - email (邮箱)
   - content (评论内容)
   - created_at (创建时间)
   - status (状态: 0-待审核, 1-已审核)

#### 示例数据

- **文章**: 3 篇示例文章
  - "欢迎来到我的博客"
  - "FLEA 框架介绍"
  - "PHP 最佳实践"

- **评论**: 3 条示例评论

### 应用功能

#### 文章管理
- ✅ 查看文章列表（分页显示）
- ✅ 查看文章详情
- ✅ 创建新文章
- ✅ 编辑文章
- ✅ 删除文章

#### 评论功能
- ✅ 查看文章评论
- ✅ 发表评论

#### 用户界面
- ✅ 响应式设计
- ✅ 现代化 UI
- ✅ 友好的交互体验

### 技术栈

- **框架**: FLEA (PSR-4 标准)
- **数据库**: MySQL
- **PHP**: 7.1+
- **模板**: 原生 PHP
- **CSS**: 自定义响应式样式

### 项目结构

```
fleaphp-ex/
├── App/
│   ├── Config.php          # 应用配置
│   ├── Controller/
│   │   └── Post.php        # 文章控制器
│   ├── Model/
│   │   ├── Post.php        # 文章模型
│   │   └── Comment.php     # 评论模型
│   └── View/
│       └── post/
│           ├── index.php   # 文章列表
│           ├── view.php    # 文章详情
│           ├── create.php  # 创建文章
│           └── edit.php    # 编辑文章
├── blog.sql                # 数据库脚本
├── index.php               # 入口文件
├── cache/                  # 缓存目录
├── BLOG_SETUP.md           # 安装说明
└── FLEA/                   # 框架核心
```

### 数据库配置

- **主机**: 127.0.0.1:3306
- **用户名**: root
- **密码**: 11111111
- **数据库**: blog

### 访问方式

```
首页: http://localhost/fleaphp-ex/
文章详情: ?controller=Post&action=view&id=1
创建文章: ?controller=Post&action=create
编辑文章: ?controller=Post&action=edit&id=1
删除文章: ?controller=Post&action=delete&id=1
```

### 验证

- ✅ 数据库创建成功
- ✅ 数据表创建成功
- ✅ 示例数据导入成功
- ✅ 所有文件通过语法检查
- ✅ 代码符合 PSR-4 标准

### 目的

创建一个完整的博客示例应用，用于：
1. 展示 FLEA 框架的实际使用方法
2. 提供 MVC 架构的参考实现
3. 帮助开发者快速上手框架
4. 作为学习和测试的参考项目

### 下一步

可以根据需要扩展此博客：
- 添加用户登录功能
- 添加标签分类
- 添加文章搜索
- 添加图片上传
- 添加后台管理界面

---

## 2026-02-25 - 修复 PSR-4 全限定名称使用不一致问题

### 问题描述

用户指出：框架内还用到其它的全局类了吗

### 发现的问题

经过全面检查，发现了以下问题：

#### 1. Mysqlt.php 中使用了未加 `\` 前缀的 PDOException
```php
// 第40行和第59行
catch (PDOException $e)  // ❌ 错误
```

#### 2. Mysql.php 中有多处使用了未加 `\` 前缀的 PDOException
```php
// 第111行、第149行、第189行
catch (PDOException $e)  // ❌ 错误
```

#### 3. Sqlitepdo.php 中使用了未加 `\` 前缀的 PDOException
```php
// 第950行
catch(PDOException $e)  // ❌ 错误
```

### 修复内容

#### 1. Mysqlt.php (2处修改)
```php
// 修改前
catch (PDOException $e) {

// 修改后
catch (\PDOException $e) {
```

#### 2. Mysql.php (3处修改)
```php
// 修改前
} catch (PDOException $e) {

// 修改后
} catch (\PDOException $e) {
```

#### 3. Sqlitepdo.php (1处修改)
```php
// 修改前
catch(PDOException $e)
{

// 修改后
catch(\PDOException $e)
{
```

### 检查范围

对整个框架进行了全面检查：
- ✅ 检查了所有 PHP 全局类的使用
- ✅ 验证了 PDO、PDOStatement、PDOException 的使用
- ✅ 确认了其他全局类（如 Exception）的使用
- ✅ 修复了所有不一致的地方

### 验证

- ✅ 所有修改的文件通过了 PHP 语法检查
- ✅ 框架中所有 PHP 全局类都使用了 `\` 前缀
- ✅ 代码一致性和规范性得到保证

### 总结

此次修复确保了：
1. 框架中所有 PHP 全局类都使用全限定名称
2. 消除了命名空间使用的不一致性
3. 提高了代码的可维护性
4. 符合现代 PHP 开发最佳实践

---

## 2026-02-24 - 移除旧式类加载机制，完全使用 Composer PSR-4 自动加载

（以下内容为之前的改动记录，此处省略详细内容，仅列出标题）

## 2026-02-24 - 集中管理全局函数到 Functions.php

## 2026-02-12 - 重构配置管理，消除 $GLOBALS 使用

## 2026-02-12 - 新增开发者使用手册

## 2026-02-12 - 增强用户手册内容

## 2026-02-13 - 引入 Composer 支持

## 2026-02-13 - 创建 PSR-4 迁移计划

## 2026-02-13 - PSR-4 试点实施

## 2026-02-13 - PSR-4 试点实施审查和修正

## 2026-02-13 - 数据库类 PSR-4 重构（第一批：异常类）

## 2026-02-24 - 数据库类 PSR-4 重构（完成）

## 2026-02-24 - 更新文档以反映 PSR-4 迁移

---

## 2026-02-25 - 引入 SqlStatement 类统一 SQL 处理

### 背景

在 FLEA\Db\ 的一些场景里,`$sql` 变量除了字符串类型之外也有可能是 `\PDOStatement` 对象。为了统一处理这两种情况,引入了 `\FLEA\Db\SqlStatement` 类来封装 SQL 语句或 PDOStatement 对象。

### 新增文件
- `FLEA/FLEA/Db/SqlStatement.php` - SQL 语句封装类
- `FLEA/Functions.php` - 新增 `sql_statement()` 全局函数

### SqlStatement 类设计

类提供了以下功能:
- 封装 SQL 字符串或 PDOStatement 对象
- 通过 `isResource()` 判断内部类型
- 通过 `getSql()` 获取内部对象
- 通过静态方法 `create()` 创建实例

### 修改的核心方法

#### AbstractDriver.php

修改了以下方法的参数类型为 `\FLEA\Db\SqlStatement`:
- `execute()` - 参数和返回值改为 `\FLEA\Db\SqlStatement`
- `selectLimit()` - 参数改为 `string`,返回值改为 `\FLEA\Db\SqlStatement`
- `getAll()`, `getOne()`, `getRow()`, `getCol()`, `getAllGroupBy()`, `getAllWithFieldRefs()`, `assemble()` - 参数改为 `\FLEA\Db\SqlStatement`

#### 核心逻辑优化

在 `execute()` 方法中添加了性能优化:
```php
public function execute(\FLEA\Db\SqlStatement $sql, ?array $inputarr = null, bool $throw = true): \FLEA\Db\SqlStatement
{
    // 如果已经是 PDOStatement 对象,直接返回
    if ($sql->isResource()) {
        return $sql;
    }
    // ... 执行逻辑 ...
}
```

### 兼容性处理

所有查询方法统一使用:
```php
$res = $sql->isResource() ? $sql->getSql() : $this->execute($sql)->getSql();
```

### 修改的框架文件

1. `FLEA/FLEA/Db/Driver/AbstractDriver.php` - 抽象驱动基类
2. `FLEA/FLEA/Db/Driver/Mysql.php` - MySQL 驱动实现
3. `FLEA/FLEA/Db/TableDataGateway.php` - 数据表网关
4. `FLEA/FLEA/Helper/Pager.php` - 分页助手

### 修改的辅助文件

1. `FLEA/FLEA/Db/TableLink/ManyToManyLink.php` - 多对多关联
2. `FLEA/FLEA/Db/TableLink/HasManyLink.php` - 一对多关联
3. `FLEA/FLEA/Acl/Table/UserGroups.php` - 用户组管理
4. `FLEA/FLEA/Session/Db.php` - Session 存储

### 优势

1. **统一接口** - 统一使用 SqlStatement 对象处理
2. **类型安全** - 方法参数类型明确
3. **性能优化** - 避免重复执行已执行的查询
4. **向后兼容** - 使用 sql_statement() 函数包装现有代码

### 验证

- ✅ 所有修改的文件通过了 PHP 语法检查
- ✅ SqlStatement 类功能完整
- ✅ sql_statement() 全局函数正常工作
- ✅ 框架核心方法全部适配
- ✅ 辅助类全部适配

---

## 2026-02-25 - 修复 Sqlitepdo.php 驱动错误

### 修改文件
- `FLEA/FLEA/Db/Driver/Sqlitepdo.php`

### 修复的错误

1. **failTrans() 方法逻辑错误** - 将 `$this->_transCommit = true` 改为 `false`
2. **使用数组解构** - 将 `list()` 改为 `[$length, $offset]`
3. **connect() 返回值** - 改为返回 `true`
4. **affectedRows() 方法** - 修复注释和参数

### 注

Sqlitepdo.php 驱动文件已被删除,仅保留修改记录。

---

## 2026-02-25 - Simple 视图引擎优化

### 修改文件
- `FLEA/FLEA/View/Simple.php`

### 修改内容

1. **重命名属性**
   - 将 `$path` 重命名为 `$templateDir`
   - 使变量名与配置键名保持一致

2. **简化构造函数**
   - 移除了对 `templateDir` 的特殊判断逻辑
   - 统一使用 `$this->{$key} = $viewConfig[$key]` 赋值

3. **更新模板加载**
   - 将 `$this->path` 改为 `$this->templateDir`

### 优化效果

1. 变量名与配置键名保持一致
2. 代码更简洁
3. 类型安全性更好

### 验证

- ✅ Simple.php 语法检查通过
- ✅ 配置加载逻辑正确
- ✅ 模板路径处理正确

---

