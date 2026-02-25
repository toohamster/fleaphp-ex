# FLEA 博客小程序

一个基于 FLEA 框架开发的简单博客系统。

## 功能特性

- ✅ 文章列表展示
- ✅ 文章详情查看
- ✅ 创建新文章
- ✅ 编辑文章
- ✅ 删除文章
- ✅ 评论功能
- ✅ 分页显示
- ✅ 响应式设计

## 安装步骤

### 1. 创建数据库

```bash
mysql -u root -p < blog.sql
```

或者手动执行 `blog.sql` 中的 SQL 语句。

### 2. 配置数据库连接

已配置好的数据库信息：
- 主机: 127.0.0.1:3306
- 用户名: root
- 密码: 11111111
- 数据库: blog

如需修改，请编辑 `App/Config.php` 文件。

### 3. 访问应用

打开浏览器访问：
```
http://localhost/fleaphp-ex/index.php
```

## 项目结构

```
fleaphp-ex/
├── App/
│   ├── Config.php          # 应用配置文件
│   ├── Controller/
│   │   └── Post.php         # 文章控制器
│   ├── Model/
│   │   ├── Post.php         # 文章模型
│   │   └── Comment.php      # 评论模型
│   └── View/
│       └── post/
│           ├── index.php    # 文章列表页
│           ├── view.php     # 文章详情页
│           ├── create.php   # 创建文章页
│           └── edit.php     # 编辑文章页
├── blog.sql                 # 数据库初始化脚本
├── index.php                # 应用入口文件
└── FLEA/                    # FLEA 框架核心
```

## 使用说明

### 访问首页
```
http://localhost/fleaphp-ex/index.php
或
http://localhost/fleaphp-ex/index.php?controller=Post&action=index
```

### 查看文章
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=view&id=1
```

### 创建文章
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=create
```

### 编辑文章
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=edit&id=1
```

### 删除文章
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=delete&id=1
```

## 技术栈

- **框架**: FLEA (PSR-4 标准)
- **数据库**: MySQL
- **PHP**: 7.1+
- **模板引擎**: 原生 PHP
- **CSS**: 自定义响应式样式

## 数据库表结构

### posts (文章表)
- id: 主键
- title: 文章标题
- content: 文章内容
- author: 作者
- created_at: 创建时间
- updated_at: 更新时间
- status: 状态 (0-草稿, 1-发布)

### comments (评论表)
- id: 主键
- post_id: 文章ID (外键)
- author: 评论者
- email: 邮箱
- content: 评论内容
- created_at: 创建时间
- status: 状态 (0-待审核, 1-已审核)

## 开发说明

### 添加新的控制器

1. 在 `App/Controller/` 目录下创建新的控制器类
2. 继承 `\FLEA\Controller\Action`
3. 实现以 `action` 开头的方法

示例：
```php
<?php

namespace App\Controller;

use \FLEA\Controller\Action;

class MyController extends Action
{
    public function actionIndex()
    {
        // 你的逻辑
    }
}
```

### 添加新的模型

1. 在 `App/Model/` 目录下创建新的模型类
2. 继承 `\FLEA\Db\TableDataGateway`

示例：
```php
<?php

namespace App\Model;

use \FLEA\Db\TableDataGateway;

class MyModel extends TableDataGateway
{
    protected $tableName = 'my_table';
    protected $primaryKey = 'id';
}
```

## 许可证

MIT License

## 作者

FLEA 框架团队
