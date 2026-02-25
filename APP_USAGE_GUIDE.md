# TableDataGateway::findAll 方法使用说明

## limit 参数使用规则

`findAll` 方法的 `$limit` 参数支持两种类型：

### 规则1：单个数值（限制记录数）
当只需要限制返回的记录数量，不需要分页时使用：

```php
// 只返回前 10 条记录
$posts = $postModel->findAll(null, 'created_at DESC', 10);

// 只返回前 5 条记录
$posts = $postModel->findAll(null, null, 5);
```

**使用场景**：
- 获取最新的 N 条记录
- 获取排行榜前 N 名
- 获取热门文章等

### 规则2：数组（分页）
当需要分页查询时，使用数组格式：`[length, offset]`

```php
// 返回第 1 页（每页 10 条），即第 0-9 条
$page = 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;
$posts = $postModel->findAll(null, 'created_at DESC', [$pageSize, $offset]);

// 返回第 2 页（每页 10 条），即第 10-19 条
$page = 2;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;
$posts = $postModel->findAll(null, 'created_at DESC', [$pageSize, $offset]);

// 返回第 3 页（每页 20 条），即第 40-59 条
$page = 3;
$pageSize = 20;
$offset = ($page - 1) * $pageSize;
$posts = $postModel->findAll(null, 'created_at DESC', [$pageSize, $offset]);
```

**使用场景**：
- 分页显示数据列表
- 数据加载更多功能
- 翻页功能

## 在博客应用中的应用

在 `App/Model\Post` 的 `getPublishedPosts` 方法中：

```php
public function getPublishedPosts($limit = 10, $offset = 0)
{
    // 直接传递参数给 findAll，不需要任何额外处理
    // findAll 方法内部会自动处理单个数值或数组的两种情况
    return $this->findAll(
        array('status' => 1),
        'created_at DESC',
        [$limit, $offset]
    );
}
```

**在控制器中调用**：

```php
public function actionIndex()
{
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $pageSize = 10;
    $offset = ($page - 1) * $pageSize;

    // 传入数组格式进行分页查询
    $posts = $this->postModel->getPublishedPosts($pageSize, $offset);
    $total = $this->postModel->getTotalCount();
    $totalPages = ceil($total / $pageSize);

    $this->view->assign('posts', $posts);
    $this->view->assign('page', $page);
    $this->view->assign('totalPages', $totalPages);
    $this->view->assign('total', $total);

    $this->view->display('post/index.php');
}
```

## 正确的调用方式

### 方式1：限制记录数
```php
$posts = $postModel->getPublishedPosts(10, 0);
// 传递 10 给 $limit, 0 给 $offset
// findAll 会收到 [10, 0]，返回前 10 条
```

### 方式2：分页查询
```php
$page = 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

$posts = $postModel->getPublishedPosts($pageSize, $offset);
// 传递 10 给 $limit, 0 给 $offset
// findAll 会收到 [10, 0]，返回第 0-9 条
```

## 总结

| 场景 | limit 参数格式 | 示例 | 含义 |
|------|----------------|------|------|
| 限制数量 | 单个数值 | `10` | 返回最多 10 条记录 |
| 分页查询 | 数组 `[length, offset]` | `[10, 20]` | 从第 20 条开始，返回 10 条记录 |

## 常见错误

❌ **错误理解**：
- ❌ 认为 `getPublishedPosts` 需要判断 `is_array($limit)` 来处理两种情况
- ❌ 认为 `getPublishedPosts` 需要单独处理 `$offset` 参数
- ❌ 在上层方法中添加额外的逻辑

✅ **正确理解**：
- ✅ `findAll` 方法内部已经支持单个数值或数组
- ✅ `getPublishedPosts` 只需要将 `$limit` 和 `$offset` 组合成数组传递给 `findAll`
- ✅ 上层方法保持简洁，不需要额外处理逻辑

## 记忆原则

1. **相信底层实现**：`findAll` 已经支持复合类型，不需要在上层重复处理
2. **简单直接**：直接传递参数，不要自己脑补复杂场景
3. **理解需求第一**：确保完全理解需求后再开始实现
4. **不要过度设计**：简单直接往往更好
