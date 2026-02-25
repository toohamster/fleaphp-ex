# FleaPHP PSR-4 命名空间重构计划

## 概述

本文档详细说明了将 FleaPHP 框架从 PSR-0 风格（下划线分隔的类名）迁移到 PSR-4 命名空间风格的计划。

## 当前状态

### PSR-0 风格示例

```
类名: FLEA_Db_TableDataGateway
文件路径: FLEA/FLEA/Db/TableDataGateway.php
类名: FLEA_Helper_Array
文件路径: FLEA/FLEA/Helper/Array.php
类名: FLEA_Controller_Action
文件路径: FLEA/FLEA/Controller/Action.php
```

### 特点

- 类名中的下划线（_）对应目录层级
- 框架提供了 `FLEA::loadClass()` 和 `FLEA::autoload()` 来处理类加载
- 传统的 SPL 自动加载不直接支持这种命名约定

## 目标状态

### PSR-4 风格示例

```
类名: FLEA\Db\TableDataGateway
文件路径: FLEA/FLEA/Db/TableDataGateway.php
类名: FLEA\Helper\Array
文件路径: FLEA/FLEA/Helper/Array.php
类名: FLEA\Controller\Action
文件路径: FLEA/FLEA/Controller/Action.php
```

### 特点

- 完全符合 PSR-4 自动加载标准
- 可以直接使用 Composer 的 PSR-4 自动加载
- 与现代 PHP 项目保持一致
- 保持文件路径不变，只修改类名和命名空间声明

## 重构策略

### 阶段 1：核心基础类（第一优先级）

#### 需要重构的类

1. **核心框架类**
   - `FLEA` → `FLEA\FLEA`
   - `FLEA_Config` → `FLEA\Config`
   - `FLEA_Exception` → `FLEA\Exception`

2. **数据库相关类**
   - `FLEA_Db_TableDataGateway` → `FLEA\Db\TableDataGateway`
   - `FLEA_Db_ActiveRecord` → `FLEA\Db\ActiveRecord`
   - `FLEA_Db_TableLink` → `FLEA\Db\TableLink`
   - `FLEA_Db_SqlHelper` → `FLEA\Db\SqlHelper`

3. **控制器类**
   - `FLEA_Controller_Action` → `FLEA\Controller\Action`
   - `FLEA_Dispatcher_Auth` → `FLEA\Dispatcher\Auth`
   - `FLEA_Dispatcher_Simple` → `FLEA\Dispatcher\Simple`

4. **权限控制类**
   - `FLEA_Rbac` → `FLEA\Rbac`
   - `FLEA_Acl` → `FLEA\Acl`

5. **助手类**
   - `FLEA_Helper_Array` → `FLEA\Helper\Array`
   - `FLEA_Helper_FileSystem` → `FLEA\Helper\FileSystem`
   - `FLEA_Helper_Verifier` → `FLEA\Helper\Verifier`
   - `FLEA_Helper_Pager` → `FLEA\Helper\Pager`

### 阶段 2：异常类（第二优先级）

#### 需要重构的异常类

1. **框架异常**
   - `FLEA_Exception_ExpectedFile` → `FLEA\Exception\ExpectedFile`
   - `FLEA_Exception_ExpectedClass` → `FLEA\Exception\ExpectedClass`
   - `FLEA_Exception_TypeMismatch` → `FLEA\Exception\TypeMismatch`
   - `FLEA_Exception_ExistsKeyName` → `FLEA\Exception\ExistsKeyName`
   - `FLEA_Exception_NotExistsKeyName` → `FLEA\Exception\NotExistsKeyName`
   - `FLEA_Exception_MissingController` → `FLEA\Exception\MissingController`
   - `FLEA_Exception_MissingAction` → `FLEA\Exception\MissingAction`
   - `FLEA_Exception_CacheDisabled` → `FLEA\Exception\CacheDisabled`

2. **数据库异常**
   - `FLEA_Db_Exception_InvalidDSN` → `FLEA\Db\Exception\InvalidDSN`
   - `FLEA_Db_Exception_SqlQuery` → `FLEA\Db\Exception\SqlQuery`
   - `FLEA_Db_Exception_MissingPrimaryKey` → `FLEA\Db\Exception\MissingPrimaryKey`
   - `FLEA_Db_Exception_MetaColumnsFailed` → `FLEA\Db\Exception\MetaColumnsFailed`

3. **调度器异常**
   - `FLEA_Dispatcher_Exception_CheckFailed` → `FLEA\Dispatcher\Exception\CheckFailed`

4. **RBAC 异常**
   - `FLEA_Rbac_Exception_InvalidACTFile` → `FLEA\Rbac\Exception\InvalidACTFile`
   - `FLEA_Rbac_Exception_InvalidACT` → `FLEA\Rbac\Exception\InvalidACT`

### 阶段 3：数据库驱动类（第三优先级）

#### 需要重构的数据库驱动类

