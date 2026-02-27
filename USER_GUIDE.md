# FleaPHP 框架用户手册

## 目录

1. [简介](#简介)
2. [快速开始](#快速开始)
3. [核心组件](#核心组件)
4. [配置管理](#配置管理)
5. [控制器开发](#控制器开发)
6. [模型开发](#模型开发)
7. [视图开发](#视图开发)
8. [数据库操作](#数据库操作)
9. [关联关系](#关联关系)
10. [异常处理](#异常处理)
11. [开发最佳实践](#开发最佳实践)

---

## 简介

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 命名空间标准和 Composer 自动加载机制。框架设计简洁，适合快速开发中小型 Web 应用。

### 主要特性

- **MVC 架构**：清晰的模型 - 视图 - 控制器分离
- **PSR-4 自动加载**：基于 Composer 的标准自动加载
- **TableDataGateway 模式**：简洁的数据库 CRUD 操作
- **简单视图引擎**：使用原生 PHP 作为模板语言
- **事件回调**：支持控制器生命周期回调
- **异常处理**：完善的异常处理机制
- **日志服务**：实现 PSR-3 标准的日志接口

### 系统要求

- **PHP**: 7.4+
- **Composer**: 用于依赖管理
- **数据库**: MySQL 5.0+ 或其他 PDO 支持的数据库

---

## 快速开始

### 1. 项目初始化

```bash
# 安装依赖
composer install

# 启动开发服务器
php74 -S 127.0.0.1:8081

# 访问应用
http://127.0.0.1:8081/index.php
```

### 2. 项目结构

```
project/
├── App/
│   ├── Config.php          # 应用配置文件
│   ├── Controller/         # 控制器目录
│   │   └── PostController.php
│   ├── Model/              # 模型目录
│   │   ├── Post.php
│   │   └── Comment.php
│   └── View/               # 视图模板目录
│       └── post/
│           ├── index.php
│           └── view.php
├── FLEA/                   # 框架核心目录
│   ├── FLEA.php           # 框架入口
│   └── FLEA/              # 框架组件
├── cache/                  # 缓存目录
├── vendor/                 # Composer 依赖
├── composer.json           # Composer 配置
└── index.php               # 应用入口
```

### 3. 入口文件

```php
<?php
// index.php

require_once 'vendor/autoload.php';

// 注册 App 命名空间
class_loader()->addPsr4('App\\', __DIR__ . '/App/');

// 加载应用配置
\FLEA::loadAppInf('App/Config.php');

// 运行 MVC 应用
\FLEA::runMVC();
```

### 4. URL 访问格式

```
标准模式：index.php?controller=Post&action=index
PATHINFO 模式：index.php/Post/index
URL 重写：/Post/index
```

---

## 核心组件

### FLEA 类

框架的主入口，提供静态方法：

```php
// 加载配置
\FLEA::loadAppInf('config.php');

// 获取配置值
$dbConfig = \FLEA::getAppInf('dbDSN');

// 设置配置值
\FLEA::setAppInf('siteName', '我的博客');

// 获取数据库对象
$dbo = \FLEA::getDBO();
```

### 调度器 (Dispatcher)

```php
// 配置中设置
'dispatcher' => \FLEA\Dispatcher\Simple::class,
'controllerAccessor' => 'controller',
'actionAccessor' => 'action',
```

调度器解析 URL 参数，实例化控制器并执行相应的动作方法。

---

## 配置管理

### 配置文件结构

```php
<?php
// App/Config.php

return [
    // 数据库配置
    'dbDSN' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'login' => 'root',
        'password' => 'password',
        'database' => 'blog',
        'charset' => 'utf8mb4',
    ],

    // 控制器配置
    'controllerAccessor' => 'controller',
    'actionAccessor' => 'action',
    'defaultController' => 'Post',
    'defaultAction' => 'index',

    // URL 配置
    'urlMode' => URL_STANDARD,  // URL_STANDARD, URL_PATHINFO, URL_REWRITE
    'urlBootstrap' => 'index.php',

    // 视图配置
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,
    ],

    // 日志配置
    'logEnabled' => false,
    'logFileDir' => __DIR__ . '/../logs',
    'logErrorLevel' => [\Psr\Log\LogLevel::ERROR, \Psr\Log\LogLevel::WARNING],

    // 错误显示（开发环境）
    'displayErrors' => true,
    'displaySource' => true,
];
```

### 调试模式与生产模式

```php
// 在 index.php 中定义
define('DEPLOY_MODE', true);  // 生产模式
// 或不定义（默认调试模式）
```

---

## 控制器开发

### 基本结构

```php
<?php
namespace App\Controller;

use \FLEA\Controller\Action;
use App\Model\Post;

class PostController extends Action
{
    protected $postModel;
    public $view;

    public function __construct()
    {
        parent::__construct('Post');
        $this->postModel = new Post();
        $this->view = $this->_getView();
    }

    /**
     * 列表页
     */
    public function actionIndex()
    {
        $posts = $this->postModel->findAll(['status' => 1]);
        $this->view->assign('posts', $posts);
        $this->view->display('post/index.php');
    }

    /**
     * 详情页
     */
    public function actionView()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('ID 不能为空');
        }

        $post = $this->postModel->find($id);
        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('记录不存在');
        }

        $this->view->assign('post', $post);
        $this->view->display('post/view.php');
    }

    /**
     * 创建
     */
    public function actionCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
            ];

            $this->postModel->create($data);
            header('Location: ?controller=Post&action=index');
        } else {
            $this->view->display('post/create.php');
        }
    }
}
```

### 控制器生命周期方法

```php
class MyController extends Action
{
    // 调度前回调
    public function __setController($controllerName, $actionName): void
    {
        $this->_controllerName = $controllerName;
        $this->_actionName = $actionName;
    }

    // 动作执行前
    public function _beforeExecute($actionMethod): void
    {
        // 权限检查等
    }

    // 动作执行后
    public function _afterExecute($actionMethod): void
    {
        // 日志记录等
    }
}
```

---

## 模型开发

### 基本模型

```php
<?php
namespace App\Model;

use \FLEA\Db\TableDataGateway;

class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public $primaryKey = 'id';

    // 自定义查询方法
    public function getPublishedPosts($limit = 10, $offset = 0)
    {
        return $this->findAll(
            ['status' => 1],
            'created_at DESC',
            [$limit, $offset]
        );
    }

    // 创建记录
    public function createPost($data)
    {
        return $this->create($data);
    }

    // 更新记录
    public function updatePost($id, $data)
    {
        return $this->updateByConditions([$this->primaryKey => $id], $data);
    }

    // 删除记录
    public function deletePost($id)
    {
        return $this->removeByPkv($id);
    }
}
```

### 模型方法返回类型

| 方法 | 返回类型 | 说明 |
|------|----------|------|
| `find()` | `?array` | 查询单条记录 |
| `findAll()` | `array` | 查询多条记录 |
| `create()` | `int` | 创建记录，返回插入 ID |
| `update()` | `bool` | 更新记录 |
| `remove()` | `bool` | 删除记录 |
| `findCount()` | `int` | 统计记录数 |

---

## 视图开发

### Simple 视图引擎

```php
// 控制器中
$this->view->assign('title', '文章标题');
$this->view->assign('content', '文章内容');
$this->view->display('post/view.php');
```

### 模板文件

```php
<!-- App/View/post/view.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <div><?php echo $content; ?></div>
</body>
</html>
```

### 视图配置

```php
'view' => \FLEA\View\Simple::class,
'viewConfig' => [
    'templateDir' => __DIR__ . '/View',  // 模板目录
    'cacheDir' => __DIR__ . '/../cache', // 缓存目录
    'cacheLifeTime' => 900,              // 缓存时间（秒）
    'enableCache' => false,              // 是否启用缓存
],
```

---

## 数据库操作

### 查询方法

```php
// 查询单条记录
$post = $this->postModel->find($id);
$post = $this->postModel->findByField('status', 1);
$post = $this->postModel->findByPkv($id);

// 查询多条记录
$posts = $this->postModel->findAll();
$posts = $this->postModel->findAll(['status' => 1], 'created_at DESC');
$posts = $this->postModel->findAll(null, null, [10, 0]); // 分页

// 统计
$count = $this->postModel->findCount(['status' => 1]);

// 查询指定字段
$post = $this->postModel->find($id, null, 'id,title,content');
```

### 条件查询

```php
// 数组条件
$posts = $this->postModel->findAll(['status' => 1, 'author' => 'admin']);

// SQL 条件
$posts = $this->postModel->findAll('status = 1 AND author = "admin"');

// LIKE 查询
$posts = $this->postModel->findAll(['title LIKE' => '%关键词%']);

// IN 查询
$posts = $this->postModel->findAll(['id IN' => [1, 2, 3]]);
```

### 操作方法

```php
// 创建
$data = ['title' => '标题', 'content' => '内容'];
$id = $this->postModel->create($data);

// 更新
$data = ['title' => '新标题'];
$this->postModel->update(['id' => $id, ...$data]);
$this->postModel->updateByConditions(['id' => $id], $data);

// 删除
$this->postModel->remove($row);
$this->postModel->removeByPkv($id);
$this->postModel->removeByConditions(['status' => 0]);
```

---

## 关联关系

### 定义关联

```php
class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public $primaryKey = 'id';

    // 一对多：一篇文章有多个评论
    public ?array $hasMany = [
        [
            'tableClass' => Comment::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'comments',
        ],
    ];
}

class Comment extends TableDataGateway
{
    public string $tableName = 'comments';
    public $primaryKey = 'id';

    // 从属：评论属于一篇文章
    public ?array $belongsTo = [
        [
            'tableClass' => Post::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'post',
        ],
    ];
}
```

### 使用关联查询

```php
// 查询文章及评论
$post = $this->postModel->find($id, null, '*', true); // true 启用关联
$comments = $post['comments'];

// 查询评论及文章
$comment = $this->commentModel->find($id, null, '*', true);
$post = $comment['post'];

// 禁用关联（提高性能）
$posts = $this->postModel->findAll(
    ['status' => 1],
    'created_at DESC',
    [10, 0],
    '*',
    false  // false 禁用关联
);
```

### 关联类型

| 类型 | 常量 | 说明 |
|------|------|------|
| HAS_ONE | `HAS_ONE` | 一对一关联 |
| HAS_MANY | `HAS_MANY` | 一对多关联 |
| BELONGS_TO | `BELONGS_TO` | 从属关联 |
| MANY_TO_MANY | `MANY_TO_MANY` | 多对多关联 |

---

## 异常处理

### 框架异常类

```php
// 参数异常
throw new \FLEA\Exception\InvalidArguments('参数不能为空');
throw new \FLEA\Exception\MissingArguments('缺少必要参数');

// 控制器/动作异常
throw new \FLEA\Exception\MissingController('控制器不存在');
throw new \FLEA\Exception\MissingAction('动作不存在');

// 数据库异常
throw new \FLEA\Db\Exception\MissingPrimaryKey('缺少主键');
throw new \FLEA\Db\Exception\SqlQuery('SQL 查询错误');
```

### 异常处理示例

```php
public function actionView()
{
    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        throw new \FLEA\Exception\InvalidArguments('文章 ID 不能为空');
    }

    $post = $this->postModel->find($id);

    if (!$post) {
        throw new \FLEA\Exception\InvalidArguments('文章不存在');
    }

    $this->view->display('post/view.php');
}
```

---

## 开发最佳实践

### 1. 命名规范

```php
// 控制器：XxxController
class PostController extends Action {}

// 模型：表名单数形式
class Post extends TableDataGateway {}

// 动作方法：actionXxx
public function actionIndex() {}
public function actionCreate() {}

// 视图文件：{controller}/{action}.php
App/View/post/index.php
```

### 2. 数据库字段约定

```php
// 主键
id

// 时间戳
created_at   // 创建时间（自动填充）
updated_at   // 更新时间（自动填充）

// 外键
{table}_id   // 如：post_id, user_id
```

### 3. 代码组织

```php
<?php
namespace App\Controller;

// 1. 使用声明
use \FLEA\Controller\Action;
use App\Model\Post;

// 2. 类定义
class PostController extends Action
{
    // 3. 属性声明
    protected $postModel;
    public $view;

    // 4. 构造函数
    public function __construct()
    {
        parent::__construct('Post');
        $this->postModel = new Post();
    }

    // 5. 动作方法（public）
    public function actionIndex() {}

    // 6. 辅助方法（protected/private）
    protected function validateData() {}
}
```

### 4. 安全实践

```php
// 转义输出
echo htmlspecialchars($post['title']);

// 参数验证
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    throw new \FLEA\Exception\InvalidArguments('ID 无效');
}

// CSRF 保护（表单）
session_start();
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;

// 验证
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    throw new \FLEA\Exception\InvalidArguments('CSRF 验证失败');
}
```

### 5. 性能优化

```php
// 禁用不需要的关联
$posts = $this->postModel->findAll(
    ['status' => 1],
    null,
    [10, 0],
    '*',
    false  // 禁用关联查询
);

// 只查询需要的字段
$posts = $this->postModel->findAll(
    ['status' => 1],
    'created_at DESC',
    null,
    'id,title,created_at'  // 只查询需要的字段
);

// 使用缓存
'viewConfig' => [
    'enableCache' => true,
    'cacheLifeTime' => 3600,
],
```

---

## 附录

### A. 辅助函数

```php
// 日志记录
log_message('调试信息', \Psr\Log\LogLevel::DEBUG);
log_message('错误信息', \Psr\Log\LogLevel::ERROR);

// 加载辅助文件
\FLEA::loadHelper('array');
\FLEA::loadHelper('string');
```

### B. 常量定义

```php
// URL 模式
URL_STANDARD    // 标准模式
URL_PATHINFO    // PATHINFO 模式
URL_REWRITE     // URL 重写模式

// 关联类型
HAS_ONE         // 一对一
HAS_MANY        // 一对多
BELONGS_TO      // 从属
MANY_TO_MANY    // 多对多
```

### C. 相关文档

- [SPEC.md](SPEC.md) - 框架规格说明书
- [CHANGES.md](CHANGES.md) - 框架修改记录
- [README.md](README.md) - 项目说明
