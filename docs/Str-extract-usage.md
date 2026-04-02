# Str::extract() 使用指南

## 功能说明

`Str::extract()` 方法用于从字符串中提取命名参数，无需手写正则表达式。

**核心优势：**
- 不用写正则表达式
- 使用模板语法，直观易懂
- 自动返回关联数组

---

## 基本用法

### 语法

```php
use FLEA\Helper\Str;

$result = Str::extract(string $string, string $pattern, array $options = []);
```

**参数说明：**
| 参数 | 说明 | 默认值 |
|------|------|--------|
| `$string` | 要解析的目标字符串 | - |
| `$pattern` | 模式模板（如 `{name}-{age}`） | - |
| `$options` | 选项数组（见下文） | `[]` |

**返回值：**
- 成功：关联数组 `['name' => '值', 'age' => '值']`
- 失败：空数组 `[]`

---

## 使用示例

### 1. 基本提取

```php
use FLEA\Helper\Str;

// 提取连字符分隔的参数
$result = Str::extract('380-250-80-j', '{width}-{height}-{quality}-{format}');
print_r($result);

/*
输出：
Array
(
    [width] => 380
    [height] => 250
    [quality] => 80
    [format] => j
)
*/
```

---

### 2. URL 路径解析

```php
// 解析日期路径
$result = Str::extract('/2012/08/12/test.html', '/{year}/{month}/{day}/{title}.html');
print_r($result);

/*
输出：
Array
(
    [year] => 2012
    [month] => 08
    [day] => 12
    [title] => test
)
*/

// 解析文章 ID
$result = Str::extract('/post/123', '/post/{id}');
// ['id' => '123']

// 解析用户 ID
$result = Str::extract('/users/456/profile', '/users/{userId}/profile');
// ['userId' => '456']
```

---

### 3. 特殊字符提取

```php
// 提取邮箱和 URL
$result = Str::extract(
    'John Doe <john@example.com> (http://example.com)',
    '{name} <{email}> ({url})'
);
print_r($result);

/*
输出：
Array
(
    [name] => John Doe
    [email] => john@example.com
    [url] => http://example.com
)
*/
```

---

### 4. 自定义分隔符

```php
// 使用冒号作为分隔符（空结束分隔符）
$result = Str::extract(
    'The time is 4:35pm here at Lima, Peru',
    'The time is :time here at :city',
    ['delimiters' => [':', '']]
);
print_r($result);

/*
输出：
Array
(
    [time] => 4:35pm
    [city] => Lima, Peru
)
*/
```

---

### 5. 忽略大小写

```php
// 模式与字符串大小写不一致
$result = Str::extract(
    'Convert 1500 Grams to Kilograms',
    'convert {quantity} {from_unit} to {to_unit}',
    ['case_insensitive' => true]
);
print_r($result);

/*
输出：
Array
(
    [quantity] => 1500
    [from_unit] => Grams
    [to_unit] => Kilograms
)
*/
```

---

### 6. 压缩空白字符

```php
// 字符串中有多余空格
$result = Str::extract(
    'from   4th  October   to   10th  October',
    'from {from} to {to}',
    ['collapse_whitespace' => true, 'strip_values' => true]
);
print_r($result);

/*
输出：
Array
(
    [from] => 4th October
    [to] => 10th October
)
*/
```

---

### 7. 组合选项

```php
// 多个选项同时使用
$result = Str::extract(
    'CONVERT   1500  Grams   TO   Kilograms',
    'convert {amount} {from} to {to}',
    [
        'case_insensitive' => true,      // 忽略大小写
        'collapse_whitespace' => true,   // 压缩空白
        'strip_values' => true,          // 去除首尾空格
    ]
);
print_r($result);

/*
输出：
Array
(
    [amount] => 1500
    [from] => Grams
    [to] => Kilograms
)
*/
```

---

## 选项说明

| 选项 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `delimiters` | array | `['{', '}']` | 自定义分隔符 |
| `strip_values` | bool | `false` | 去除提取值的首尾空格 |
| `case_insensitive` | bool | `false` | 忽略大小写匹配 |
| `collapse_whitespace` | bool | `false` | 压缩连续空白为单个空格 |

---

## 失败场景

以下情况返回空数组 `[]`：

```php
// 1. 模式不匹配（占位符只能匹配 \w+ 字符）
$result = Str::extract('hello-world', '{name}');
// []  因为 - 不在 \w 范围内

// 2. 分隔符无效
$result = Str::extract('test', '{name', ['delimiters' => ['{', '{']]);
// []  分隔符相同

// 3. 字符串为空
$result = Str::extract('', '{name}');
// []
```

---

## 实际应用场景

### 场景 1：路由参数提取

```php
class RouteExtractor
{
    public function parse($path, $routePattern)
    {
        return \FLEA\Helper\Str::extract($path, $routePattern);
    }
}

// 使用
$extractor = new RouteExtractor();
$params = $extractor->parse('/users/123/posts/456', '/users/{userId}/posts/{postId}');
// ['userId' => '123', 'postId' => '456']
```

---

### 场景 2：日志解析

```php
// 日志格式：[2024-01-15 10:30:45] INFO: User 123 logged in from 192.168.1.1
$logLine = '[2024-01-15 10:30:45] INFO: User 123 logged in from 192.168.1.1';

$result = Str::extract(
    $logLine,
    '[{datetime}] {level}: User {userId} logged in from {ip}'
);
print_r($result);

/*
输出：
Array
(
    [datetime] => 2024-01-15 10:30:45
    [level] => INFO
    [userId] => 123
    [ip] => 192.168.1.1
)
*/
```

---

### 场景 3：配置文件解析

```php
// 配置格式：DB_HOST=localhost:3306
$config = 'DB_HOST=localhost:3306';

$result = Str::extract($config, '{name}={host}:{port}', ['delimiters' => ['{', '}']]);
// ['name' => 'DB_HOST', 'host' => 'localhost', 'port' => '3306']
```

---

## 注意事项

1. **占位符命名规则**：只能使用字母、数字和下划线（`\w+`），且不能以数字开头
2. **匹配模式**：占位符默认非贪婪匹配（`.+?`）
3. **分隔符限制**：开始和结束分隔符不能相同（除非使用默认 `{}`）

---

## 参考链接

- [FLEA Helper 文档](USER_GUIDE.md)
- [SPEC.md](SPEC.md)