- `FLEA_Db_Driver_Abstract` → `FLEA\Db\Driver\AbstractDriver`
- `FLEA_Db_Driver_Mysql` → `FLEA\Db\Driver\Mysql`
- `FLEA_Db_Driver_Mysqlt` → `FLEA\Db\Driver\Mysqlt`
- `FLEA_Db_Driver_Sqlitepdo` → `FLEA\Db\Driver\Sqlitepdo`

### 阶段 4：表链接类（第四优先级）

#### 需要重构的表链接类

- `FLEA_Db_TableLink_HasOneLink` → `FLEA\Db\TableLink\HasOneLink`
- `FLEA_Db_TableLink_BelongsToLink` → `FLEA\Db\TableLink\BelongsToLink`
- `FLEA_Db_TableLink_HasManyLink` → `FLEA\Db\TableLink\HasManyLink`
- `FLEA_Db_TableLink_ManyToManyLink` → `FLEA\Db\TableLink\ManyToManyLink`

### 阶段 5：ACL 相关类（第五优先级）

#### 需要重构的 ACL 类

- `FLEA_Acl_Manager` → `FLEA\Acl\Manager`
- `FLEA_Acl_Exception_UserGroupNotFound` → `FLEA\Acl\Exception\UserGroupNotFound`
- ACL Table 类（Users, Roles, Permissions 等）

### 阶段 6：其他辅助类（第六优先级）

#### 需要重构的辅助类

- `FLEA_WebControls` → `FLEA\WebControls`
- `FLEA_Ajax` → `FLEA\Ajax`
- `FLEA_Log` → `FLEA\Log`
- `FLEA_Language` → `FLEA\Language`
- `FLEA_Helper_Image` → `FLEA\Helper\Image`
- `FLEA_Helper_Html` → `FLEA\Helper\Html`
- `FLEA_Helper_FileUploader` → `FLEA\Helper\FileUploader`
- `FLEA_Helper_ImgCode` → `FLEA\Helper\ImgCode`
- `FLEA_Helper_SendFile` → `FLEA\Helper\SendFile`
- `FLEA_Helper_Yaml` → `FLEA\Helper\Yaml`
- `FLEA_View_Simple` → `FLEA\View\Simple`
- `FLEA_Session_Db` → `FLEA\Session\Db`

## 重构步骤

### 步骤 1：创建别名映射表

创建一个全局的别名映射表，用于向后兼容：

```php
// FLEA/FLEA/Aliases.php
class FLEA_Aliases
{
    const MAPPING = [
        'FLEA_Db_TableDataGateway' => 'FLEA\Db\TableDataGateway',
        'FLEA_Helper_Array' => 'FLEA\Helper\Array',
        'FLEA_Controller_Action' => 'FLEA\Controller\Action',
        // ... 所有类映射
    ];
}
```

### 步骤 2：修改核心类文件

以 `FLEA_Db_TableDataGateway` 为例：

```php
<?php
// 旧代码（PSR-0）
class FLEA_Db_TableDataGateway
{
    // ...
}

// 新代码（PSR-4）
namespace FLEA\Db;

class TableDataGateway
{
    // ...
}
```

### 步骤 3：更新类引用

更新所有文件中对类的引用：

```php
// 旧代码
class Table_Users extends FLEA_Db_TableDataGateway
{
}

// 新代码
namespace App\Table;

use FLEA\Db\TableDataGateway;

class Users extends TableDataGateway
{
}
```

### 步骤 4：向后兼容支持

在 `FLEA/FLEA.php` 中添加类别名，支持旧的类名：

```php
<?php
// FLEA/FLEA.php
namespace FLEA;

use FLEA\Db\TableDataGateway as FLEA_Db_TableDataGateway;
use FLEA\Helper\Array as FLEA_Helper_Array;
use FLEA\Controller\Action as FLEA_Controller_Action;
// ... 其他别名
```

### 步骤 5：更新自动加载器

修改 `FLEA::autoload()` 方法，支持旧的类名：

```php
public static function autoload(string $className): bool
{
    // 首先尝试 PSR-4 加载
    if (class_exists($className, false)) {
        return true;
    }

    // 尝试旧的 PSR-0 命名
    if (strpos($className, 'FLEA_') === 0) {
        // 将旧的类名转换为新的命名空间
        $newClassName = self::convertToNamespace($className);
        return class_exists($newClassName, false);
    }

    // 使用内部的 loadClass 方法来加载类
    return self::loadClass($className, true);
}

private static function convertToNamespace(string $oldClassName): string
{
    // FLEA_Db_TableDataGateway -> FLEA\Db\TableDataGateway
    return str_replace('_', '\\', $oldClassName);
}
```

## 向后兼容性

### 选项 1：类别名（推荐）

使用 `class_alias()` 创建别名：

```php
// 在每个新类文件底部添加
class_alias('FLEA\Db\TableDataGateway', 'FLEA_Db_TableDataGateway');
class_alias('FLEA\Helper\Array', 'FLEA_Helper_Array');
// ...
```

