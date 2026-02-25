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

