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
