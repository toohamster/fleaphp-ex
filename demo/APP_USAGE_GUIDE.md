# 博客应用使用手册 v2.0

本手册描述基于 FleaPHP v2.0 框架开发的博客示例应用，涵盖项目结构、配置、启动流程和核心代码说明。

---

## 目录结构

```
demo/
├── .env                    # 环境配置（复制 .env.example 修改）
├── .env.example            # 环境配置示例
├── blog.sql                # 数据库初始化脚本
├── public/
│   └── index.php           # Web 入口文件
└── App/
    ├── Config.php          # 应用配置
    ├── Controller/
    │   └── PostController.php   # 文章控制器
    ├── Model/
    │   ├── Post.php             # 文章模型
    │   └── Comment.php          # 评论模型
    └── View/
        └── post/
            ├── index.php        # 文章列表页
            ├── view.php         # 文章详情页
            ├── create.php       # 创建文章页
            └── edit.php         # 编辑文章页
```

---

## 快速开始

### 1. 安装依赖

在项目根目录执行：

```bash
php74 ~/bin/composer.phar install
```

### 2. 配置环境变量

复制环境配置示例文件：

```bash
cp demo/.env.example demo/.env
```

编辑 `demo/.env` 文件，配置数据库连接：

```env
# 应用环境
APP_ENV=local
APP_DEBUG=true

# 数据库配置
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=your_password
DB_DATABASE=blog
```

### 3. 初始化数据库

```bash
mysql -u root -p < demo/blog.sql
```

`blog.sql` 创建以下数据表：

**posts 表**：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| title | VARCHAR(255) | 文章标题 |
| content | TEXT | 文章内容 |
| author | VARCHAR(100) | 作者 |
| created_at | DATETIME | 创建时间（自动填充） |
| updated_at | DATETIME | 更新时间（自动更新） |
| status | TINYINT | 0=草稿，1=发布 |

**comments 表**：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| post_id | INT | 文章 ID（外键，级联删除） |
| author | VARCHAR(100) | 评论者 |
| email | VARCHAR(255) | 邮箱（可选） |
| content | TEXT | 评论内容 |
| created_at | DATETIME | 创建时间（自动填充） |
| status | TINYINT | 0=待审核，1=已审核 |

### 4. 启动开发服务器

在项目根目录执行：

```bash
php bin/flea-cli --project-dir=demo
```

或者在 `demo` 目录下执行：

```bash
cd demo && php ../bin/flea-cli
```

访问：`http://127.0.0.1:8081/index.php`

---

## 入口文件 public/index.php

```php
<?php
/**
 * 博客应用 Web 入口
 */

// 加载 Composer 自动加载器
require_once __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
\FLEA::loadEnv(__DIR__ . '/../.env');

// 加载应用配置
\FLEA::loadAppInf(__DIR__ . '/../App/Config.php');

// 启动 MVC
\FLEA::runMVC();
```

启动流程：
1. 加载 Composer 自动加载器
2. 加载 `.env` 和 `.env.{APP_ENV}` 环境变量
3. 加载 `App/Config.php` 应用配置
4. 启动 MVC 分发器处理请求

---

## 应用配置 App/Config.php

```php
<?php
/**
 * 博客应用配置
 */

return [
    // 默认控制器和动作
    'defaultController' => 'Post',
    'defaultAction' => 'index',

    // 调度器
    'dispatcher' => \FLEA\Dispatcher\Simple::class,

    // 视图引擎
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,  // 开发环境关闭缓存
    ],

    // 错误处理
    'displayErrors' => env('APP_DEBUG', true),
    'friendlyErrorsMessage' => true,
];
```

**配置说明**：

| 配置项 | 说明 | 默认值 |
|--------|------|--------|
| `defaultController` | 默认控制器 | `Post` |
| `defaultAction` | 默认动作 | `index` |
| `dispatcher` | 调度器类 | `Simple` |
| `view` | 视图引擎类 | `Simple` |
| `displayErrors` | 是否显示详细错误 | `true`（开发） |

---

## URL 路由

博客应用使用路由器模式，路由定义在 `App/routes.php` 文件中。

### 路由定义

```php
// 文章相关路由
\FLEA\Router::get('/post', 'PostController@index')->name('post.index');
\FLEA\Router::get('/post/create', 'PostController@create')->name('post.create');
\FLEA\Router::get('/post/{id:\d+}', 'PostController@view')->name('post.view');
\FLEA\Router::any('/post/{id:\d+}/edit', 'PostController@edit')->name('post.edit');
\FLEA\Router::post('/post/{id:\d+}/delete', 'PostController@delete')->name('post.delete');
\FLEA\Router::post('/post/comment', 'PostController@comment')->name('post.comment');

// 兜底路由（框架自动注册）
// /{controller}/{action} → {Controller}Controller@action
// /{controller} → {Controller}Controller@index
// / → PostController@index
```

### URL 格式

| URL | 对应方法 |
|-----|---------|
| `/post` | `PostController::actionIndex()` |
| `/post/create` | `PostController::actionCreate()` |
| `/post/1` | `PostController::actionView()` |
| `/post/1/edit` | `PostController::actionEdit()` |
| `/post/1/delete` (POST) | `PostController::actionDelete()` |

### 命名路由

```php
// 生成 URL
$url = \FLEA\Router::urlFor('post.view', ['id' => 1]);
// 输出：/post/1

$url = \FLEA\Router::urlFor('post.edit', ['id' => 2]);
// 输出：/post/2/edit
```

---

## 控制器 PostController

**文件**：`App/Controller/PostController.php`
**命名空间**：`App\Controller`
**继承**：`\FLEA\Controller\Action`

