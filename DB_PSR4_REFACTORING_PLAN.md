# 数据库类 PSR-4 重构计划

## 概述

本文档详细说明了将数据库相关类从 PSR-0 迁移到 PSR-4 命名空间的计划和策略。

## 要重构的类

### 1. 核心数据库类

| 旧类名 | 新命名空间和类名 | 文件位置 |
|--------|------------------|----------|
| `FLEA_Db_TableDataGateway` | `FLEA\Db\TableDataGateway` | FLEA/FLEA/Db/TableDataGateway.php |
| `FLEA_Db_ActiveRecord` | `FLEA\Db\ActiveRecord` | FLEA/FLEA/Db/ActiveRecord.php |
| `FLEA_Db_SqlHelper` | `FLEA\Db\SqlHelper` | FLEA/FLEA/Db/SqlHelper.php |
| `FLEA_Db_TableLink` | `FLEA\Db\TableLink` | FLEA/FLEA/Db/TableLink.php |

### 2. 数据库驱动类

| 旧类名 | 新命名空间和类名 | 文件位置 |
|--------|------------------|----------|
| `FLEA_Db_Driver_Abstract` | `FLEA\Db\Driver\AbstractDriver` | FLEA/FLEA/Db/Driver/AbstractDriver.php |
| `FLEA_Db_Driver_Mysql` | `FLEA\Db\Driver\Mysql` | FLEA/FLEA/Db/Driver/Mysql.php |
| `FLEA_Db_Driver_Mysqlt` | `FLEA\Db\Driver\Mysqlt` | FLEA/FLEA/Db/Driver/Mysqlt.php |
| `FLEA_Db_Driver_Sqlitepdo` | `FLEA\Db\Driver\Sqlitepdo` | FLEA/FLEA/Db/Driver/Sqlitepdo.php |

### 3. 表链接类

| 旧类名 | 新命名空间和类名 | 文件位置 |
|--------|------------------|----------|
| `FLEA_Db_TableLink_HasOneLink` | `FLEA\Db\TableLink\HasOneLink` | FLEA/FLEA/Db/TableLink/HasOneLink.php |
| `FLEA_Db_TableLink_BelongsToLink` | `FLEA\Db\TableLink\BelongsToLink` | FLEA/FLEA/Db/TableLink/BelongsToLink.php |
| `FLEA_Db_TableLink_HasManyLink` | `FLEA\Db\TableLink\HasManyLink` | FLEA/FLEA/Db/TableLink/HasManyLink.php |
| `FLEA_Db_TableLink_ManyToManyLink` | `FLEA\Db\TableLink\ManyToManyLink` | FLEA/FLEA/Db/TableLink/ManyToManyLink.php |

### 4. 数据库异常类

| 旧类名 | 新命名空间和类名 | 文件位置 |
|--------|------------------|----------|
| `FLEA_Db_Exception_InvalidDSN` | `FLEA\Db\Exception\InvalidDSN` | FLEA/FLEA/Db/Exception/InvalidDSN.php |
| `FLEA_Db_Exception_SqlQuery` | `FLEA\Db\Exception\SqlQuery` | FLEA/FLEA/Db/Exception/SqlQuery.php |
| `FLEA_Db_Exception_MissingPrimaryKey` | `FLEA\Db\Exception\MissingPrimaryKey` | FLEA/FLEA/Db/Exception/MissingPrimaryKey.php |
| `FLEA_Db_Exception_MetaColumnsFailed` | `FLEA\Db\Exception\MetaColumnsFailed` | FLEA/FLEA/Db/Exception/MetaColumnsFailed.php |
| `FLEA_Db_Exception_MissingLinkOption` | `FLEA\Db\Exception\MissingLinkOption` | FLEA/FLEA/Db/Exception/MissingLinkOption.php |
| `FLEA_Db_Exception_MissingLink` | `FLEA\Db\Exception\MissingLink` | FLEA/FLEA/Db/Exception/MissingLink.php |
| `FLEA_Db_Exception_InvalidLinkType` | `FLEA\Db\Exception\InvalidLinkType` | FLEA/FLEA/Db/Exception/InvalidLinkType.php |
| `FLEA_Db_Exception_InvalidInsertID` | `FLEA\Db\Exception\InvalidInsertID` | FLEA/FLEA/Db/Exception/InvalidInsertID.php |

## 重构步骤

### 步骤 1：更新 composer.json

确保 composer.json 中包含所有数据库类的 PSR-4 映射：

```json
{
    "autoload": {
        "psr-4": {
            "FLEA\\": "FLEA/FLEA/",
            "FLEA\\Db\\": "FLEA/FLEA/Db/",
            "FLEA\\Db\\Driver\\": "FLEA/FLEA/Db/Driver/",
            "FLEA\\Db\\TableLink\\": "FLEA/FLEA/Db/TableLink/",
            "FLEA\\Db\\Exception\\": "FLEA/FLEA/Db/Exception/"
        }
    }
}
```

### 步骤 2：重构核心数据库类

