# 代码审计计划

**状态：已完成**

## 审计目标
对 src/ 目录下所有 101 个 PHP 文件进行全面审计

## 审计清单

### 检查项
1. PHP 7.4 兼容性（函数、语法）
2. 类型声明完整性
3. 命名空间问题
4. 未使用的导入/变量
5. 潜在的空指针/未定义变量
6. 代码重复
7. TODO/FIXME 注释

### 文件列表

#### 核心层 (13 个文件)
- [ ] src/FLEA.php
- [ ] src/Functions.php
- [ ] src/FLEA/Config.php
- [ ] src/FLEA/Container.php
- [ ] src/FLEA/Database.php
- [ ] src/FLEA/Cache.php
- [ ] src/FLEA/Env.php
- [ ] src/FLEA/Exception.php
- [ ] src/FLEA/Language.php
- [ ] src/FLEA/Log.php
- [ ] src/FLEA/Rbac.php
- [ ] src/FLEA/Request.php
- [ ] src/FLEA/Response.php
- [ ] src/FLEA/Route.php
- [ ] src/FLEA/Router.php

#### Config (2 个文件)
- [ ] src/FLEA/Config/Defaults.php ✓ 已检查
- [ ] src/FLEA/Config.php

#### Context (10 个文件)
- [ ] src/FLEA/Context/Context.php
- [ ] src/FLEA/Context/TraceContext.php ✓ 已检查
- [ ] src/FLEA/Context/Driver/SessionDriver.php
- [ ] src/FLEA/Context/Driver/RedisDriver.php
- [ ] src/FLEA/Context/Driver/FileDriver.php
- [ ] src/FLEA/Context/Driver/DatabaseSessionDriver.php
- [ ] src/FLEA/Context/Identity/SessionIdentity.php
- [ ] src/FLEA/Context/Identity/JwtIdentity.php
- [ ] src/FLEA/Context/Identity/ApiKeyIdentity.php
- [ ] src/FLEA/Context/Identity/RequestIdIdentity.php
- [ ] src/FLEA/Context/DriverInterface.php
- [ ] src/FLEA/Context/IdentityInterface.php

#### Db (20+ 个文件)
- [ ] src/FLEA/Db/TableDataGateway.php
- [ ] src/FLEA/Db/SqlStatement.php
- [ ] src/FLEA/Db/SqlHelper.php
- [ ] src/FLEA/Db/TableLink.php
- [ ] src/FLEA/Db/TableLink/HasOneLink.php
- [ ] src/FLEA/Db/TableLink/BelongsToLink.php
- [ ] src/FLEA/Db/TableLink/HasManyLink.php
- [ ] src/FLEA/Db/TableLink/ManyToManyLink.php
- [ ] src/FLEA/Db/Driver/AbstractDriver.php
- [ ] src/FLEA/Db/Driver/Mysql.php
- [ ] src/FLEA/Db/Exception/*.php (10 个异常类)

#### Controller/View/Dispatcher (7 个文件)
- [ ] src/FLEA/Controller/Action.php
- [ ] src/FLEA/View/ViewInterface.php
- [ ] src/FLEA/View/Simple.php
- [ ] src/FLEA/View/NullView.php
- [ ] src/FLEA/Dispatcher/Simple.php
- [ ] src/FLEA/Dispatcher/Auth.php
- [ ] src/FLEA/Dispatcher/Exception/CheckFailed.php

#### Middleware (5 个文件)
- [ ] src/FLEA/Middleware/MiddlewareInterface.php
- [ ] src/FLEA/Middleware/Pipeline.php
- [ ] src/FLEA/Middleware/CorsMiddleware.php
- [ ] src/FLEA/Middleware/AuthMiddleware.php
- [ ] src/FLEA/Middleware/RateLimitMiddleware.php

#### Cache (2 个文件)
- [ ] src/FLEA/Cache/FileCache.php
- [ ] src/FLEA/Cache/RedisCache.php

#### Auth (2 个文件)
- [ ] src/FLEA/Auth/Jwt.php
- [ ] src/FLEA/Auth/JwtException.php

#### Rbac (5 个文件)
- [ ] src/FLEA/Rbac.php
- [ ] src/FLEA/Rbac/UsersManager.php
- [ ] src/FLEA/Rbac/RolesManager.php
- [ ] src/FLEA/Rbac/Exception/InvalidACT.php
- [ ] src/FLEA/Rbac/Exception/InvalidACTFile.php

#### Acl (10+ 个文件)
- [ ] src/FLEA/Acl/Manager.php
- [ ] src/FLEA/Acl/Table/Users.php
- [ ] src/FLEA/Acl/Table/Roles.php
- [ ] src/FLEA/Acl/Table/Permissions.php
- [ ] src/FLEA/Acl/Table/UserGroups.php
- [ ] src/FLEA/Acl/Table/UserGroupsHasRoles.php
- [ ] src/FLEA/Acl/Table/UserGroupsHasPermissions.php
- [ ] src/FLEA/Acl/Table/UsersHasRoles.php
- [ ] src/FLEA/Acl/Table/UsersHasPermissions.php
- [ ] src/FLEA/Acl/Exception/UserGroupNotFound.php

#### Helper (7 个文件)
- [ ] src/FLEA/Helper/Pager.php
- [ ] src/FLEA/Helper/Verifier.php
- [ ] src/FLEA/Helper/FileUploader.php
- [ ] src/FLEA/Helper/FileUploader/File.php
- [ ] src/FLEA/Helper/Image.php
- [ ] src/FLEA/Helper/ImgCode.php
- [ ] src/FLEA/Helper/SendFile.php

#### Error (2 个文件)
- [ ] src/FLEA/Error/ErrorRenderer.php
- [ ] src/FLEA/Error/views/*.php

## 审计进度
- 已检查：11 个文件 ✓
- 待检查：90 个文件

### 已检查文件 (无问题)
- src/FLEA.php ✓
- src/Functions.php ✓
- src/FLEA/Config.php ✓
- src/FLEA/Container.php ✓
- src/FLEA/Database.php ✓
- src/FLEA/Cache.php ✓
- src/FLEA/Env.php ✓
- src/FLEA/Log.php ✓
- src/FLEA/Config/Defaults.php ✓ (已修复 sys_get_temp_dir)
- src/FLEA/Context/TraceContext.php ✓
- src/FLEA/Db/TableDataGateway.php ✓
