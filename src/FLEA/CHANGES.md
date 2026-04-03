# FLEA View + Response 架构重构变更日志

## 2026-04-03

### View + Response 架构重构（v2.3.0）

**新增文件 (12 个):**

1. **View 接口**
   - `View/ViewInterface.php` - 视图顶层接口
   - `View/StreamingViewInterface.php` - 流式视图接口

2. **具体 View 类 (9 个)**
   - `View/FileTemplateView.php` - 文件模板视图
   - `View/JsonView.php` - JSON 数据视图
   - `View/CsvView.php` - CSV 导出视图
   - `View/RedirectView.php` - 重定向视图
   - `View/BinaryView.php` - 二进制文件视图
   - `View/SseView.php` - SSE 流式视图
   - `View/CallbackView.php` - 回调视图
   - `View/CallbackViewBuilder.php` - 回调视图构建器
   - `View/NullView.php` - 空视图（重构）

3. **渲染器组件 (2 个)**
   - `View/RendererConfig.php` - 渲染器配置类
   - `View/SimpleRenderer.php` - 简单 PHP 模板渲染器

4. **工厂类**
   - `View.php` - View 工厂类（12 个静态方法）

**修改文件 (7 个):**
- `Response.php` - 新增 `fromView()` 和 `send()` 方法
- `Dispatcher/Simple.php` - 新增 `handleActionResult()` 方法
- `FLEA.php` - 新增 `initViewRenderer()` 方法
- `View/NullView.php` - 重构为实现新接口
- `View/Simple.php` - 已删除
- `Helper/SendFile.php` - 已删除（功能由 `View::binary()` 覆盖）
- `Helper/ImgCode.php` - 重构，新增 `generate()`, `getImageData()`, `getContentType()`, `hex2rgb()` 方法

**核心设计思想:**
- View 负责内容生成，Response 负责 HTTP 响应细节
- 支持 9 种具体 View 类，覆盖 HTML/JSON/CSV/Redirect/Binary/SSE/Callback 等场景
- 旧代码自动兼容（Dispatcher 兼容 void 返回）
- 新代码推荐使用 `View::html()` 等工厂方法

**迁移指南:**
```php
// 旧代码（已废弃）
$view = new \FLEA\View\Simple();
$view->assign('posts', $posts);
$view->display('post/index.php');

// 新代码（推荐）
return View::html('post/index.php', ['posts' => $posts]);
```

---

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
