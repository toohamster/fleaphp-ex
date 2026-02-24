# 代码修改记录

本文档记录对 FleaPHP 框架的所有修改。

---

## 2026-02-12 - 重构配置管理，消除 $GLOBALS 使用

### 修改文件
- `FLEA/FLEA.php`
- `FLEA/FLEA/Config.php`

### 修改目的
重构配置管理系统，确保所有配置操作由 `FLEA_Config` 类提供，`FLEA` 类仅作为调用者，完全消除 `FLEA` 类中使用 `$GLOBALS` 处理配置。

### 详细修改内容

#### FLEA/FLEA.php

1. **删除 G_FLEA_VAR 常量**
   - 删除了 `define('G_FLEA_VAR', '__FLEA_CORE__');`
   - 该常量不再需要，因为不再使用 `$GLOBALS[G_FLEA_VAR]`

2. **重构配置方法，全部委托给 FLEA_Config：**
   - `loadAppInf($flea_internal_config)` - 现在调用 `FLEA_Config::mergeAppInf($flea_internal_config)`
   - `getAppInf($option, $default)` - 现在调用 `FLEA_Config::getAppInf($option, $default)`
   - `setAppInf($option, $data)` - 现在调用 `FLEA_Config::setAppInf($option, $data)`
   - `getAppInfValue($option, $keyname, $default)` - 现在调用 `FLEA_Config::getAppInfValue($option, $keyname, $default)`
   - `setAppInfValue($option, $keyname, $value)` - 现在调用 `FLEA_Config::setAppInfValue($option, $keyname, $value)`

3. **重构注册表方法，全部委托给 FLEA_Config：**
   - `register($obj, $name)` - 现在调用 `FLEA_Config::registerObject($obj, $name)`
   - `registry($name)` - 现在调用 `FLEA_Config::getRegistry($name)`
   - `isRegistered($name)` - 现在调用 `FLEA_Config::isRegistered($name)`

4. **重构数据库方法：**
   - `getDBO($dsn)` - 现在使用：
     - `FLEA_Config::hasDbo($dsnid)` 检查 DBO 是否存在
     - `FLEA_Config::getDbo($dsnid)` 获取 DBO
     - `FLEA_Config::registerDbo($dbo, $dsnid)` 注册 DBO
   - 删除了所有 `$GLOBALS[G_FLEA_VAR]['DBO']` 的引用

5. **重构类路径方法：**
   - `import($dir)` - 现在调用 `FLEA_Config::addClassPath($dir)`
   - `getFilePath($filename, $return)` - 现在使用 `FLEA_Config::getClassPath()` 替代 `$GLOBALS[G_FLEA_VAR]['CLASS_PATH']`
   - 删除了所有 `$GLOBALS[G_FLEA_VAR]['CLASS_PATH']` 的引用

6. **重构异常处理函数：**
   - `__TRY()` - 现在使用 `FLEA_Config` 管理异常堆栈
   - `__CATCH()` - 现在使用 `FLEA_Config` 管理异常堆栈
   - `__CANCEL_TRY()` - 现在使用 `FLEA_Config` 管理异常堆栈
   - 删除了所有 `$GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK']` 的引用

#### FLEA/FLEA/Config.php

1. **更新 `registerObject()` 方法：**
   - 修改异常抛出参数，使用 `$name` 而不是硬编码的字符串

2. **更新 `getRegistry()` 方法：**
   - 当对象不存在时，抛出 `FLEA_Exception_NotExistsKeyName` 异常
   - 与原始 `FLEA::registry()` 行为保持一致

### 影响范围
- 所有配置相关的方法现在都通过 `FLEA_Config` 单例进行访问
- `FLEA` 类不再直接访问 `$GLOBALS` 数组
- 保持了与旧版本 API 的兼容性，所有公共方法签名不变

