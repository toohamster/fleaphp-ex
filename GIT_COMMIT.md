# GIT_COMMIT.md

记录每次代码改动的 git commit 说明。

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