### 属性

```php
protected Post $postModel;       // 文章模型
protected Comment $commentModel; // 评论模型
```

### 构造函数

```php
public function __construct()
{
    parent::__construct('Post');
    $this->postModel = new Post();
    $this->commentModel = new Comment();
}
```

### 动作方法

#### actionIndex() — 文章列表

分页显示已发布文章，每页 10 条。

```php
public function actionIndex(): void
```

- 获取当前页码 `page`
- 调用 `$this->postModel->getPublishedPosts($pageSize, $offset)`
- 调用 `$this->postModel->getTotalCount()` 获取总数
- 传递 `posts, page, totalPages, total` 给视图

#### actionView() — 文章详情

显示文章内容及其评论列表。

```php
public function actionView(): void
```

- 获取文章 ID（从路由参数）
- 调用 `$this->postModel->find($id, null, '*', true)` 查询并加载关联评论
- 评论数据从 `$post['comments']` 获取（一次查询）
- 传递 `post, comments, commentCount` 给视图

#### actionCreate() — 创建文章

GET 显示表单，POST 处理提交。

```php
public function actionCreate(): void
```

- GET 请求：显示创建表单
- POST 请求：
  - 获取表单数据：`title, content, author`
  - 设置 `status = 1`（发布状态）
  - 调用 `$this->postModel->createPost($data)` 创建
  - 时间戳由框架自动填充

#### actionEdit() — 编辑文章

GET 显示编辑表单，POST 处理更新。

```php
public function actionEdit(): void
```

- GET 请求：显示编辑表单（预填内容）
- POST 请求：
  - 获取文章 ID
  - 获取表单数据：`title, content, author`
  - 调用 `$this->postModel->updatePost($id, $data)` 更新

#### actionDelete() — 删除文章

删除指定文章。

```php
public function actionDelete(): void
```

- 获取文章 ID
- 调用 `$this->postModel->deletePost($id)` 删除
- 重定向到文章列表页

---

## 模型

### Post 模型

**文件**：`App/Model/Post.php`
**命名空间**：`App\Model`
**继承**：`\FLEA\Db\TableDataGateway`
**数据表**：`posts`
**主键**：`id`

#### 关联关系

```php
public array $hasMany = [
    [
        'tableClass' => Comment::class,
        'foreignKey' => 'post_id',
        'mappingName' => 'comments',
    ],
];
```

一篇文章有多个评论。

#### 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| `getPublishedPosts` | `(int $limit = 10, int $offset = 0): array` | 获取已发布文章 |
| `getPostById` | `(int $id): ?array` | 根据 ID 获取文章 |
| `createPost` | `(array $data): int` | 创建文章，返回 ID |
| `updatePost` | `(int $id, array $data): bool` | 更新文章 |
| `deletePost` | `(int $id): bool` | 删除文章 |
| `getTotalCount` | `(): int` | 获取已发布文章总数 |

### Comment 模型

**文件**：`App/Model/Comment.php`
**命名空间**：`App\Model`
**继承**：`\FLEA\Db\TableDataGateway`
**数据表**：`comments`
**主键**：`id`

#### 关联关系

```php
public array $belongsTo = [
    [
        'tableClass' => Post::class,
        'foreignKey' => 'post_id',
        'mappingName' => 'post',
    ],
];
```

一个评论属于一篇文章。

#### 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| `getCommentsByPostId` | `(int $postId): array` | 获取文章的已审核评论 |
| `createComment` | `(array $data): int` | 创建评论，返回 ID |
| `deleteComment` | `(int $id): bool` | 删除评论 |
| `getCommentCount` | `(int $postId): int` | 获取评论数 |

---

## 视图

视图文件位于 `App/View/post/`，使用 `\FLEA\View\Simple` 模板引擎。

### 模板语法

控制器传递数据：

```php
$this->getView()->assign('posts', $posts);
$this->getView()->display('post/index.php');
```

模板中使用变量：

```php
<?php foreach ($posts as $post): ?>
    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
<?php endforeach; ?>
```

### 视图文件

| 文件 | 传入变量 | 功能 |
|------|----------|------|
| `index.php` | `$posts, $page, $totalPages, $total` | 文章列表 + 分页 |
| `view.php` | `$post, $comments, $commentCount` | 文章详情 + 评论列表 + 评论表单 |
| `create.php` | 无 | 创建文章表单 |
| `edit.php` | `$post` | 编辑文章表单（预填内容） |

---

## 框架特性

### 时间戳自动填充

`TableDataGateway` 自动处理时间戳：
- `create()` 时填充 `created_at` 和 `updated_at`
- `update()` 时填充 `updated_at`

### 关联查询

使用 `hasMany` 关联一次获取文章和评论：

```php
// 第 4 个参数 true 表示加载关联数据
$post = $this->postModel->find($id, null, '*', true);
$comments = $post['comments'] ?? [];
```

### Context 上下文

v2.0 新增的 Context 组件替代传统 Session：

```php
// 存储数据
flea_context()->set('user_id', 123);

// 读取数据
$userId = flea_context()->get('user_id');
```

---

## 常见问题

### 数据库连接失败

检查 `.env` 中的数据库配置是否正确，确保 MySQL 服务已启动。

### 缓存目录权限

确保 `cache/` 目录可写：

```bash
chmod -R 777 cache/
```

### 自动加载问题

重新生成自动加载文件：

```bash
php74 ~/bin/composer.phar dump-autoload
```

---

## 参考文档

- [USER_GUIDE.md](../USER_GUIDE.md) - 框架用户手册
- [SPEC.md](../SPEC.md) - 框架规格说明书
