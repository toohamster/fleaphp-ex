# App 目录改动记录

本文档记录对 App 目录的所有修改。

---

## 2026-02-26

### feat: 建立 Post 和 Comment 模型关联关系并优化控制器查询

#### 修改文件
- `App/Model/Post.php`
- `App/Model/Comment.php`
- `App/Controller/PostController.php`

#### 新增关联关系

**Post.php - 添加 `$hasMany` 关联:**
```php
public $hasMany = array(
    array(
        'tableClass' => Comment::class,
        'foreignKey' => 'post_id',
        'mappingName' => 'comments',
    ),
);
```

**Comment.php - 添加 `$belongsTo` 关联:**
```php
public $belongsTo = array(
    array(
        'tableClass' => Post::class,
        'foreignKey' => 'post_id',
        'mappingName' => 'post',
    ),
);
```

#### 控制器优化

**actionView() - 利用关联减少数据库查询:**
```php
// 优化前：3 次查询
$post = $this->postModel->getPostById($id);
$comments = $this->commentModel->getCommentsByPostId($id);
$commentCount = $this->commentModel->getCommentCount($id);

// 优化后：1 次查询
$post = $this->postModel->find($id, null, '*', true);
$comments = isset($post['comments']) ? $post['comments'] : array();
$commentCount = count($comments);
```

#### Post.php 修改

**getPublishedPosts() - 禁用关联查询:**
```php
// 列表页不需要加载评论数据，避免不必要的查询开销
return $this->findAll(
    array('status' => 1),
    'created_at DESC',
    [$limit, $offset],
    '*',
    false  // 不查询关联数据
);
```

#### 优化效果

| 方法 | 优化前查询次数 | 优化后查询次数 |
|------|----------------|----------------|
| actionIndex() | 2 次 | 2 次（不变） |
| actionView() | 3 次 | 1 次 |

---

## 2026-02-26

### fix: 修复模型中 TableDataGateway 方法调用错误及移除冗余时间戳设置

#### 修改文件
- `App/Model/Post.php`
- `App/Model/Comment.php`

#### 问题描述

**问题 1: `remove()` 方法参数类型错误**

`TableDataGateway::remove()` 方法签名：
```php
public function remove(array &$row, bool $removeLink = true): bool
```

原代码错误地传递了主键值（int）而不是数组：
```php
// ❌ 错误：$id 是 int，但 remove() 期望 array
return $this->remove($id);
```

**问题 2: 冗余的时间戳设置**

`TableDataGateway` 类已自动处理时间戳字段：
- `$createdTimeFields = array('CREATED', 'CREATED_ON', 'CREATED_AT')` - create 时自动填充
- `$updatedTimeFields = array('UPDATED', 'UPDATED_ON', 'UPDATED_AT')` - create 和 update 时自动填充

原代码手动设置时间是冗余的：
```php
// ❌ 冗余代码
$data['created_at'] = date('Y-m-d H:i:s');
$data['updated_at'] = date('Y-m-d H:i:s');
```

#### 修复内容

**Post.php:**

1. `updatePost($id, $data)` - 改用 `updateByConditions()`:
```php
// ✅ 修复后
public function updatePost($id, $data)
{
    return $this->updateByConditions([$this->primaryKey => $id], $data);
}
```

2. `deletePost($id)` - 改用 `removeByPkv()`:
```php
// ✅ 修复后
public function deletePost($id)
{
    return $this->removeByPkv($id);
}
```

3. `createPost($data)` - 移除冗余时间戳:
```php
// ✅ 修复后
public function createPost($data)
{
    return $this->create($data);
}
```

**Comment.php:**

1. `deleteComment($id)` - 改用 `removeByPkv()`:
```php
// ✅ 修复后
public function deleteComment($id)
{
    return $this->removeByPkv($id);
}
```

2. `createComment($data)` - 移除冗余时间戳（保留 status 设置）:
```php
// ✅ 修复后
public function createComment($data)
{
    $data['status'] = 1; // 自动审核通过
    return $this->create($data);
}
```

