# App 目录改动记录

本文档记录对 App 目录的所有修改。

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