### 优势
- 更好的封装性 - 配置数据集中管理
- 更易于测试 - 不依赖全局变量
- 更符合现代 PHP 编程规范 - 使用面向对象的方式管理配置
- 更清晰的职责划分 - `FLEA` 类作为门面，`FLEA_Config` 负责实际的配置管理

---

## 2026-02-12 - 新增开发者使用手册

### 新增文件
- `USER_GUIDE.md`

### 文档内容
创建完整的 FleaPHP 开发者使用手册，包含以下章节：

1. **简介** - 框架特性、系统要求
2. **快速开始** - 安装、配置、初始化
3. **核心概念** - 配置管理、对象容器、类搜索路径、连接池
4. **配置管理** - 获取/设置配置项、加载配置文件、数组配置操作
5. **类加载与自动加载** - 自动加载机制、手动加载类/文件、搜索路径
6. **对象注册与单例模式** - 注册对象、获取对象、单例获取
7. **数据库操作** - 获取连接、DSN 格式、连接池
8. **MVC 模式** - 运行应用、控制器、URL 路由
9. **缓存管理** - 写入/读取/删除缓存、缓存配置
10. **异常处理** - 框架异常、异常处理器、异常捕获点
11. **助手函数** - 加载助手、初始化 WebControls/Ajax
12. **URL 生成** - 生成 URL、URL 模式、URL 选项、回调
13. **最佳实践** - 配置管理、类组织、对象管理、数据库、缓存等
14. **常见问题** - FAQ
15. **附录** - 配置项参考、内置助手、相关资源

### 目的
为开发者提供全面的使用指南，帮助快速上手并充分利用 FleaPHP 框架的功能。

---

## 2026-02-12 - 增强用户手册内容

### 修改文件
- `USER_GUIDE.md`

### 新增内容

#### 1. 新增 "TableDataGateway - 表数据入口" 章节

详细介绍了 `FLEA_Db_TableDataGateway` 类的使用方法，包括：

- **定义数据表入口类**：如何继承 `FLEA_Db_TableDataGateway` 创建数据访问类
- **表关系定义**：完整介绍了四种表关系类型
  - 一对一关系（HAS_ONE）
  - 一对多关系（HAS_MANY）
  - 从属关系（BELONGS_TO）
  - 多对多关系（MANY_TO_MANY）
- **查询数据**：
  - 查找单条记录（find）
  - 查找多条记录（findAll）
  - 根据字段查找（findByField / findAllByField）
  - 根据多个主键查找（findAllByPkvs）
  - 使用 SQL 查询（findBySql）
- **条件表达式**：详细说明各种查询条件的使用方法
  - 简单条件
  - OR 条件
  - IN 条件
  - LIKE 条件
  - 比较条件
  - 复杂条件
- **创建记录**：
  - 创建单条记录（create）
  - 创建多条记录（createRowset）
  - 不处理关联创建
- **更新记录**：
  - 根据主键更新（update）
  - 根据条件更新（updateByConditions）
  - 更新单个字段（updateField）
  - 更新多条记录（updateRowset）
- **删除记录**：
  - 根据主键删除（remove）
  - 根据条件删除（removeByConditions）
  - 根据多个主键删除（removeByPkvs）
  - 删除所有记录（removeAll / removeAllWithLinks）
  - 删除时处理关联
- **保存记录**：
  - 智能保存（save）- 自动判断创建或更新
  - 保存多条记录（saveRowset）
- **关联操作**：
  - 启用/禁用关联
  - 动态创建/删除关联
- **数据验证**：
  - 启用自动验证
  - 定义验证规则
  - 获取验证错误
- **自动填充时间字段**：CREATED、UPDATED 等字段的自动填充

#### 2. 新增 "RBAC 权限控制" 章节

完整介绍了 FleaPHP 的 RBAC（基于角色的访问控制）功能，包括：

- **RBAC 常量**：预定义的 RBAC 相关常量
  - RBAC_EVERYONE
  - RBAC_HAS_ROLE
  - RBAC_NO_ROLE
  - RBAC_NULL
  - ACTION_ALL