### 选项 2：自定义自动加载器

维护一个旧的类名到新命名空间的映射表，在自动加载时处理。

### 选项 3：过渡期支持

在过渡期内，同时支持两种命名方式，在文档中明确标记旧命名方式已废弃。

## 测试策略

### 1. 单元测试

为每个重构的类编写单元测试：

```php
<?php
// tests/Db/TableDataGatewayTest.php
namespace FLEA\Tests\Db;

use FLEA\Db\TableDataGateway;

class TableDataGatewayTest extends \PHPUnit\Framework\TestCase
{
    public function testFind()
    {
        // 测试代码
    }
}
```

### 2. 集成测试

测试重构后的类在完整应用中的运行情况。

### 3. 向后兼容测试

测试旧的类名是否仍然可用：

```php
<?php
// 测试旧类名
$table = new FLEA_Db_TableDataGateway();
$this->assertInstanceOf('FLEA_Db_TableDataGateway', $table);
```

## 更新文档

### 1. 更新 USER_GUIDE.md

将所有示例代码更新为使用新的命名空间：

```php
// 旧示例
$userTable = FLEA::getSingleton('Table_Users');

// 新示例
use FLEA\Db\TableDataGateway;

$userTable = new TableDataGateway();
// 或者继续使用 FLEA::getSingleton()
```

### 2. 创建迁移指南

创建 `MIGRATION_GUIDE.md`，指导开发者如何迁移代码：

```markdown
# FleaPHP PSR-4 迁移指南

## 迁移步骤

1. 更新类引用
2. 更新 use 语句
3. 测试应用
```

## 时间表

### 第一周

- 完成阶段 1：核心基础类重构
- 创建别名映射表
- 更新核心类文件

### 第二周

- 完成阶段 2：异常类重构
- 完成阶段 3：数据库驱动类重构
- 编写单元测试

### 第三周

- 完成阶段 4：表链接类重构
- 完成阶段 5：ACL 相关类重构
- 完成阶段 6：其他辅助类重构

### 第四周

- 向后兼容性实现
- 集成测试
- 文档更新
- 发布候选版本

## 风险和缓解

### 风险 1：破坏现有代码

**缓解**：提供向后兼容的别名和过渡期支持。

### 风险 2：配置文件需要更新

**缓解**：在文档中提供清晰的迁移指南和示例。

### 风险 3：第三方库依赖

**缓解**：与第三方库维护者沟通，提供兼容性方案。

## 成功标准

1. 所有类使用 PSR-4 命名空间
2. 所有类通过 Composer 自动加载
3. 向后兼容性得到保障
4. 文档完全更新
5. 所有测试通过
6. 性能无明显下降

## 附录：类名转换表

### 核心类

| 旧类名 | 新命名空间和类名 |
|--------|----------------|
| FLEA | FLEA\FLEA |
| FLEA_Config | FLEA\Config |
| FLEA_Exception | FLEA\Exception |
| FLEA_Rbac | FLEA\Rbac |
| FLEA_Acl | FLEA\Acl |

### 数据库类

| 旧类名 | 新命名空间和类名 |
|--------|----------------|
| FLEA_Db_TableDataGateway | FLEA\Db\TableDataGateway |
| FLEA_Db_ActiveRecord | FLEA\Db\ActiveRecord |
| FLEA_Db_TableLink | FLEA\Db\TableLink |
| FLEA_Db_SqlHelper | FLEA\Db\SqlHelper |

### 控制器类

| 旧类名 | 新命名空间和类名 |
|--------|----------------|
| FLEA_Controller_Action | FLEA\Controller\Action |
| FLEA_Dispatcher_Auth | FLEA\Dispatcher\Auth |
| FLEA_Dispatcher_Simple | FLEA\Dispatcher\Simple |

### 助手类

| 旧类名 | 新命名空间和类名 |
|--------|----------------|
| FLEA_Helper_Array | FLEA\Helper\Array |
| FLEA_Helper_FileSystem | FLEA\Helper\FileSystem |
| FLEA_Helper_Verifier | FLEA\Helper\Verifier |
| FLEA_Helper_Pager | FLEA\Helper\Pager |
| FLEA_Helper_Image | FLEA\Helper\Image |
| FLEA_Helper_Html | FLEA\Helper\Html |
| FLEA_Helper_FileUploader | FLEA\Helper\FileUploader |

### 异常类

| 旧类名 | 新命名空间和类名 |
|--------|----------------|
| FLEA_Exception_ExpectedFile | FLEA\Exception\ExpectedFile |
| FLEA_Exception_ExpectedClass | FLEA\Exception\ExpectedClass |
| FLEA_Exception_TypeMismatch | FLEA\Exception\TypeMismatch |
| FLEA_Exception_ExistsKeyName | FLEA\Exception\ExistsKeyName |
| FLEA_Exception_NotExistsKeyName | FLEA\Exception\NotExistsKeyName |

（完整列表见各阶段详细说明）

---

*本文档创建于 2026-02-13*
