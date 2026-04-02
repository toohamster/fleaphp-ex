# FLEA\Helper\Str 变更日志

## 2026-04-02

### 新增

- `Str::extract()` 方法：从字符串中提取命名参数
  - 支持自定义分隔符（默认 `{}`）
  - 支持忽略大小写匹配
  - 支持空白压缩
  - 支持去除提取值的首尾空格

### 使用示例

```php
use FLEA\Helper\Str;

// 基本用法
$result = Str::extract('380-250-80-j', '{width}-{height}-{quality}-{format}');
// ['width' => '380', 'height' => '250', 'quality' => '80', 'format' => 'j']

// 提取 URL 路径
$result = Str::extract('/2012/08/12/test.html', '/{year}/{month}/{day}/{title}.html');
// ['year' => '2012', 'month' => '08', 'day' => '12', 'title' => 'test']

// 自定义分隔符
$result = Str::extract('The time is 4:35pm', 'The time is :time', ['delimiters' => [':', '']]);
// ['time' => '4:35pm']

// 忽略大小写
$result = Str::extract('HELLO World', 'hello {name}', ['case_insensitive' => true]);
// ['name' => 'World']

// 压缩空白
$result = Str::extract('hello   world', 'hello {name}', ['collapse_whitespace' => true]);
// ['name' => 'world']
```
