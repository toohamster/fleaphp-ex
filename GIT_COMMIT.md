# GIT_COMMIT.md

记录每次代码改动的 git commit 说明。

---

## 2026-02-26

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
