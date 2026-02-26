# GIT_COMMIT.md

记录每次代码改动的 git commit 说明。

---

## 2026-02-26

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
