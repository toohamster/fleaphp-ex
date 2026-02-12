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
