# Throw 语句语法错误修复报告

## 问题描述
在代码审查中发现多个 `throw` 语句后面存在无效的 `return` 语句。由于 `throw` 会立即终止函数执行并抛出异常，其后的任何代码（包括 `return` 语句）都永远不会被执行，这属于语法冗余和潜在的逻辑错误。

## 修复详情

### 1. FLEA/FLEA/Db/TableDataGateway.php
**位置**: 第1183行
**问题**: 
```php
throw new FLEA_Db_Exception_MissingPrimaryKey($this->primaryKey);
return false;  // 此行永远不会执行
```
**修复**: 删除了无效的 `return false;` 语句

### 2. FLEA/FLEA/Dispatcher/Simple.php
**位置**: 第134行和第140行
**问题**: 
```php
// 第134行
throw new FLEA_Exception_MissingController(...);
return false;  // 此行永远不会执行

// 第140行  
throw new FLEA_Exception_MissingAction(...);
return false;  // 此行永远不会执行
```
**修复**: 删除了两处无效的 `return false;` 语句

### 3. FLEA/FLEA/Dispatcher/Auth.php
**位置**: 第283行
**问题**: 
```php
throw new FLEA_Rbac_Exception_InvalidACTFile($actFilename, $ACT);
return false;  // 此行永远不会执行
```
**修复**: 删除了无效的 `return false;` 语句

## 技术说明
这种错误通常出现在代码重构过程中，当开发者将原来的错误返回模式改为异常抛出模式时，忘记删除原有的返回语句。虽然这些冗余代码不会影响程序运行（因为永远不会执行），但会造成代码混乱和维护困难。

## 验证结果
所有修复均已应用，相关文件的语法错误已消除，代码逻辑更加清晰。