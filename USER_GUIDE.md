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
11. [分页功能](#分页功能)
12. [Ajax 支持](#ajax-支持)
13. [RBAC 权限控制](#rbac-权限控制)
14. [ACL 访问控制列表](#acl-访问控制列表)
15. [Session 管理](#session-管理)
16. [日志服务](#日志服务)
17. [辅助类](#辅助类)
18. [开发最佳实践](#开发最佳实践)
19. [常见问题](#常见问题)

---

## 简介

FleaPHP 是一个轻量级的 PHP MVC 框架，采用 PSR-4 命名空间标准和 Composer 自动加载机制。框架设计简洁，适合快速开发中小型 Web 应用。

### 主要特性

- **MVC 架构**：清晰的模型 - 视图 - 控制器分离
- **PSR-4 自动加载**：基于 Composer 的标准自动加载
- **TableDataGateway 模式**：简洁的数据库 CRUD 操作
- **简单视图引擎**：使用原生 PHP 作为模板语言，支持模板缓存
- **事件回调**：支持控制器生命周期回调（_beforeExecute、_afterExecute）
- **关联查询**：支持 HAS_ONE、HAS_MANY、BELONGS_TO、MANY_TO_MANY 关联
- **异常处理**：完善的异常处理机制
- **日志服务**：实现 PSR-3 标准的日志接口
- **RBAC/ACL**：内置基于角色的权限控制和访问控制列表

### 系统要求

- **PHP**: 7.4+
- **Composer**: 用于依赖管理
- **数据库**: MySQL 5.0+ 或其他 PDO 支持的数据库（PostgreSQL、SQLite 等）
- **Web 服务器**: Apache/Nginx（可选，用于 URL 重写）

### 框架版本

当前版本：**1.7.1524**

---

## 快速开始

### 1. 项目初始化

```bash
# 进入项目目录
cd your-project

# 安装依赖
composer install

# 启动开发服务器（PHP 7.4）
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
├── cache/                  # 缓存目录（需可写）
├── vendor/                 # Composer 依赖
├── composer.json           # Composer 配置
└── index.php               # 应用入口
```

### 3. 入口文件

```php
<?php
// index.php

require_once 'vendor/autoload.php';

// 注册 App 命名空间到自动加载器
class_loader()->addPsr4('App\\', __DIR__ . '/App/');

// 加载应用配置
\FLEA::loadAppInf('App/Config.php');

// 运行 MVC 应用
\FLEA::runMVC();
```

### 4. URL 访问格式

**标准模式**（默认）：
```
index.php?controller=Post&action=index
index.php?controller=Post&action=view&id=1
```

**PATHINFO 模式**：
```
index.php/Post/index
index.php/Post/view/id/1
```

**URL 重写模式**（需要 .htaccess 或 Nginx 配置）：
```
/Post/index
/Post/view/id/1
```

### 5. 第一个控制器

```php
<?php
// App/Controller/IndexController.php

namespace App\Controller;

use \FLEA\Controller\Action;

class IndexController extends Action
{
    public function __construct()
    {
        parent::__construct('Index');
    }

    public function actionIndex()
    {
        echo "Hello, FleaPHP!";
    }
}
```

访问：`index.php?controller=Index&action=index`

---

## 核心组件

### FLEA 类

框架的主入口类，提供静态方法管理框架服务：

```php
class FLEA
{
    // 加载应用配置
    public static function loadAppInf($config): void

    // 获取配置值
    public static function getAppInf(string $option, $default = null)

    // 设置配置值
    public static function setAppInf($option, $data = null): void

    // 获取单例实例
    public static function getSingleton(string $className)

    // 注册对象实例
    public static function register($object, string $id): void

    // 检查对象是否已注册
    public static function isRegistered(string $id): bool

    // 获取数据库访问对象
    public static function getDBO(?string $dsn = null)

    // 运行 MVC 应用
    public static function runMVC()
}
```

### 使用示例

```php
// 加载配置
\FLEA::loadAppInf('App/Config.php');

// 获取配置值
$dbConfig = \FLEA::getAppInf('dbDSN');
$siteName = \FLEA::getAppInf('siteName', '默认站点名');

// 设置配置值
\FLEA::setAppInf('siteName', '我的博客');

// 获取数据库对象
$dbo = \FLEA::getDBO();

// 运行应用
\FLEA::runMVC();
```

### Config 配置管理器

单例模式管理框架配置：

```php
namespace FLEA;

class Config
{
    public $appInf = [];       // 应用程序配置
    public $objects = [];      // 对象实例容器
    public $dbo = [];          // 数据库访问对象

    // 获取单例实例
    public static function getInstance(): self

    // 获取配置值
    public function getAppInf(string $option, $default = null)

    // 设置配置值
    public function setAppInf($option, $data = null): void

    // 合并配置
    public function mergeAppInf(array $config): void
}
```

---

## 配置管理

### 配置文件结构

```php
<?php
// App/Config.php

return [
    // ========================
    // 数据库配置
    // ========================
    'dbDSN' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'login' => 'root',
        'password' => 'password',
        'database' => 'blog',
        'charset' => 'utf8mb4',
    ],

    // 数据表前缀
    'dbTablePrefix' => '',

    // ========================
    // 控制器配置
    // ========================
    'controllerAccessor' => 'controller',     // URL 中控制器的参数名
    'actionAccessor' => 'action',             // URL 中动作的参数名
    'defaultController' => 'Post',            // 默认控制器
    'defaultAction' => 'index',               // 默认动作
    'controllerMethodPrefix' => 'action',     // 控制器方法前缀

    // ========================
    // URL 配置
    // ========================
    'urlMode' => URL_STANDARD,    // URL_STANDARD, URL_PATHINFO, URL_REWRITE
    'urlBootstrap' => 'index.php',
    'urlLowerChar' => false,      // URL 是否转换为小写

    // ========================
    // 视图配置
    // ========================
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,     // 缓存时间（秒）
        'enableCache' => false,     // 开发环境建议关闭
    ],

    // ========================
    // 调度器配置
    // ========================
    'dispatcher' => \FLEA\Dispatcher\Simple::class,

    // ========================
    // 日志配置
    // ========================
    'logEnabled' => false,
    'logProvider' => null,
    'logFileDir' => __DIR__ . '/../logs',
    'logFilename' => 'app.log',
    'logErrorLevel' => [
        \Psr\Log\LogLevel::ERROR,
        \Psr\Log\LogLevel::WARNING,
    ],

    // ========================
    // Session 配置
    // ========================
    'sessionProvider' => null,  // 默认使用 PHP 原生 Session
    // 'sessionProvider' => \FLEA\Session\Db::class,  // 使用数据库 Session

    // ========================
    // 错误显示（开发环境）
    // ========================
    'displayErrors' => true,
    'displaySource' => true,
    'friendlyErrorsMessage' => true,

    // ========================
    // 缓存目录
    // ========================
    'internalCacheDir' => __DIR__ . '/../cache',
];
```

### 调试模式与生产模式

FleaPHP 支持两种运行模式：

**调试模式**（默认）：
```php
// 不定义 DEPLOY_MODE 或定义为 false
// 使用 Config/DEBUG_MODE_CONFIG.php 中的配置
// 显示详细错误信息，适合开发环境
```

**生产模式**：
```php
// 在 index.php 中定义
define('DEPLOY_MODE', true);

// 使用 Config/DEPLOY_MODE_CONFIG.php 中的配置
// 隐藏错误信息，记录日志，适合生产环境
```

### 配置继承与覆盖

```php
// 在配置文件中可以先加载默认配置，然后覆盖
$defaultConfig = require FLEA_DIR . '/Config/DEBUG_MODE_CONFIG.php';
$customConfig = [
    'dbDSN' => [...],  // 覆盖数据库配置
];
return array_merge($defaultConfig, $customConfig);
```

---

## 控制器开发

### 基本结构

```php
<?php
namespace App\Controller;

use \FLEA\Controller\Action;
use App\Model\Post;

/**
 * 文章控制器
 */
class PostController extends Action
{
    /**
     * @var Post
     */
    protected $postModel;

    /**
     * @var \FLEA\View\Simple
     */
    public $view;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 调用父类构造函数，传入控制器名称
        parent::__construct('Post');

        // 初始化模型
        $this->postModel = new Post();

        // 获取视图对象
        $this->view = $this->_getView();
    }

    /**
     * 列表页 - action 前缀的方法是公开的控制器动作
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
            throw new \FLEA\Exception\InvalidArguments('文章 ID 不能为空');
        }

        $post = $this->postModel->find($id);
        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('文章不存在');
        }

        $this->view->assign('post', $post);
        $this->view->display('post/view.php');
    }

    /**
     * 创建文章 - 处理 GET 和 POST 请求
     */
    public function actionCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 处理表单提交
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author' => $_POST['author'] ?? '匿名',
            ];

            // 数据验证
            if (empty($data['title'])) {
                echo '<script>alert("标题不能为空"); history.back();</script>';
                return;
            }

            // 创建文章
            $id = $this->postModel->create($data);
            if ($id) {
                echo '<script>alert("创建成功"); location.href="?controller=Post&action=index";</script>';
            } else {
                echo '<script>alert("创建失败"); history.back();</script>';
            }
        } else {
            // 显示表单
            $this->view->display('post/create.php');
        }
    }

    /**
     * 编辑文章
     */
    public function actionEdit()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('文章 ID 不能为空');
        }

        // 获取文章
        $post = $this->postModel->find($id);
        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('文章不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author' => $_POST['author'] ?? '匿名',
            ];

            if (empty($data['title'])) {
                echo '<script>alert("标题不能为空"); history.back();</script>';
                return;
            }

            // 更新文章
            $data['id'] = $id;
            $result = $this->postModel->update($data);
            if ($result) {
                echo '<script>alert("更新成功"); location.href="?controller=Post&action=view&id=' . $id . '";</script>';
            } else {
                echo '<script>alert("更新失败"); history.back();</script>';
            }
        } else {
            $this->view->assign('post', $post);
            $this->view->display('post/edit.php');
        }
    }

    /**
     * 删除文章
     */
    public function actionDelete()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('文章 ID 不能为空');
        }

        // 二次确认
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            $result = $this->postModel->removeByPkv($id);
            if ($result) {
                echo '<script>alert("删除成功"); location.href="?controller=Post&action=index";</script>';
            } else {
                echo '<script>alert("删除失败"); history.back();</script>';
            }
        } else {
            echo '<script>if(confirm("确定要删除吗？")) { location.href="?controller=Post&action=delete&id=' . $id . '&confirm=yes"; } else { history.back(); }</script>';
        }
    }
}
```

### 控制器生命周期方法

```php
class MyController extends Action
{
    /**
     * 设置控制器信息（由调度器调用）
     */
    public function __setController($controllerName, $actionName): void
    {
        $this->_controllerName = $controllerName;
        $this->_actionName = $actionName;
    }

    /**
     * 设置调度器（由调度器调用）
     */
    public function __setDispatcher($dispatcher): void
    {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * 动作执行前的回调
     * 可用于：权限检查、登录验证、数据预处理等
     */
    public function _beforeExecute($actionMethod): void
    {
        // 检查用户是否登录
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=User&action=login');
            exit;
        }

        // 记录访问日志
        log_message("访问 {$this->_controllerName}::{$actionMethod}", \Psr\Log\LogLevel::INFO);
    }

    /**
     * 动作执行后的回调
     * 可用于：清理资源、记录操作日志等
     */
    public function _afterExecute($actionMethod): void
    {
        // 清理临时数据
    }

    /**
     * 渲染视图前的回调
     */
    public function _beforeRender($template): void
    {
        // 设置全局模板变量
        $this->view->assign('siteName', '我的博客');
    }
}
```

### 控制器间跳转

```php
public function actionSuccess()
{
    // 重定向到另一个动作
    header('Location: ?controller=Post&action=index');
    exit;
}
```

### 返回 JSON 数据

```php
public function actionAjaxSearch()
{
    header('Content-Type: application/json');

    $keyword = $_GET['keyword'] ?? '';
    $results = $this->model->search($keyword);

    echo json_encode([
        'success' => true,
        'data' => $results,
    ]);
    exit;
}
```

---

## 模型开发

### 基本模型

```php
<?php
namespace App\Model;

use \FLEA\Db\TableDataGateway;

/**
 * 文章模型
 */
class Post extends TableDataGateway
{
    /**
     * 数据表名
     */
    public string $tableName = 'posts';

    /**
     * 主键字段
     */
    public $primaryKey = 'id';

    /**
     * 启用自动验证
     */
    public $autoValidating = true;

    /**
     * 定义验证器
     */
    public $verifier = null;  // 设置验证器实例

    /**
     * 获取所有已发布的文章
     *
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return array
     */
    public function getPublishedPosts($limit = 10, $offset = 0)
    {
        return $this->findAll(
            ['status' => 1],
            'created_at DESC',
            [$limit, $offset],
            '*',
            false  // 不启用关联查询，提高性能
        );
    }

    /**
     * 根据 ID 获取文章
     *
     * @param int $id 文章 ID
     * @return array|null
     */
    public function getPostById($id)
    {
        return $this->find($id);
    }

    /**
     * 获取文章总数
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->findCount(['status' => 1]);
    }

    /**
     * 搜索文章
     *
     * @param string $keyword 关键词
     * @param int $limit 限制数量
     * @return array
     */
    public function searchArticles(string $keyword, $limit = 20): array
    {
        $keyword = '%' . $keyword . '%';
        return $this->findAll(
            [
                'status' => 1,
                'title LIKE' => $keyword,
            ],
            'created_at DESC',
            $limit
        );
    }

    /**
     * 增加文章浏览次数
     *
     * @param int $id 文章 ID
     * @return bool
     */
    public function incrementViewCount(int $id): bool
    {
        return $this->incrField(['id' => $id], 'view_count', 1);
    }
}
```

### 模型方法返回类型

| 方法 | 返回类型 | 说明 |
|------|----------|------|
| `find()` | `?array` | 查询单条记录，不存在返回 null |
| `findAll()` | `array` | 查询多条记录，返回数组 |
| `findByField()` | `?array` | 按字段查询单条记录 |
| `findByPkv()` | `?array` | 按主键查询单条记录 |
| `findCount()` | `int` | 统计记录数 |
| `create()` | `int` | 创建记录，返回插入 ID |
| `update()` | `bool` | 更新记录 |
| `remove()` | `bool` | 删除记录 |
| `save()` | `int` | 保存记录（自动判断创建或更新） |

### 自动时间戳

TableDataGateway 会自动处理以下时间戳字段：

```php
// 创建记录时自动填充
$createdTimeFields = ['CREATED', 'CREATED_ON', 'CREATED_AT'];

// 创建和更新记录时自动填充
$updatedTimeFields = ['UPDATED', 'UPDATED_ON', 'UPDATED_AT'];
```

如果表中包含这些字段，框架会自动设置当前时间，无需手动指定。

---

## 视图开发

### Simple 视图引擎

```php
// 控制器中
$this->view->assign('title', '文章标题');
$this->view->assign('content', '文章内容');
$this->view->assign('tags', ['PHP', 'MySQL', 'FleaPHP']);
$this->view->display('post/view.php');
```

### 模板文件

```php
<!-- App/View/post/view.php -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?></h1>

    <div class="content">
        <?php echo $content; ?>
    </div>

    <!-- 遍历数组 -->
    <div class="tags">
        <?php if (!empty($tags)): ?>
            <span>标签：</span>
            <?php foreach ($tags as $tag): ?>
                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
```

### 视图配置

```php
'view' => \FLEA\View\Simple::class,
'viewConfig' => [
    'templateDir' => __DIR__ . '/View',  // 模板目录
    'cacheDir' => __DIR__ . '/../cache', // 缓存目录
    'cacheLifeTime' => 900,              // 缓存时间（秒），0 表示永久
    'enableCache' => false,              // 是否启用缓存
],
```

### 模板缓存

启用缓存后，模板会被编译成 PHP 文件并保存：

```php
// 开发环境建议关闭缓存
'enableCache' => false,

// 生产环境启用缓存
'enableCache' => true,
'cacheLifeTime' => 3600,
```

### 子视图（部分视图）

```php
// 控制器中
$this->view->assign('posts', $posts);
$this->view->assign('users', $users);

// 视图中渲染子视图
<?php $this->view->display('post/_list.php'); ?>
<?php $this->view->display('common/_header.php'); ?>
```

### 布局（Layout）

```php
// 控制器中
$this->view->assign('content', $this->_getContent());
$this->view->display('layouts/main.php');
```

```php
<!-- App/View/layouts/main.php -->
<!DOCTYPE html>
<html>
<head>
    <title>我的博客</title>
</head>
<body>
    <?php include __DIR__ . '/../common/_header.php'; ?>

    <div class="container">
        <?php echo $content; ?>
    </div>

    <?php include __DIR__ . '/../common/_footer.php'; ?>
</body>
</html>
```

---

## 数据库操作

### 查询方法

```php
<?php
$postModel = new Post();

// ========================
// 查询单条记录
// ========================

// 按主键查询
$post = $postModel->find($id);

// 按条件查询第一条
$post = $postModel->findByField('status', 1);
$post = $postModel->findByField('id', $id);

// 按主键值查询
$post = $postModel->findByPkv($id);

// 带字段选择
$post = $postModel->find($id, null, 'id,title,content');

// ========================
// 查询多条记录
// ========================

// 查询所有
$posts = $postModel->findAll();

// 带条件查询
$posts = $postModel->findAll(['status' => 1]);

// 带排序
$posts = $postModel->findAll(['status' => 1], 'created_at DESC');

// 带分页（limit, offset）
$posts = $postModel->findAll(
    ['status' => 1],
    'created_at DESC',
    [10, 0]  // [limit, offset]
);

// 带字段选择
$posts = $postModel->findAll(
    ['status' => 1],
    null,
    null,
    'id,title,created_at'
);

// ========================
// 统计
// ========================

// 统计总数
$count = $postModel->findCount();

// 带条件统计
$count = $postModel->findCount(['status' => 1]);
```

### 条件查询

```php
// 等于条件
$posts = $postModel->findAll(['status' => 1]);

// 多个等于条件（AND）
$posts = $postModel->findAll([
    'status' => 1,
    'author' => 'admin',
]);

// SQL 字符串条件
$posts = $postModel->findAll('status = 1 AND author = "admin"');

// LIKE 查询
$posts = $postModel->findAll(['title LIKE' => '%关键词%']);

// IN 查询
$posts = $postModel->findAll(['id IN' => [1, 2, 3, 4, 5]]);

// NOT IN 查询
$posts = $postModel->findAll(['id NOT IN' => [1, 2, 3]]);

// 大于/小于
$posts = $postModel->findAll([
    'created_at >=' => '2024-01-01 00:00:00',
    'view_count >' => 100,
]);

// 组合条件
$posts = $postModel->findAll([
    'status' => 1,
    'title LIKE' => '%PHP%',
    'created_at >=' => '2024-01-01',
]);
```

### 操作方法

```php
<?php
$postModel = new Post();

// ========================
// 创建记录
// ========================
$data = [
    'title' => '我的文章',
    'content' => '文章内容...',
    'author' => '作者名',
    'status' => 1,
];

// create() 返回插入的自增 ID
$id = $postModel->create($data);

// ========================
// 更新记录
// ========================

// 方式 1：update() - 需要提供完整记录（包含主键）
$data = [
    'id' => 1,
    'title' => '新标题',
    'content' => '新内容',
];
$postModel->update($data);

// 方式 2：updateByConditions() - 按条件更新
$data = ['title' => '新标题'];
$postModel->updateByConditions(['id' => 1], $data);

// 方式 3：更新多个字段
$postModel->updateByConditions(
    ['status' => 0],  // 条件
    ['status' => 1]   // 要更新的数据
);

// ========================
// 删除记录
// ========================

// 方式 1：removeByPkv() - 按主键删除
$postModel->removeByPkv($id);

// 方式 2：remove() - 删除记录数组
$row = $postModel->find($id);
$postModel->remove($row);

// 方式 3：removeByConditions() - 按条件删除
$postModel->removeByConditions(['status' => 0]);

// ========================
// 保存记录（自动判断创建或更新）
// ========================

// 如果数据包含主键值，则更新；否则创建
$data = ['title' => '标题'];
$postModel->save($data);
```

### 字段操作

```php
// 增加字段值
$postModel->incrField(['id' => 1], 'view_count', 1);

// 减少字段值
$postModel->decrField(['id' => 1], 'stock', 1);

// 更新单个字段
$postModel->updateField(['id' => 1], 'status', 1);
```

### 事务处理

```php
$dbo = \FLEA::getDBO();

$dbo->startTrans();
try {
    // 创建文章
    $postId = $postModel->create($postData);

    // 创建标签关联
    foreach ($tags as $tagId) {
        $tagModel->create([
            'post_id' => $postId,
            'tag_id' => $tagId,
        ]);
    }

    $dbo->completeTrans();  // 提交事务
} catch (Exception $e) {
    $dbo->completeTrans(false);  // 回滚事务
    throw $e;
}
```

---

## 关联关系

### 定义关联

```php
<?php
namespace App\Model;

use \FLEA\Db\TableDataGateway;

/**
 * 文章模型
 */
class Post extends TableDataGateway
{
    public string $tableName = 'posts';
    public $primaryKey = 'id';

    /**
     * 一对多：一篇文章有多个评论
     */
    public array $hasMany = [
        [
            'tableClass' => Comment::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'comments',
        ],
    ];
}

/**
 * 评论模型
 */
class Comment extends TableDataGateway
{
    public string $tableName = 'comments';
    public $primaryKey = 'id';

    /**
     * 从属：评论属于一篇文章
     */
    public array $belongsTo = [
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
// ========================
// 启用关联查询
// ========================

// find() 的第四个参数控制是否查询关联数据
$post = $postModel->find($id, null, '*', true);  // true 启用关联

// 获取文章及评论
$comments = $post['comments'];
foreach ($comments as $comment) {
    echo $comment['content'];
}

// 获取评论及所属文章
$comment = $commentModel->find($id, null, '*', true);
$post = $comment['post'];
echo $post['title'];

// ========================
// 禁用关联查询（提高性能）
// ========================

// 列表页通常不需要关联数据
$posts = $postModel->findAll(
    ['status' => 1],
    'created_at DESC',
    [10, 0],
    '*',
    false  // false 禁用关联
);
```

### 关联类型详解

#### HAS_ONE（一对一）

```php
public array $hasOne = [
    [
        'tableClass' => UserProfile::class,
        'foreignKey' => 'user_id',
        'mappingName' => 'profile',
    ],
];
```

#### HAS_MANY（一对多）

```php
public array $hasMany = [
    [
        'tableClass' => Comment::class,
        'foreignKey' => 'post_id',
        'mappingName' => 'comments',
        'sort' => 'created_at ASC',  // 可选：排序
    ],
];
```

#### BELONGS_TO（从属）

```php
public array $belongsTo = [
    [
        'tableClass' => Author::class,
        'foreignKey' => 'author_id',
        'mappingName' => 'author',
    ],
];
```

#### MANY_TO_MANY（多对多）

```php
public array $manyToMany = [
    [
        'tableClass' => Tag::class,
        'foreignKey' => 'post_id',
        'assocForeignKey' => 'tag_id',
        'joinTableClass' => PostTag::class,  // 中间表
        'mappingName' => 'tags',
    ],
];
```

### 关联数据操作

```php
// 获取关联对象
$link = $postModel->getLink('comments');

// 启用/禁用关联
$postModel->enableLink('comments');
$postModel->disableLink('comments');
$postModel->disableLinks();  // 禁用所有关联

// 清除关联
$postModel->clearLinks();

// 重新建立关联
$postModel->relink();
```

---

## 异常处理

### 框架异常类

```php
namespace FLEA\Exception;

// 参数异常
class InvalidArguments extends \Exception {}      // 无效参数
class MissingArguments extends \Exception {}      // 缺少参数
class TypeMismatch extends \Exception {}          // 类型不匹配

// 控制器/动作异常
class MissingController extends \Exception {}     // 控制器不存在
class MissingAction extends \Exception {}         // 动作不存在
class ExpectedClass extends \Exception {}         // 期望的类不存在
class ExpectedFile extends \Exception {}          // 期望的文件不存在

// 其他异常
class NotImplemented extends \Exception {}        // 方法未实现
class MustOverwrite extends \Exception {}         // 必须覆盖的方法
class ValidationFailed extends \Exception {}      // 验证失败
class CacheDisabled extends \Exception {}         // 缓存已禁用
class FileOperation extends \Exception {}         // 文件操作失败
class ExistsKeyName extends \Exception {}         // 键名已存在
class NotExistsKeyName extends \Exception {}      // 键名不存在
```

### 数据库异常

```php
namespace FLEA\Db\Exception;

class MissingDSN extends \Exception {}            // 缺少 DSN
class InvalidDSN extends \Exception {}            // 无效 DSN
class MissingPrimaryKey extends \Exception {}     // 缺少主键
class PrimaryKeyExists extends \Exception {}      // 主键已存在
class SqlQuery extends \Exception {}              // SQL 查询错误
class InvalidInsertID extends \Exception {}       // 无效的插入 ID
class MissingLink extends \Exception {}           // 关联不存在
class MissingLinkOption extends \Exception {}     // 缺少关联选项
class InvalidLinkType extends \Exception {}       // 无效的关联类型
class MetaColumnsFailed extends \Exception {}     // 获取表结构失败
```

### 异常处理示例

```php
public function actionView()
{
    try {
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('文章 ID 不能为空');
        }

        $post = $this->postModel->find($id);

        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('文章不存在');
        }

        $this->view->assign('post', $post);
        $this->view->display('post/view.php');

    } catch (\FLEA\Exception\InvalidArguments $e) {
        // 参数错误，显示错误页面
        echo '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';

    } catch (\FLEA\Db\Exception\MissingPrimaryKey $e) {
        // 数据库错误
        log_message($e->getMessage(), \Psr\Log\LogLevel::ERROR);
        echo '<div class="error">系统错误，请稍后重试</div>';

    } catch (\Exception $e) {
        // 其他异常
        log_message($e->getMessage(), \Psr\Log\LogLevel::ERROR);
        throw $e;
    }
}
```

### 全局异常处理

```php
// 在 index.php 中设置异常处理
set_exception_handler(function($e) {
    if (DEBUG_MODE) {
        // 调试模式：显示详细错误信息
        echo '<pre>';
        echo "Exception: " . get_class($e) . "\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString();
        echo '</pre>';
    } else {
        // 生产模式：显示友好错误页面
        include 'App/View/errors/500.php';
    }
});
```

---

## 分页功能

### Pager 辅助类

```php
use \FLEA\Helper\Pager;

// 创建分页对象
$pager = new Pager(
    $postModel,           // 数据源（TableDataGateway 实例）
    ['status' => 1],      // 查询条件
    'created_at DESC'     // 排序
);

// 设置每页记录数
$pager->pageSize = 10;

// 设置当前页
$page = intval($_GET['page'] ?? 1);
$pager->setPage($page);

// 执行查询
$posts = $pager->exec();

// 获取分页信息
$totalCount = $pager->totalCount;   // 总记录数
$pageCount = $pager->pageCount;     // 总页数
$currentPage = $pager->getPage();   // 当前页码

// 生成分页链接
echo $pager->getPageLinks();
```

### 自定义分页链接

```php
// 获取分页信息后手动生成链接
$pager = new Pager($postModel, ['status' => 1]);
$pager->pageSize = 10;
$pager->setPage($page);
$posts = $pager->exec();

// 手动生成分页 HTML
$html = '<div class="pagination">';
for ($i = 1; $i <= $pager->pageCount; $i++) {
    if ($i == $pager->getPage()) {
        $html .= '<span class="current">' . $i . '</span>';
    } else {
        $html .= '<a href="?page=' . $i . '">' . $i . '</a>';
    }
}
$html .= '</div>';

echo $html;
```

### 使用 SQL 作为数据源

```php
// 当不使用 TableDataGateway 时，可以使用 SQL 作为数据源
$pdo = \FLEA::getDBO();
$sql = "SELECT * FROM posts WHERE status = 1 ORDER BY created_at DESC";

$pager = new Pager($sql, null, null);
$pager->dbo = $pdo;  // 设置数据库访问对象
$pager->pageSize = 10;
$posts = $pager->exec();
```

---

## Ajax 支持

### Ajax 类

```php
use \FLEA\Ajax;

// 初始化 Ajax 对象
$ajax = new Ajax();

// 注册点击事件
$ajax->registerEvent(
    '#deleteBtn',           // 页面对象 ID
    'click',                // 事件类型
    '?controller=Post&action=delete'  // 目标 URL
);

// 注册表单提交事件
$ajax->registerEvent(
    '#postForm',
    'submit',
    '?controller=Post&action=create',
    [
        'beforeSubmit' => 'validateForm',
        'success' => 'handleSuccess',
        'error' => 'handleError',
        'clearForm' => true,
    ]
);

// 输出 JavaScript
$ajax->dumpJs();
```

### 前端 JavaScript

```html
<script src="jquery.js"></script>
<?php $ajax->dumpJs(); ?>

<script>
function validateForm() {
    // 表单验证
    var title = $('#title').val();
    if (!title) {
        alert('标题不能为空');
        return false;
    }
    return true;
}

function handleSuccess(response) {
    // 处理成功响应
    alert('操作成功');
    location.reload();
}

function handleError(xhr, status, error) {
    // 处理错误
    alert('操作失败：' + error);
}
</script>
```

### 控制器处理 Ajax 请求

```php
public function actionDelete()
{
    if (\FLEA::isAjaxRequest()) {
        header('Content-Type: application/json');

        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID 无效']);
            exit;
        }

        $result = $this->postModel->removeByPkv($id);
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '删除失败']);
        }
        exit;
    }

    // 非 Ajax 请求的处理
    // ...
}
```

---

## RBAC 权限控制

### Rbac 类

```php
use \FLEA\Rbac;

// 创建 RBAC 实例
$rbac = new Rbac();

// 设置用户信息到 Session
$rbac->setUser(
    ['user_id' => 1, 'username' => 'admin'],
    ['admin', 'editor']  // 角色列表
);

// 获取当前用户信息
$userData = $rbac->getUser();

// 检查访问权限
if ($rbac->checkAccess('PostController::create')) {
    // 有权限
} else {
    // 无权限
}
```

### 在控制器中使用 RBAC

```php
class PostController extends Action
{
    public function _beforeExecute($actionMethod): void
    {
        session_start();
        $rbac = new Rbac();
        $user = $rbac->getUser();

        // 检查是否登录
        if (!$user) {
            header('Location: ?controller=User&action=login');
            exit;
        }

        // 检查特定动作的权限
        $act = 'PostController::' . $actionMethod;
        if (!$rbac->checkAccess($act, $user[$rbac->_rolesKey])) {
            throw new \Exception('无权访问');
        }
    }
}
```

### UsersManager 和 RolesManager

```php
use \FLEA\Rbac\UsersManager;
use \FLEA\Rbac\RolesManager;

// 用户管理
$userManager = new UsersManager();

// 查找用户
$user = $userManager->findByUsername('admin');

// 验证用户
$result = $userManager->validateUser('admin', 'password');
if ($result) {
    // 验证成功
    $rbac = new Rbac();
    $roles = $userManager->fetchRoles($user);
    $rbac->setUser($user, $roles);
}

// 角色管理
$rolesManager = new RolesManager();

// 查找角色
$role = $rolesManager->findByRolename('admin');
```

---

## ACL 访问控制列表

### Acl Manager

```php
use \FLEA\Acl\Manager;

$acl = new Manager();

// 获取用户及其权限信息
$user = $acl->getUserWithPermissions(['username' => 'admin']);

// 检查用户是否有特定权限
$hasPermission = false;
foreach ($user['roles'] as $role) {
    foreach ($role['permissions'] as $permission) {
        if ($permission['name'] === 'post.create') {
            $hasPermission = true;
            break;
        }
    }
}
```

### ACL 表结构

```
users (用户表)
├── user_id
├── username
├── password
├── email
└── user_group_id (用户组 ID)

user_groups (用户组表)
├── user_group_id
├── name
├── parent_id
├── left_value  (嵌套集左值)
└── right_value (嵌套集右值)

roles (角色表)
├── role_id
├── name
└── description

permissions (权限表)
├── permission_id
├── name
└── description

多对多关联表:
- user_groups_has_roles (用户组 - 角色)
- user_groups_has_permissions (用户组 - 权限)
- users_has_roles (用户 - 角色)
- users_has_permissions (用户 - 权限)
```

---

## Session 管理

### 数据库 Session

```php
// 配置中使用数据库 Session
'sessionProvider' => \FLEA\Session\Db::class,
'sessionDbTableName' => 'sessions',
'sessionDbFieldId' => 'sess_id',
'sessionDbFieldData' => 'sess_data',
'sessionDbFieldActivity' => 'activity',
```

### 创建 Session 表

```sql
CREATE TABLE sessions (
    sess_id VARCHAR(64) PRIMARY KEY,
    sess_data TEXT,
    activity INT(11)
);
```

### 使用 Session

```php
// 启动 Session
session_start();

// 设置 Session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

// 获取 Session
$userId = $_SESSION['user_id'] ?? null;

// 删除 Session
unset($_SESSION['user_id']);

// 销毁 Session
session_destroy();
```

---

## 日志服务

### Log 类（PSR-3）

```php
use \FLEA\Log;
use \Psr\Log\LogLevel;

// 日志记录
log_message('调试信息', LogLevel::DEBUG);
log_message('普通信息', LogLevel::INFO);
log_message('警告信息', LogLevel::WARNING);
log_message('错误信息', LogLevel::ERROR);

// 记录带上下文的数据
log_message('用户登录：{username}', LogLevel::INFO, [
    'username' => 'admin'
]);

// 记录对象
$user = ['id' => 1, 'name' => 'admin'];
log_message('用户数据：' . print_r($user, true), LogLevel::DEBUG);
```

### 日志配置

```php
'logEnabled' => true,
'logFileDir' => __DIR__ . '/../logs',
'logFilename' => 'app_' . date('Y-m-d') . '.log',
'logErrorLevel' => [
    LogLevel::ERROR,
    LogLevel::WARNING,
    LogLevel::INFO,
],
```

### 日志级别

```php
// PSR-3 日志级别
LogLevel::EMERGENCY    // 系统不可用
LogLevel::ALERT        // 需要立即行动
LogLevel::CRITICAL     // 严重情况
LogLevel::ERROR        // 错误
LogLevel::WARNING      // 警告
LogLevel::NOTICE       // 正常但重要的事件
LogLevel::INFO         // 信息
LogLevel::DEBUG        // 调试信息
```

---

## 辅助类

### Pager（分页器）

```php
use \FLEA\Helper\Pager;

$pager = new Pager($model, $conditions, $sort);
$pager->pageSize = 10;
$pager->setPage($page);
$result = $pager->exec();

// 属性
$pager->totalCount   // 总记录数
$pager->pageCount    // 总页数
$pager->pageSize     // 每页记录数
```

### FileUploader（文件上传）

```php
use \FLEA\Helper\FileUploader;

$uploader = new FileUploader();
$file = $uploader->upload('file_input_name', [
    'allowedTypes' => ['image/jpeg', 'image/png', 'image/gif'],
    'maxSize' => 2097152,  // 2MB
    'destDir' => './uploads/',
]);

if ($file) {
    echo '上传成功：' . $file->savedName;
} else {
    echo '上传失败：' . $uploader->getError();
}
```

### Image（图像处理）

```php
use \FLEA\Helper\Image;

// 打开图片
$img = Image::createFromFile('photo.jpg');

// 调整大小
$img->resize(200, 150);

// 添加水印
$img->addWatermark('watermark.png', 'bottom-right');

// 保存
$img->save('thumb.jpg');
```

---

## 开发最佳实践

### 1. 命名规范

```php
// 控制器：XxxController
class PostController extends Action {}
class UserController extends Action {}

// 模型：表名单数形式，首字母大写
class Post extends TableDataGateway {}
class User extends TableDataGateway {}

// 动作方法：action 前缀 + 驼峰式
public function actionIndex() {}
public function actionCreate() {}
public function actionEditPost() {}

// 视图文件：{controller}/{action}.php
App/View/post/index.php
App/View/post/create.php

// 变量：驼峰式
$userName = 'admin';
$postList = [];

// 常量：大写 + 下划线
define('MAX_PAGE_SIZE', 100);
```

### 2. 目录组织

```
App/
├── Config.php              # 配置文件
├── Controller/             # 控制器
│   ├── PostController.php
│   └── UserController.php
├── Model/                  # 模型
│   ├── Post.php
│   └── User.php
├── View/                   # 视图
│   ├── post/
│   │   ├── index.php
│   │   └── view.php
│   ├── user/
│   │   ├── login.php
│   │   └── profile.php
│   └── layouts/            # 布局文件
│       └── main.php
└── Helper/                 # 辅助函数（可选）
    └── functions.php
```

### 3. 代码组织

```php
<?php
namespace App\Controller;

// 1. use 声明（按字母顺序）
use \FLEA\Controller\Action;
use App\Model\Post;

// 2. 类定义
class PostController extends Action
{
    // 3. 常量

    // 4. 静态属性

    // 5. 实例属性（可见性分组：public → protected → private）
    public $view;
    protected $postModel;
    private $cache;

    // 6. 构造函数
    public function __construct()
    {
        parent::__construct('Post');
        $this->postModel = new Post();
    }

    // 7. 公有方法（动作方法优先）
    public function actionIndex() {}
    public function actionView() {}

    // 8. 受保护的方法
    protected function validateData() {}

    // 9. 私有方法
    private function getCache() {}
}
```

### 4. 安全实践

```php
// ========================
// XSS 防护
// ========================
// 转义输出
echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

// ========================
// SQL 注入防护
// ========================
// 使用参数化查询（框架已内置）
$posts = $postModel->findAll(['id' => $id]);  // 安全

// ========================
// CSRF 防护
// ========================
// 生成 Token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 表单中添加 Token
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// 验证 Token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    throw new \Exception('CSRF 验证失败');
}

// ========================
// 文件上传安全
// ========================
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    throw new \Exception('不允许的文件类型');
}

// 重命名文件
$newName = uniqid() . '_' . basename($_FILES['file']['name']);
```

### 5. 性能优化

```php
// ========================
// 禁用不需要的关联
// ========================
$posts = $postModel->findAll(
    ['status' => 1],
    'created_at DESC',
    [10, 0],
    '*',
    false  // 禁用关联查询
);

// ========================
// 只查询需要的字段
// ========================
$posts = $postModel->findAll(
    ['status' => 1],
    null,
    null,
    'id,title,created_at'  // 避免 SELECT *
);

// ========================
// 使用缓存
// ========================
'viewConfig' => [
    'enableCache' => true,
    'cacheLifeTime' => 3600,
],

// ========================
// 延迟加载
// ========================
// 只在需要时才查询关联数据
$post = $postModel->find($id, null, '*', false);
if ($needComments) {
    $comments = $commentModel->findAll(['post_id' => $id]);
}
```

### 6. 错误处理

```php
// 使用 try-catch 处理异常
try {
    $post = $this->postModel->find($id);
    if (!$post) {
        throw new \FLEA\Exception\InvalidArguments('文章不存在');
    }
} catch (\FLEA\Exception\InvalidArguments $e) {
    log_message($e->getMessage(), \Psr\Log\LogLevel::WARNING);
    $this->view->assign('error', $e->getMessage());
    $this->view->display('error.php');
    return;
} catch (\Exception $e) {
    log_message($e->getMessage(), \Psr\Log\LogLevel::ERROR);
    throw $e;  // 生产环境记录日志后显示友好错误
}
```

---

## 常见问题

### 1. 数据库连接失败

**问题**：无法连接到数据库

**解决**：
1. 检查 `App/Config.php` 中的 `dbDSN` 配置
2. 确认 MySQL 服务已启动
3. 检查数据库用户权限
4. 确认数据库存在

```php
'dbDSN' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'login' => 'root',
    'password' => '正确的密码',
    'database' => '正确的数据库名',
],
```

### 2. 缓存目录权限问题

**问题**：缓存文件无法写入

**解决**：
```bash
chmod -R 777 cache/
chown -R www-data:www-data cache/
```

### 3. URL 重写不工作

**Apache 配置**：
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

**Nginx 配置**：
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 4. 控制器找不到

**问题**：访问时提示控制器不存在

**解决**：
1. 检查控制器类名是否正确（首字母大写，Controller 后缀）
2. 检查命名空间是否正确
3. 检查 Composer 自动加载配置
4. 确认 URL 参数正确

```php
// 正确的控制器
namespace App\Controller;
class PostController extends Action {}

// URL 访问
index.php?controller=Post&action=index
```

### 5. 视图文件找不到

**问题**：提示视图文件不存在

**解决**：
1. 检查视图文件路径：`App/View/{controller}/{action}.php`
2. 控制器名称和视图目录名对应（小写）
3. 检查 `viewConfig` 中的 `templateDir` 配置

### 6. 关联查询不工作

**问题**：关联数据为空或报错

**解决**：
1. 检查关联定义是否正确
2. 确认外键字段存在
3. 检查关联表数据
4. 确认是否启用了关联查询（find/findAll 的第 4/5 个参数）

```php
// 启用关联
$post = $postModel->find($id, null, '*', true);
$comments = $post['comments'];
```

---

## 附录

### A. 常量定义

```php
// URL 模式
URL_STANDARD     // 标准模式 (?controller=X&action=Y)
URL_PATHINFO     // PATHINFO 模式 (/index.php/X/Y)
URL_REWRITE      // URL 重写模式 (/X/Y)

// 关联类型
HAS_ONE          // 一对一
HAS_MANY         // 一对多
BELONGS_TO       // 从属
MANY_TO_MANY     // 多对多

// 日志级别（PSR-3）
LogLevel::DEBUG
LogLevel::INFO
LogLevel::NOTICE
LogLevel::WARNING
LogLevel::ERROR
LogLevel::CRITICAL
LogLevel::ALERT
LogLevel::EMERGENCY
```

### B. 辅助函数

```php
// 日志记录
log_message($message, $level = LogLevel::DEBUG, $context = [])

// 翻译（多语言）
_T($key, $language = null)
_ET($errorId)

// 加载辅助文件
\FLEA::loadHelper('array')
\FLEA::loadHelper('string')
\FLEA::loadHelper('file')
```

### C. 相关文档

- [SPEC.md](SPEC.md) - 框架规格说明书
- [README.md](README.md) - 项目说明
- [CHANGES.md](CHANGES.md) - 框架修改记录
- [GIT_COMMIT.md](GIT_COMMIT.md) - Git 提交记录