- **初始化 RBAC**：如何创建和使用 RBAC 实例
- **配置 RBAC**：RBAC Session 键名等配置项
- **用户管理**：
  - 设置用户信息（setUser）
  - 获取用户信息（getUser）
  - 获取用户角色（getRoles / getRolesArray）
  - 清除用户信息（clearUser）
- **权限检查**：
  - 访问控制表（ACT）的定义和格式
  - 权限检查方法（check）
  - 准备 ACT（prepareACT）
- **权限检查示例**：提供了多个实用的示例
  - 简单角色检查
  - 多角色支持
  - 拒绝特定角色
  - 必须具有角色
  - 必须没有角色
- **在控制器中使用 RBAC**：
  - 登录时设置用户和角色
  - 在控制器中检查权限
  - 使用 RBAC 中间件
- **RBAC 最佳实践**：
  - 集中管理 ACT
  - 角色命名规范
  - 权限继承
  - 日志记录
  - 最小权限原则

### 目的
补充用户手册中缺失的重要内容，特别是：
1. 数据库操作的详细说明，特别是 `FLEA_Db_TableDataGateway` 类的完整使用指南
2. 表关系的定义和使用方法，包括一对一、一对多、从属、多对多关系
3. RBAC 权限控制系统的完整使用文档，包括用户管理、角色管理、权限检查等

这些内容对于开发者充分利用 FleaPHP 框架的功能至关重要。

---

## 2026-02-13 - 引入 Composer 支持

### 新增文件
- `composer.json`

### 修改文件
- `.gitignore`

### 修改内容

#### 1. 新增 composer.json

创建了标准的 `composer.json` 文件，包含以下配置：

- **基本信息**：
  - 包名：`fleaphp/fleaphp`
  - 描述：轻量级 PHP 框架，支持 MVC 架构、数据库抽象层和 RBAC
  - 类型：library
  - 许可证：LGPL-2.1-or-later
  - PHP 版本要求：>= 7.0

- **自动加载配置**：
  - PSR-4 命名空间：`FLEA\` 映射到 `FLEA/FLEA/` 目录
  - 文件自动加载：包含 `FLEA/FLEA.php` 以确保框架初始化
  - 开发环境自动加载：`FLEA\Tests\` 映射到 `tests/` 目录

#### 2. 更新 .gitignore

在 `.gitignore` 文件中添加了 Composer 相关的忽略规则：

- `vendor/` - Composer 依赖包目录
- `composer.lock` - Composer 锁定文件
- `composer.phar` - Composer PHAR 文件

### 目的

为 FleaPHP 框架引入 Composer 支持，带来以下优势：

1. **依赖管理**：可以通过 Composer 管理框架依赖和第三方库
2. **自动加载**：利用 Composer 的 PSR-4 自动加载机制，简化类文件加载
3. **标准化**：遵循 PHP 社区的标准依赖管理方式
4. **易于集成**：更容易集成到现有的 Composer 项目中
5. **版本控制**：通过 Composer 管理框架和依赖的版本

### 使用方法

#### 安装依赖

```bash
composer install
```

#### 更新依赖

```bash
composer update
```

#### 在项目中使用

在项目的 `composer.json` 中添加：

```json
{
    "require": {
        "fleaphp/fleaphp": "^1.0"
    }
}
```

然后运行：

```bash
composer install
```

在项目中引入 Composer 自动加载：

```php
<?php
require 'vendor/autoload.php';

// FleaPHP 已经通过 composer.json 的 files 配置自动加载
// 可以直接使用框架功能
FLEA::loadAppInf('config.php');
FLEA::runMVC();
```

### 注意事项

- `FLEA/FLEA.php` 已经配置为在自动加载时加载，无需手动 require
- 框架的类名遵循 PSR-4 标准，类名中的下划线会被转换为命名空间
- 开发者可以使用传统的类加载方式，也可以完全使用 Composer 自动加载

---
