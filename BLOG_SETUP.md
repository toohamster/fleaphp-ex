# FLEA 博客小程序 - 安装完成

基于 FLEA 框架开发的博客小程序已成功创建！

## 项目结构

```
fleaphp-ex/
├── App/                    # 应用目录
│   ├── Config.php          # 应用配置文件
│   ├── Controller/
│   │   └── Post.php        # 文章控制器
│   ├── Model/
│   │   ├── Post.php        # 文章模型
│   │   └── Comment.php     # 评论模型
│   └── View/
│       └── post/
│           ├── index.php   # 文章列表页
│           ├── view.php    # 文章详情页
│           ├── create.php  # 创建文章页
│           └── edit.php    # 编辑文章页
├── blog.sql                # 数据库数据库初始化脚本
├── index.php               # 应用入口文件
├── cache/                  # 缓存目录
└── FLEA/                   # FLEA 框架核心
```

## 已完成的设置

### 1. 数据库设置
✅ 数据库 `blog` 已创建
✅ 数据表 `posts` 和 `comments` 已创建
✅ 示例数据已插入（3篇文章，3条评论）

### 2. 配置文件
✅ 数据库连接配置已完成
- 主机: 127.0.0.1:3306
- 用户名: root
- 密码: 11111111
- 数据库: blog

✅ 应用配置已设置
- 默认控制器: Post
- 默认动作: index
- 视图引擎: PHP
- 错误显示: 开启

### 3. 代码文件
✅ Model 层 (Post.php, Comment.php)
✅ Controller 层 (Post.php)
✅ View 层 (4个视图文件)
✅ 入口文件 (index.php)

## 功能特性

### 文章管理
- ✅ 查看文章列表（分页）
- ✅ 查看文章详情
- ✅ 创建新文章
- ✅ 编辑文章
- ✅ 删除文章

### 评论功能
- ✅ 查看文章评论
- ✅ 发表评论

### 用户界面
- ✅ 响应式设计
- ✅ 美观的界面
- ✅ 友好的交互

## 如何使用

### 访问博客首页
在浏览器中访问：
```
http://localhost/fleaphp-ex/
或
http://localhost/fleaphp-ex/index.php?controller=Post&action=index
```

### URL 访问模式

1. **文章列表**: `?controller=Post&action=index`
2. **文章详情**: `?controller=Post&action=view&id=1`
3. **创建文章**: `?controller=Post&action=create`
4. **编辑文章**: `?controller=Post&action=edit&id=1`
5. **删除文章**: `?controller=Post&action=delete&id=1`
6. **发表评论**: `?controller=Post&action=comment` (POST)

## 数据库信息

### posts 表 (文章表)
```sql
字段名        类型          说明
id          INT          主键
title       VARCHAR(255) 文章标题
content     TEXT         文章内容
author      VARCHAR(100) 作者
created_at  DATETIME     创建时间
updated_at  DATETIME     更新时间
status      TINYINT      状态 (0-草稿, 1-发布)
```

### comments 表 (评论表)
```sql
字段名        类型          说明
id          INT          主键
post_id     INT          文章ID (外键)
author      VARCHAR(100) 评论者
email       VARCHAR(255) 邮箱
content     TEXT         评论内容
created_at  DATETIME     创建时间
status      TINYINT      状态 (0-待审核, 1-已审核)
```

## 示例数据

数据库中已包含以下示例数据：

### 文章
1. "欢迎来到我的博客" - 管理员
2. "FLEA 框架介绍" - 管理员
3. "PHP 最佳实践" - 技术专家

### 评论
1. 访客1 对文章1的评论
2. 访客2 对文章1的评论
3. 开发者 对文章2的评论

## 技术栈

- **后端框架**: FLEA (PSR-4 标准)
- **数据库**: MySQL
- **PHP版本**: 7.1+
- **模板引擎**: 原生 PHP
- **CSS**: 自定义响应式样式

## 开发说明

### 添加新的控制器

在 `App/Controller/` 目录下创建新的 PHP 文件，例如：

```php
<?php

namespace App\Controller;

use \FLEA\Controller\Action;

class MyController extends Action
{
    public function actionIndex()
    {
        $this->view->assign('title', '我的页面');
        $this->view->display('my/index.php');
    }
}
```

### 添加新的模型

在 `App/Model/` 目录下创建新的 PHP 文件，例如：

```php
<?php

namespace App\Model;

use \FLEA\Db\TableDataGateway;

class MyModel extends TableDataGateway
{
    public $tableName = 'my_table';
    public $primaryKey = 'id';
}
```

### 添加新的视图

在 `App/View/` 目录下创建对应的视图文件。

## 注意事项

1. 确保 MySQL 服务正在运行
2. 确保数据库用户名和密码正确
3. 确保 `cache` 目录有写入权限
4. 开发环境建议开启错误显示

## 故障排除

### 如果遇到错误
1. 检查数据库连接是否正常
2. 检查 cache 目录权限
3. 查看 PHP 错误日志
4. 确保 vendor 目录已正确安装

### 重置数据库
```bash
mysql -u root -p11111111 < blog.sql
```

## 下一步

你可以根据需要扩展此博客系统：
- 添加用户登录功能
- 添加标签分类
- 添加文章搜索
- 添加图片上传
- 添加后台管理界面

祝你使用愉快！🎉