#### 方法说明

| 方法 | 用途 | 参数 |
|------|------|------|
| `remove()` | 删除单条记录 | `array &$row` - 完整的记录数组 |
| `removeByPkv()` | 根据主键值删除 | `$pkv` - 主键值 |
| `update()` | 更新记录 | `array &$row` - 必须包含主键 |
| `updateByConditions()` | 根据条件更新 | `$conditions`, `$data` |

#### 验证
- ✅ 语法检查通过
- ✅ 方法调用与父类签名一致
- ✅ 移除了冗余代码

---

## 2026-02-25 - 修复 getPublishedPosts 方法参数传递错误

### 修改文件
- `App/Model/Post.php`

### 问题描述
`getPublishedPosts` 方法调用 `findAll` 时，参数传递有误：
```php
// ❌ 错误的调用
return $this->findAll(
    array('status' => 1),
    'created_at DESC',
    $limit,    // 会传给 findAll 的 $fields 参数
    $offset    // 会传给 findAll 的 $queryLinks 参数
);
```

`findAll` 的参数顺序是：
1. `$conditions`
2. `$sort`
3. `$limit`
4. `$fields`
5. `$queryLinks`

### 修复内容

```php
// ✅ 正确的调用
return $this->findAll(
    array('status' => 1),
    'created_at DESC',
    [$limit, $offset]  // 作为数组传给 $limit 参数
);
```

### 原理说明
`findAll` 方法的 `$limit` 参数支持两种类型：
- **单个数值**：限制记录数量，如 `findAll(null, null, 10)`
- **数组**：分页查询，如 `findAll(null, null, [10, 20])`

`getPublishedPosts` 方法接收 `$limit` 和 `$offset` 两个参数，需要将它们组合成数组传递给 `findAll`。

### 调用方式
在控制器中使用：
```php
// 分页查询
$page = 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;
$posts = $this->postModel->getPublishedPosts($pageSize, $offset);
```

### 验证
- ✅ 语法检查通过
- ✅ 参数传递正确
- ✅ 符合 `findAll` 方法的设计规范

---


## 2026-02-25 - 设置博客应用使用 Simple 视图引擎

### 修改文件
- `App/Config.php` - 应用配置文件
- `App/Controller/PostController.php` - 文章控制器

### Config.php 修改

添加了视图配置:
```php
'view' => \FLEA\View\Simple::class,
'viewConfig' => array(
    'templateDir' => __DIR__ . '/View',
    'cacheDir' => __DIR__ . '/../cache',
    'cacheLifeTime' => 900,
    'enableCache' => false,
),
```

**修改前**:
```php
'view' => 'PHP',
```

**修改后**:
```php
'view' => \FLEA\View\Simple::class,
'viewConfig' => array(
    'templateDir' => __DIR__ . '/View',
    'cacheDir' => __DIR__ . '/../cache',
    'cacheLifeTime' => 900,
    'enableCache' => false,
),
```

### PostController.php 修改

添加了视图支持:

```php
/**
 * @var \FLEA\View\Simple
 */
public $view;

/**
 * 构造函数
 */
public function __construct()
{
    parent::__construct('Post');
    $this->postModel = new Post();
    $this->commentModel = new Comment();
    $this->view = $this->_getView();  // 获取视图对象
}
```

### 视图文件位置

模板文件位于:
- `App/View/post/index.php` - 文章列表页
- `App/View/post/view.php` - 文章详情页
- `App/View/post/create.php` - 创建文章页
- `App/View/post/edit.php` - 编辑文章页

### 配置说明

- **templateDir**: 模板文件目录,指向 `App/View/`
- **cacheDir**: 缓存文件目录,指向 `cache/`
- **cacheLifeTime**: 缓存过期时间(秒)
- **enableCache**: 是否启用缓存(开发环境关闭)

### 验证

- ✅ 配置文件语法正确
- ✅ 控制器可以正常使用视图
- ✅ 模板文件路径正确

---

