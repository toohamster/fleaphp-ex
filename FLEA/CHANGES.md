# FLEA Framework Changes

## 2026-02-26

### fix: 修复 TableDataGateway::insert() 中 execute() 调用错误

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
