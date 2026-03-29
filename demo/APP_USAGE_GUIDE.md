# App 博客应用使用手册

本手册基于 `App/` 目录下的实际代码编写，描述博客应用的结构、配置和使用方法。

---

## 目录结构

```
App/
├── Config.php                  # 应用配置
├── Controller/
│   └── PostController.php      # 文章控制器（含评论操作）
├── Model/
│   ├── Post.php                # 文章模型
│   └── Comment.php             # 评论模型
└── View/
    └── post/
        ├── index.php           # 文章列表页
        ├── view.php            # 文章详情页
        ├── create.php          # 创建文章页
        └── edit.php            # 编辑文章页
```

---

## 环境准备

### 1. 初始化数据库

```bash
mysql -u root -p < blog.sql
```

`blog.sql` 创建 `blog` 数据库及两张表：

**posts 表**：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| title | VARCHAR(255) | 文章标题 |
| content | TEXT | 文章内容 |
| author | VARCHAR(100) | 作者 |
| created_at | DATETIME | 创建时间（自动填充） |
| updated_at | DATETIME | 更新时间（自动更新） |
| status | TINYINT | 0=草稿, 1=发布 |

**comments 表**：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| post_id | INT | 文章ID（外键，级联删除） |
| author | VARCHAR(100) | 评论者 |
| email | VARCHAR(255) | 邮箱（可选） |
| content | TEXT | 评论内容 |
| created_at | DATETIME | 创建时间（自动填充） |
| status | TINYINT | 0=待审核, 1=已审核 |

### 2. 安装依赖

```bash
php74 ~/bin/composer.phar install
```

### 3. 修改数据库配置

编辑 `App/Config.php` 中的 `dbDSN` 部分：

```php
'dbDSN' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'login' => 'root',
    'password' => '11111111',
    'database' => 'blog',
    'charset' => 'utf8mb4',
],
```

### 4. 启动开发服务器

```bash
php74 -S 127.0.0.1:8081
```

访问 `http://127.0.0.1:8081/index.php`。

---

## 入口文件 index.php

```php
require_once 'vendor/autoload.php';
class_loader()->addPsr4('App\\', __DIR__ . '/App/');
\FLEA::loadAppInf('App/Config.php');
\FLEA::runMVC();
```

启动流程：
1. 加载 Composer 自动加载器
2. 注册 `App\` 命名空间到 `App/` 目录
3. 加载应用配置
4. 启动 MVC 分发

---

## 应用配置 App/Config.php

```php
return [
    // 数据库
    'dbDSN' => [...],

    // 路由：URL 中 controller 和 action 参数名
    'controllerAccessor' => 'controller',
    'actionAccessor' => 'action',
    'defaultController' => 'Post',      // 默认控制器
    'defaultAction' => 'index',          // 默认动作

    // URL 模式
    'urlMode' => URL_STANDARD,           // 标准查询字符串模式
    'urlBootstrap' => 'index.php',

    // 调度器
    'dispatcher' => \FLEA\Dispatcher\Simple::class,

    // 视图引擎
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',   // 模板目录
        'cacheDir' => __DIR__ . '/../cache',   // 缓存目录
        'cacheLifeTime' => 900,                // 缓存时间（秒）
        'enableCache' => false,                // 开发环境关闭缓存
    ],

    // 错误显示（开发环境）
    'displayErrors' => true,
    'friendlyErrorsMessage' => true,
    'displaySource' => true,
];
```

---

## URL 路由

格式：`index.php?controller={控制器名}&action={动作名}&参数=值`

| URL | 对应方法 |
|-----|---------|
| `?controller=Post&action=index` | PostController::actionIndex() |
| `?controller=Post&action=view&id=1` | PostController::actionView() |
| `?controller=Post&action=create` | PostController::actionCreate() |
| `?controller=Post&action=edit&id=1` | PostController::actionEdit() |
| `?controller=Post&action=delete&id=1` | PostController::actionDelete() |
| `?controller=Post&action=comment` | PostController::actionComment() |
| （无参数，使用默认值） | PostController::actionIndex() |

---

## 控制器 PostController

**文件**：`App/Controller/PostController.php`
**命名空间**：`App\Controller`
**继承**：`\FLEA\Controller\Action`

### 属性

```php
protected Post $postModel;       // 文章模型
protected Comment $commentModel; // 评论模型
public $view;                    // 视图对象（通过 $this->getView() 获取）
```

### 构造函数

```php
public function __construct()
{
    parent::__construct('Post');
    $this->postModel = new Post();
    $this->commentModel = new Comment();
    $this->view = $this->getView();
}
```

### 动作方法

#### actionIndex() — 文章列表

分页显示已发布文章，每页 10 条。

```php
public function actionIndex(): void
```

- 通过 `$_GET['page']` 获取页码
- 调用 `$this->postModel->getPublishedPosts($pageSize, $offset)` 获取文章
- 调用 `$this->postModel->getTotalCount()` 获取总数
- 传递 `posts, page, totalPages, total` 给视图

#### actionView() — 文章详情

显示文章内容及其评论列表，带评论表单。

```php
public function actionView(): void
```

- 通过 `$_GET['id']` 获取文章 ID
- 调用 `$this->postModel->find($id, null, '*', true)` 查询文章并加载关联评论
- 评论数据从关联结果 `$post['comments']` 中获取（一次查询完成）
- 传递 `post, comments, commentCount` 给视图

#### actionCreate() — 创建文章

GET 请求显示表单，POST 请求处理提交。

```php
public function actionCreate(): void
```

- POST 数据：`title, content, author`
- 自动设置 `status = 1`（发布状态）
- 调用 `$this->postModel->createPost($data)` 创建
- 时间戳由框架自动填充（`created_at`, `updated_at`）

#### actionEdit() — 编辑文章

GET 请求显示编辑表单（预填内容），POST 请求处理更新。

```php
public function actionEdit(): void
```

- 通过 `$_GET['id']` 获取文章 ID
- POST 数据：`title, content, author`
- 调用 `$this->postModel->updatePost($id, $data)` 更新

#### actionDelete() — 删除文章

通过 JavaScript confirm 弹窗确认后删除。

```php
public function actionDelete(): void
```

- 通过 `$_GET['id']` 获取文章 ID
- 需要 `$_GET['confirm'] === 'yes'` 才执行删除
- 调用 `$this->postModel->deletePost($id)` 删除

#### actionComment() — 添加评论

仅处理 POST 请求。

```php
public function actionComment(): void
```

- POST 数据：`post_id, author, email, content`
- 调用 `$this->commentModel->createComment($data)` 创建
- 评论自动设为已审核状态（`status = 1`）

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

一个文章有多个评论。当 `find()` 的 `$queryLinks` 参数为 `true` 时，返回结果会包含 `comments` 键。

#### 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| getPublishedPosts | `(int $limit = 10, int $offset = 0): array` | 获取已发布文章（不加载评论） |
| getPostById | `(int $id): ?array` | 根据 ID 获取文章 |
| createPost | `(array $data): int` | 创建文章，返回新记录 ID（失败返回 0） |
| updatePost | `(int $id, array $data): bool` | 更新文章 |
| deletePost | `(int $id): bool` | 删除文章 |
| getTotalCount | `(): int` | 获取已发布文章总数 |

**getPublishedPosts 分页示例**：

```php
// 第 1 页，每页 10 条
$posts = $postModel->getPublishedPosts(10, 0);