**FLEA_Db_TableDataGateway**
- 添加 `namespace FLEA\Db;`
- 更新异常引用：`FLEA_Exception_*` → `\FLEA\Exception\*`
- 更新配置引用：`FLEA_Config::getInstance()` → `\FLEA\Config::getInstance()`
- 更新类内引用：`FLEA_Db_TableLink_*` → `\FLEA\Db\TableLink\*`

### 步骤 3：重构数据库驱动类

- 所有驱动类添加 `namespace FLEA\Db\Driver;`
- 更新父类引用：`FLEA_Db_Driver_Abstract` → `\FLEA\Db\Driver\AbstractDriver`
- 更新异常引用

### 步骤 4：重构表链接类

- 所有表链接类添加 `namespace FLEA\Db\TableLink;`
- 更新父类引用：`FLEA_Db_TableLink` → `\FLEA\Db\TableLink`
- 更新异常引用

### 步骤 5：重构数据库异常类

- 所有异常类添加 `namespace FLEA\Db\Exception;`
- 更新父类引用：`FLEA_Exception` → `\FLEA\Exception`

### 步骤 6：更新 FLEA.php 中的引用

使用 sed 或批量替换更新所有 `FLEA_Db_*` 类引用：

```bash
# 示例替换命令
sed -i '' 's/FLEA_Db_TableDataGateway/\\FLEA\\Db\\TableDataGateway/g' FLEA/FLEA.php
sed -i '' 's/FLEA_Db_ActiveRecord/\\FLEA\\Db\\ActiveRecord/g' FLEA/FLEA.php
sed -i '' 's/FLEA_Db_Driver_Abstract/\\FLEA\\Db\\Driver\\Abstract/g' FLEA/FLEA.php
sed -i '' 's/new FLEA_Db_Driver_/new \\FLEA\\Db\\Driver\\/g' FLEA/FLEA.php
```

### 步骤 7：更新文档

- 更新 USER_GUIDE.md 中的数据库示例代码
- 更新 PSR4_MIGRATION_PLAN.md，标记数据库类为完成

## 重构规则

### 1. 命名空间规则

- **文件路径**：保持不变，例如 `FLEA/FLEA/Db/TableDataGateway.php`
- **命名空间声明**：`namespace FLEA\Db;`
- **类名**：`TableDataGateway`
- **完全限定类名**：`\FLEA\Db\TableDataGateway`

### 2. 类引用更新规则

**内部引用**（同一命名空间内）：
```php
// 旧代码
new FLEA_Db_TableLink_HasOneLink();

// 新代码
new \FLEA\Db\TableLink\HasOneLink();
// 或者
use FLEA\Db\TableLink\HasOneLink;
new HasOneLink();
```

**外部引用**（从其他命名空间）：
```php
// 旧代码
new FLEA_Db_TableDataGateway();

// 新代码
use FLEA\Db\TableDataGateway;
new TableDataGateway();
```

### 3. 异常类引用规则

```php
// 旧代码
throw new FLEA_Db_Exception_InvalidDSN($dsn);

// 新代码
throw new \FLEA\Db\Exception\InvalidDSN($dsn);
// 或者
use FLEA\Db\Exception\InvalidDSN;
throw new InvalidDSN($dsn);
```

### 4. 配置类引用规则

```php
// 旧代码
FLEA_Config::getInstance();

// 新代码
use FLEA\Config;
Config::getInstance();
```

## 注意事项

1. **不要使用 class_alias**
   - 根据用户要求，不向后兼容
   - 所有代码必须使用新的命名空间

2. **FLEA.php 引用更新**
   - FLEA.php 文件是框架入口，包含大量类引用
   - 需要更新所有数据库相关引用

3. **测试优先**
   - 每重构一个类，立即测试
   - 确保新命名空间可以正常工作

4. **性能考虑**
   - 避免在热路径中添加过多的 `use` 语句
   - 优先使用完全限定名

## 时间表

| 阶段 | 任务 | 预计时间 |
|------|------|---------|
| 阶段 1 | 更新 composer.json | 10 分钟 |
| 阶段 2 | 重构核心数据库类 | 1 小时 |
| 阶段 3 | 重构数据库驱动类 | 1 小时 |
| 阶段 4 | 重构表链接类 | 1 小时 |
| 阶段 5 | 重构数据库异常类 | 1 小时 |
| 阶段 6 | 更新 FLEA.php 引用 | 1 小时 |
| 阶段 7 | 测试和文档更新 | 1 小时 |

**总计**：约 5-6 小时

## 测试策略

### 1. 单元测试

为每个重构的类创建单元测试：

```php
<?php
namespace FLEA\Tests\Db;

use FLEA\Db\TableDataGateway;

class TableDataGatewayTest extends \PHPUnit\Framework\TestCase
{
    public function testNamespace()
    {
        $this->assertEquals('FLEA\Db\TableDataGateway', TableDataGateway::class);
    }

    public function testInstantiation()
    {
        $table = new TableDataGateway();
        $this->assertInstanceOf(TableDataGateway::class, $table);
    }
}
```

### 2. 集成测试

测试重构后的类在应用中的集成情况。

## 下一步

开始实施阶段 1：更新 composer.json

---

*文档创建于 2026-02-13*
