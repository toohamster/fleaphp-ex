# FLEA Framework Changes

## 2026-02-26

### refactor(log): 移除 appendLog，log_message() 直接调用 log()

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