// 第 2 页，每页 10 条
$posts = $postModel->getPublishedPosts(10, 10);

// 第 3 页，每页 20 条
$posts = $postModel->getPublishedPosts(20, 40);
```

内部调用 `findAll()` 时将 `$limit` 和 `$offset` 组合为数组 `[$limit, $offset]` 传递，同时设置 `$queryLinks = false` 避免加载评论。

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

一个评论属于一个文章。

#### 方法

| 方法 | 签名 | 说明 |
|------|------|------|
| getCommentsByPostId | `(int $postId): array` | 获取文章的已审核评论（按时间正序） |
| createComment | `(array $data): int` | 创建评论（自动设置 status=1），返回新记录 ID |
| deleteComment | `(int $id): bool` | 删除评论 |
| getCommentCount | `(int $postId): int` | 获取文章的已审核评论数 |

---

## 视图

视图文件位于 `App/View/post/`，使用 `\FLEA\View\Simple` 模板引擎。

控制器通过 `$this->view->assign('变量名', $值)` 传递数据，通过 `$this->view->display('模板路径')` 渲染。模板中直接使用 `$变量名` 访问数据。

### 列表页 index.php

**传入变量**：`$posts`（文章数组）、`$page`（当前页码）、`$totalPages`（总页数）、`$total`（文章总数）

功能：
- 文章卡片列表，显示标题、作者、发布时间、内容摘要（前 200 字）
- 分页导航（页数 > 1 时显示）
- 顶部导航：首页 / 写文章

### 详情页 view.php

**传入变量**：`$post`（文章数据）、`$comments`（评论数组）、`$commentCount`（评论数）

功能：
- 文章标题、作者、发布时间、更新时间、正文内容
- 操作按钮：编辑文章 / 删除文章
- 评论列表（评论者、邮箱、时间、内容）
- 评论表单（昵称、邮箱、内容），提交到 `actionComment`

### 创建页 create.php

**传入变量**：无

功能：
- 表单字段：标题、作者（默认"匿名"）、内容
- 提交到 `actionCreate`（POST）

### 编辑页 edit.php

**传入变量**：`$post`（文章数据）

功能：
- 表单字段预填当前文章内容
- 提交到 `actionEdit`（POST）
- 导航包含"查看文章"链接

---

## 框架特性说明

### 时间戳自动填充

`TableDataGateway` 基类自动处理时间戳字段：
- `create()` 时自动填充 `created_at` 和 `updated_at`
- `update()` 时自动填充 `updated_at`

应用代码无需手动设置这些字段。

### 关联查询

文章详情页利用 `hasMany` 关联一次查询获取文章和评论：

```php
// 查询文章并加载关联数据（第 4 个参数 true 表示加载关联）
$post = $this->postModel->find($id, null, '*', true);
$comments = $post['comments'] ?? [];
```

列表页禁用关联查询以减少开销：

```php
// findAll 的第 5 个参数 false 表示不加载关联
return $this->findAll(['status' => 1], 'created_at DESC', [$limit, $offset], '*', false);
```

### findAll 的 limit 参数

`findAll()` 的 `$limit` 参数支持两种格式：

| 格式 | 示例 | 含义 |
|------|------|------|
| 单个数值 | `10` | 返回最多 10 条记录 |
| 数组 `[length, offset]` | `[10, 20]` | 从第 20 条开始，返回 10 条 |
