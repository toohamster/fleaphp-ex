# View + Response 架构重构方案

**日期**：2026-04-03
**版本**：PHP 7.4 兼容版本
**状态**：待 review

---

## 背景

当前 `\FLEA\Response` 类的问题：
1. 静态方法（`success()`, `error()`）调用 `exit` 终止程序
2. 与中间件洋葱模型冲突，后置中间件无法执行
3. 视图渲染（`View::display()`）直接 echo，无法统一处理

---

## 核心设计思想

**View 负责内容生成，Response 负责 HTTP 响应细节**

```
Action → ViewInterface → Response → 浏览器
         (内容生成)      (HTTP 响应)
```

---

## 接口层次设计

```
                    ViewInterface
                    - getContentType(): string
                    - getContent(): string
                          │
          ┌───────────────┴───────────────┐
          │                               │
   StreamingViewInterface          (其他扩展)
   - stream(): void
          │
    ┌─────┴─────┐
    │           │
 SseView    ProgressView
```

---

## 接口定义

### 1. ViewInterface（顶层接口）

```php
<?php

namespace FLEA\View;

/**
 * 视图顶层接口
 *
 * 定义所有视图必须实现的基本方法
 */
interface ViewInterface
{
    /**
     * 获取内容类型
     *
     * @return string 如 'text/html', 'application/json', 'text/csv'
     */
    public function getContentType(): string;

    /**
     * 获取内容字符串
     *
     * @return string 内容（可以是文本或二进制数据）
     *                 对于重定向视图，返回空字符串（HTTP 重定向无 body）
     */
    public function getContent(): string;
}
```

---

### 2. StreamingViewInterface（流式视图）

```php
<?php

namespace FLEA\View;

/**
 * 流式视图接口
 *
 * 用于 SSE、实时推送等需要保持连接、持续输出的场景
 */
interface StreamingViewInterface extends ViewInterface
{
    /**
     * 流式发送内容
     *
     * 此方法由 Response 调用，负责控制整个响应生命周期
     * 包括：设置响应头、循环输出、处理连接断开等
     */
    public function stream(): void;
}
```

---

## 具体视图实现

### 1. FileTemplateView（文件模板视图）

```php
<?php

namespace FLEA\View;

/**
 * 文件模板视图
 *
 * 用于渲染任意类型的模板文件（HTML、XML、Markdown、专有 JSON 格式等）
 * 不局限于 HTML，可以是任何需要模板文件生成的内容
 *
 * 依赖 SimpleRenderer 进行实际渲染和缓存处理
 */
class FileTemplateView implements ViewInterface
{
    /**
     * @var string|null 模板文件路径
     */
    private $template = null;

    /**
     * @var array 视图变量
     */
    private $vars = [];

    /**
     * @var string 内容类型
     */
    private $contentType = 'text/html';

    /**
     * @var RendererConfig|null 渲染器配置
     */
    private $rendererConfig = null;

    /**
     * 构造函数
     *
     * @param string|null $template 模板文件路径
     * @param array $vars 视图变量
     * @param string $contentType 内容类型（默认 text/html）
     * @param RendererConfig|null $rendererConfig 渲染器配置
     */
    public function __construct(
        ?string $template = null,
        array $vars = [],
        string $contentType = 'text/html',
        ?RendererConfig $rendererConfig = null
    ) {
        $this->template = $template;
        $this->vars = $vars;
        $this->contentType = $contentType;
        $this->rendererConfig = $rendererConfig;
    }

    /**
     * 设置模板文件
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * 分配视图变量
     */
    public function assign($key, $value = null): self
    {
        if (is_array($key)) {
            $this->vars = array_merge($this->vars, $key);
        } else {
            $this->vars[$key] = $value;
        }
        return $this;
    }

    /**
     * 设置渲染器配置
     */
    public function setRendererConfig(RendererConfig $config): self
    {
        $this->rendererConfig = $config;
        return $this;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * 获取渲染后的内容
     *
     * 委托给 SimpleRenderer 进行实际渲染
     */
    public function getContent(): string
    {
        if ($this->template === null) {
            throw new \RuntimeException('Template not set');
        }

        return SimpleRenderer::render($this->template, $this->vars, $this->rendererConfig);
    }
}
```

---

### 1.5 RendererConfig（渲染器配置）

```php
<?php

namespace FLEA\View;

/**
 * 渲染器配置
 *
 * 封装模板渲染的配置项
 */
class RendererConfig
{
    /**
     * @var string|null 模板文件目录
     */
    public ?string $templateDir = null;

    /**
     * @var string 缓存文件目录
     */
    public string $cacheDir = './cache';

    /**
     * @var int 缓存有效期（秒）
     */
    public int $cacheLifetime = 900;

    /**
     * @var bool 是否启用缓存
     */
    public bool $enableCache = true;

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
```

**配置来源：**
```php
// demo/App/Config.php
return [
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,
    ],
];

// FLEA.php boot() 方法中
private static function initView(): void
{
    $viewConfig = (array) self::getAppInf('viewConfig');
    $rendererConfig = new RendererConfig($viewConfig);
    SimpleRenderer::configure($rendererConfig);
}
```

---

### 1.8 SimpleRenderer（简单 PHP 模板渲染器）

```php
<?php

namespace FLEA\View;

/**
 * 简单 PHP 模板渲染器
 *
 * 专注模板渲染和缓存，不依赖任何状态
 * 使用 extract() + include() 渲染 PHP 原生模板
 */
class SimpleRenderer
{
    /**
     * @var RendererConfig|null 全局配置
     */
    private static ?RendererConfig $config = null;

    /**
     * 设置全局配置
     *
     * @param RendererConfig $config 配置对象
     */
    public static function configure(RendererConfig $config): void
    {
        self::$config = $config;
    }

    /**
     * 渲染模板
     *
     * @param string $template 模板文件路径（绝对路径或相对路径）
     * @param array $vars 视图变量
     * @param RendererConfig|null $config 临时配置（可选，覆盖全局配置）
     * @return string 渲染后的内容
     */
    public static function render(string $template, array $vars = [], ?RendererConfig $config = null): string
    {
        $config = $config ?? self::$config ?? new RendererConfig();

        // 如果是相对路径，拼接模板目录
        if (!str_starts_with($template, '/')) {
            $template = $config->templateDir . DIRECTORY_SEPARATOR . $template;
        }

        // 缓存处理
        if ($config->enableCache) {
            $cacheFile = self::getCacheFile($template, $config);
            if (self::isCacheValid($cacheFile, $config->cacheLifetime)) {
                return file_get_contents($cacheFile);
            }
        }

        // 渲染模板
        extract($vars);
        ob_start();
        include $template;
        $content = ob_get_clean();

        // 保存缓存
        if ($config->enableCache && isset($cacheFile)) {
            self::saveCache($cacheFile, $content);
        }

        return $content;
    }

    /**
     * 获取缓存文件路径
     */
    private static function getCacheFile(string $template, RendererConfig $config): string
    {
        $hash = md5($template);
        return $config->cacheDir . DIRECTORY_SEPARATOR . $hash . '.php';
    }

    /**
     * 检查缓存是否有效
     */
    private static function isCacheValid(string $cacheFile, int $lifetime): bool
    {
        if (!file_exists($cacheFile)) {
            return false;
        }
        return (time() - filemtime($cacheFile)) < $lifetime;
    }

    /**
     * 保存缓存
     */
    private static function saveCache(string $cacheFile, string $content): void
    {
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0755, true);
        }
        file_put_contents($cacheFile, $content);
    }
}
```

**架构设计图：**
```
┌─────────────────────────────────────────────────────────┐
│  Config.php                                              │
│  'viewConfig' => [...]                                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  FLEA::boot()                                            │
│  → 创建 RendererConfig                                   │
│  → SimpleRenderer::configure($config)                    │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Controller Action                                       │
│  return View::html('post/index.php', ['posts' => $posts])│
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  FileTemplateView                                        │
│  - template: 'post/index.php'                            │
│  - vars: ['posts' => $posts]                             │
│  - rendererConfig: (从全局获取)                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  FileTemplateView::getContent()                          │
│  → SimpleRenderer::render(template, vars, config)        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  SimpleRenderer                                          │
│  - 检查缓存 → 有 → 返回缓存内容                           │
│  - 检查缓存 → 无 → extract + include → 保存缓存 → 返回    │
└─────────────────────────────────────────────────────────┘
```

---

### 2. JsonView（JSON 数据视图）

```php
<?php

namespace FLEA\View;

/**
 * JSON 数据视图
 *
 * 用于 API 响应，自动序列化数据为 JSON 格式
 */
class JsonView implements ViewInterface
{
    /**
     * @var mixed 要编码的数据
     */
    private $data;

    /**
     * @var int HTTP 状态码
     */
    private $statusCode;

    /**
     * 构造函数
     *
     * @param mixed $data 要编码的数据
     * @param int $statusCode HTTP 状态码
     */
    public function __construct($data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * 获取 JSON 内容
     */
    public function getContent(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
```

---

### 3. CsvView（CSV 数据视图）

```php
<?php

namespace FLEA\View;

/**
 * CSV 数据视图
 *
 * 用于导出 CSV 文件，支持自定义分隔符和文件名
 * 支持 Excel 兼容模式（添加 UTF-8 BOM，让 Excel 正确识别中文）
 */
class CsvView implements ViewInterface
{
    /**
     * @var array 数据行
     */
    private $rows;

    /**
     * @var string 分隔符
     */
    private $delimiter;

    /**
     * @var string 文件名
     */
    private $filename;

    /**
     * @var bool Excel 兼容模式
     */
    private $excelCompatible;

    /**
     * 构造函数
     *
     * @param array $rows 数据行
     * @param string $delimiter 分隔符
     * @param string $filename 文件名
     * @param bool $excelCompatible Excel 兼容模式
     */
    public function __construct(
        array $rows,
        string $delimiter = ',',
        string $filename = 'export.csv',
        bool $excelCompatible = false
    ) {
        $this->rows = $rows;
        $this->delimiter = $delimiter;
        $this->filename = $filename;
        $this->excelCompatible = $excelCompatible;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return $this->excelCompatible
            ? 'application/vnd.ms-excel'  // Excel 兼容模式
            : 'text/csv';
    }

    /**
     * 获取 CSV 内容
     */
    public function getContent(): string
    {
        $output = fopen('php://memory', 'w');

        // Excel 兼容模式：添加 UTF-8 BOM，让 Excel 正确识别中文
        if ($this->excelCompatible) {
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        }

        foreach ($this->rows as $row) {
            fputcsv($output, $row, $this->delimiter);
        }
        $result = stream_get_contents($output);
        fclose($output);
        return $result;
    }

    /**
     * 获取下载文件名
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
```

**用法示例**：
```php
// 普通 CSV 导出
return new CsvView($rows, ',', 'data.csv');

// Excel 兼容模式（带 BOM，Excel 能正确显示中文）
return new CsvView($rows, ',', 'data.xls', true);
```

---

### 4. RedirectView（重定向视图）

```php
<?php

namespace FLEA\View;

/**
 * 重定向视图
 *
 * 用于 HTTP 重定向响应，无内容体
 */
class RedirectView implements ViewInterface
{
    /**
     * @var string 重定向 URL
     */
    private $url;

    /**
     * @var int HTTP 状态码
     */
    private $statusCode;

    /**
     * 构造函数
     *
     * @param string $url 重定向 URL
     * @param int $statusCode HTTP 状态码
     */
    public function __construct(string $url, int $statusCode = 302)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * 获取内容（重定向无内容）
     */
    public function getContent(): string
    {
        return '';
    }

    /**
     * 获取重定向 URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
```

---

### 5. BinaryView（二进制文件视图）

```php
<?php

namespace FLEA\View;

/**
 * 二进制文件视图
 *
 * 用于下载二进制文件（PDF、Excel、图片等）
 * 支持流式输出大文件，避免内存溢出
 *
 * 典型用途：
 * - PDF 文件下载
 * - Excel 文件下载（真正的 .xlsx，需要 phpspreadsheet 生成）
 * - 图片下载
 * - 任意文件下载
 */
class BinaryView implements ViewInterface
{
    /**
     * @var string 文件路径
     */
    private $filePath;

    /**
     * @var string 下载文件名
     */
    private $filename;

    /**
     * @var string MIME 类型
     */
    private $mimeType;

    /**
     * 构造函数
     *
     * @param string $filePath 文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型
     */
    public function __construct(
        string $filePath,
        string $filename,
        string $mimeType
    ) {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return $this->mimeType;
    }

    /**
     * 获取文件内容
     *
     * @return string|resource 小文件返回 string，大文件返回 resource
     */
    public function getContent()
    {
        $size = filesize($this->filePath);

        // 小文件（< 1MB）直接读取为 string
        if ($size < 1024 * 1024) {
            return file_get_contents($this->filePath);
        }

        // 大文件返回流资源，由 Response 流式输出
        return fopen($this->filePath, 'rb');
    }

    /**
     * 获取下载文件名
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
```

**用法示例**：
```php
// PDF 下载
return new BinaryView('/path/to/file.pdf', 'report.pdf', 'application/pdf');

// Excel 下载（真正的 .xlsx，需要先用 phpspreadsheet 生成）
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 1. 生成 Excel 文件
$spreadsheet = new Spreadsheet();
// ... 设置数据
$tempFile = sys_get_temp_dir() . '/export.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFile);

// 2. 使用 BinaryView 输出
return new BinaryView($tempFile, 'data.xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
```

---

### 6. SseView（SSE 流式视图）

```php
<?php

namespace FLEA\View;

/**
 * SSE (Server-Sent Events) 流式视图
 *
 * 用于实时推送场景，保持连接持续输出
 */
class SseView implements StreamingViewInterface
{
    /**
     * @var callable Generator 函数，yield 数据
     */
    private $generator;

    /**
     * 构造函数
     *
     * @param callable $generator Generator 函数
     */
    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return 'text/event-stream';
    }

    /**
     * 获取内容（流式视图无完整内容）
     */
    public function getContent(): string
    {
        return '';
    }

    /**
     * 流式发送内容
     */
    public function stream(): void
    {
        // 禁用缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        // SSE 响应头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');  // Nginx
        header('Content-Encoding: none');

        // 调用数据生成器
        $generator = $this->generator;
        foreach ($generator() as $data) {
            echo "data: " . json_encode($data) . "\n\n";
            flush();

            // 检查客户端是否断开
            if (connection_aborted()) {
                break;
            }
        }
    }
}
```

---

### 7. CallbackView（回调视图）

```php
<?php

namespace FLEA\View;

/**
 * 回调视图
 *
 * 用于特殊场景，允许用户通过回调函数处理任意数据生成逻辑
 * 适用于框架设计时未想到的扩展场景，如：
 * - Protocol Buffers 序列化
 * - GraphQL 响应
 * - MessagePack 编码
 * - 自定义模板引擎（Twig、Smarty 等）
 */
class CallbackView implements ViewInterface
{
    /**
     * @var mixed 用户数据
     */
    private $data;

    /**
     * @var string 内容类型
     */
    private $contentType;

    /**
     * @var callable 回调函数
     */
    private $callback;

    /**
     * 构造函数
     *
     * @param mixed $data 用户数据
     * @param string $contentType 内容类型
     * @param callable $callback 回调函数
     */
    public function __construct($data, string $contentType, callable $callback)
    {
        $this->data = $data;
        $this->contentType = $contentType;
        $this->callback = $callback;
    }

    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * 获取内容（通过回调函数生成）
     */
    public function getContent(): string
    {
        $result = call_user_func($this->callback, $this->data);

        // 确保返回字符串
        if (!is_string($result)) {
            // 对象有 __toString 方法
            if (is_object($result) && method_exists($result, '__toString')) {
                return (string) $result;
            }
            // 其他类型转为 JSON
            return json_encode($result);
        }

        return $result;
    }
}
```

**用法示例**：
```php
// Protocol Buffers 序列化
use MyProto\Message;
$msg = new Message(['id' => 123, 'name' => 'test']);
return View::callback($msg, 'application/x-protobuf', fn($msg) => $msg->serializeToString());

// Twig 模板引擎
use Twig\Environment;
$twig = new Environment(...);
return View::callback(
    ['template' => 'index.twig', 'vars' => ['user' => $user]],
    'text/html',
    fn($data) => $twig->render($data['template'], $data['vars'])
);

// GraphQL 响应
return View::callback(
    ['query' => $graphqlQuery, 'schema' => $schema],
    'application/json',
    fn($data) => graphql_query($data['schema'], $data['query'])
);

// MessagePack 编码
return View::callback($data, 'application/x-msgpack', 'msgpack_pack');
```

---

### 8. CallbackViewBuilder（回调视图构建器）

```php
<?php

namespace FLEA\View;

/**
 * CallbackView 的链式构建器
 *
 * 提供更优雅的 API 来创建 CallbackView
 */
class CallbackViewBuilder
{
    /**
     * @var mixed 用户数据
     */
    private $data;

    /**
     * @var string 内容类型
     */
    private $contentType;

    /**
     * @var callable 回调函数
     */
    private $callback;

    /**
     * 设置数据类型
     */
    public function type(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * 设置回调函数
     */
    public function handler(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * 构建视图
     *
     * @param mixed $data 用户数据
     * @return CallbackView
     */
    public function toView($data): CallbackView
    {
        return new CallbackView($data, $this->contentType, $this->callback);
    }
}
```

**用法示例**：
```php
// 链式 API
use FLEA\View\CallbackViewBuilder;

$builder = new CallbackViewBuilder();

// Protocol Buffers
return $builder
    ->type('application/x-protobuf')
    ->handler(fn($msg) => $msg->serializeToString())
    ->toView($protobufMessage);

// GraphQL
return $builder
    ->type('application/json')
    ->handler(fn($data) => graphql_query($data['schema'], $data['query']))
    ->toView(['schema' => $schema, 'query' => $query]);
```

---

## Response 统一处理

```php
<?php

namespace FLEA;

use FLEA\View\ViewInterface;
use FLEA\View\StreamingViewInterface;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\JsonView;

/**
 * HTTP 响应封装
 *
 * 负责根据 View 类型设置响应头并发送内容
 */
class Response
{
    /**
     * @var ViewInterface 视图对象
     */
    private $view;

    /**
     * 构造函数
     *
     * @param ViewInterface $view 视图对象
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * 从 View 创建 Response
     */
    public static function fromView(ViewInterface $view): self
    {
        return new self($view);
    }

    /**
     * 发送响应
     */
    public function send(): void
    {
        // 流式视图（SSE、实时推送等）
        if ($this->view instanceof StreamingViewInterface) {
            $this->view->stream();
            return;
        }

        // 重定向视图
        if ($this->view instanceof RedirectView) {
            http_response_code($this->view->getStatusCode());
            header("Location: " . $this->view->getUrl());
            return;
        }

        // 设置 Content-Type
        header("Content-Type: " . $this->view->getContentType() . "; charset=utf-8");

        // CSV 和 BinaryView 需要下载头
        if ($this->view instanceof CsvView || $this->view instanceof BinaryView) {
            header("Content-Disposition: attachment; filename=\"" . $this->view->getFilename() . "\"");
        }

        // JSON 视图设置状态码
        if ($this->view instanceof JsonView) {
            http_response_code($this->view->getStatusCode());
        }

        // BinaryView 支持流式输出
        if ($this->view instanceof BinaryView) {
            $content = $this->view->getContent();
            if (is_resource($content)) {
                // 大文件流式输出
                fpassthru($content);
                fclose($content);
                return;
            }
            // 小文件直接输出
            echo $content;
            return;
        }

        // 输出内容
        echo $this->view->getContent();
    }
}
```

---

## Dispatcher 处理逻辑

```php
// FLEA/Dispatcher/Simple.php
public function dispatching(): void
{
    // ... 初始化代码

    $controller = new $controllerClass();
    $result = $controller->$actionMethod();  // 执行 action

    // 统一处理响应
    if ($result instanceof ViewInterface) {
        $response = Response::fromView($result);
        $response->send();
    }
    // void 返回：兼容旧代码（已自行输出）
}
```

---

## 迁移策略（旧代码自动适配）

为了平滑迁移，Dispatcher 需要兼容旧版控制器代码：

### 1. 兼容 void 返回（已自行输出）

```php
public function dispatching(): void
{
    $controller = new $controllerClass();
    $result = $controller->$actionMethod();

    if ($result instanceof ViewInterface) {
        // 新代码：返回 ViewInterface
        $response = Response::fromView($result);
        $response->send();
    }
    // void 返回：旧代码已自行处理（echo/display）
    // 什么都不做，避免重复输出
}
```

### 2. 兼容旧版 SimpleView 的 display()/fetch() 方法

如果旧代码调用 `$this->view->display()` 或 `$this->view->fetch()`，
这些方法已直接输出内容，属于 void 返回场景，无需额外处理。

### 3. 旧控制器代码示例（仍能工作）

```php
// 旧代码：使用 SimpleView
class PostController extends Controller
{
    public function actionIndex(): void
    {
        $posts = $this->model->getPublishedPosts(10);
        $this->view->assign('posts', $posts);
        $this->view->display('post/index.php');
        // 已自行输出，返回 void
    }
}
```

### 4. 新控制器代码示例

```php
// 新代码：返回 ViewInterface
class PostController extends Controller
{
    public function actionIndex(): ViewInterface
    {
        $posts = $this->model->getPublishedPosts(10);
        return ViewFactory::html('post/index.php', ['posts' => $posts]);
    }
}
```

**迁移建议：**
- 旧代码可以逐步迁移，不需要一次性全部改写
- 新代码推荐使用 `ViewFactory` 工厂类创建视图
- 旧代码的 `SimpleView` 已废弃，但短期内仍能工作

```php
<?php

namespace App\Controller;

use FLEA\View\ViewInterface;
use FLEA\View\FileTemplateView;
use FLEA\View\JsonView;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\SseView;
use FLEA\View;

class PostController extends Controller
{
    // 1. HTML 视图（推荐用工厂方法）
    public function actionIndex(): ViewInterface
    {
        $posts = $this->model->getPublishedPosts(10);
        return View::html('post/index.php', ['posts' => $posts]);
    }

    // 1b. HTML 视图（直接实例化）
    public function actionIndex2(): ViewInterface
    {
        $posts = $this->model->getPublishedPosts(10);
        return (new FileTemplateView('post/index.php', ['posts' => $posts]));
    }

    // 2. JSON 响应
    public function actionApi(): ViewInterface
    {
        return new JsonView([
            'status' => 'ok',
            'data' => $data
        ]);
    }

    // 3. 重定向
    public function actionRedirect(): ViewInterface
    {
        return new RedirectView('/new-location', 302);
    }

    // 4. CSV 导出
    public function actionExport(): ViewInterface
    {
        return new CsvView($rows, ',', 'posts-export.csv');
    }

    // 5. SSE 实时推送
    public function actionUpdates(): ViewInterface
    {
        return new SseView(function() {
            while (true) {
                yield ['time' => date('H:i:s'), 'data' => getData()];
                sleep(1);
                if (connection_aborted()) break;
            }
        });
    }

    // 6. CallbackView - Protocol Buffers
    public function actionProto(): ViewInterface
    {
        $msg = new MyProto\Message(['id' => 123, 'name' => 'test']);
        return View::callback($msg, 'application/x-protobuf', fn($msg) => $msg->serializeToString());
    }

    // 7. CallbackView - Twig 模板
    public function actionTwig(): ViewInterface
    {
        return View::callback(
            ['template' => 'page.twig', 'vars' => ['user' => $user]],
            'text/html',
            fn($data) => $twig->render($data['template'], $data['vars'])
        );
    }
}
```

---

## View 工厂类（完整 API）

```php
<?php

namespace FLEA;

use FLEA\View\FileTemplateView;
use FLEA\View\JsonView;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\SseView;
use FLEA\View\CallbackView;

/**
 * 视图工厂类
 *
 * 简化常用视图的创建
 */
class View
{
    /**
     * 创建文件模板视图（通用方法）
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @param string $contentType 内容类型（默认 text/html）
     * @return FileTemplateView
     */
    public static function render(string $template, array $vars = [], string $contentType = 'text/html'): FileTemplateView
    {
        return new FileTemplateView($template, $vars, $contentType);
    }

    /**
     * 创建 HTML 视图
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @return FileTemplateView
     */
    public static function html(string $template, array $vars = []): FileTemplateView
    {
        return new FileTemplateView($template, $vars, 'text/html');
    }

    /**
     * 创建 XML 视图
     *
     * @param string $template 模板文件路径
     * @param array $vars 视图变量
     * @return FileTemplateView
     */
    public static function xml(string $template, array $vars = []): FileTemplateView
    {
        return new FileTemplateView($template, $vars, 'text/xml');
    }

    /**
     * 创建 JSON 视图
     *
     * @param mixed $data 要编码的数据
     * @param int $status HTTP 状态码
     * @return JsonView
     */
    public static function json($data, int $status = 200): JsonView
    {
        return new JsonView($data, $status);
    }

    /**
     * 创建 CSV 视图
     *
     * @param array $rows 数据行
     * @param string $filename 文件名
     * @param string $delimiter 分隔符
     * @param bool $excelCompatible Excel 兼容模式（添加 UTF-8 BOM）
     * @return CsvView
     */
    public static function csv(array $rows, string $filename = 'export.csv', string $delimiter = ',', bool $excelCompatible = false): CsvView
    {
        return new CsvView($rows, $delimiter, $filename, $excelCompatible);
    }

    /**
     * 创建重定向视图
     *
     * @param string $url 重定向 URL
     * @param int $code HTTP 状态码
     * @return RedirectView
     */
    public static function redirect(string $url, int $code = 302): RedirectView
    {
        return new RedirectView($url, $code);
    }

    /**
     * 创建二进制文件视图
     *
     * @param string $filePath 文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型
     * @return BinaryView
     */
    public static function binary(string $filePath, string $filename, string $mimeType): BinaryView
    {
        return new BinaryView($filePath, $filename, $mimeType);
    }

    /**
     * 创建 SSE 视图
     *
     * @param callable $generator Generator 函数
     * @return SseView
     */
    public static function sse(callable $generator): SseView
    {
        return new SseView($generator);
    }

    /**
     * 创建回调视图
     *
     * @param mixed $data 用户数据
     * @param string $contentType 内容类型
     * @param callable $callback 回调函数
     * @return CallbackView
     */
    public static function callback($data, string $contentType, callable $callback): CallbackView
    {
        return new CallbackView($data, $contentType, $callback);
    }

    /**
     * 使用构建器创建回调视图
     *
     * @return CallbackViewBuilder
     */
    public static function build(): CallbackViewBuilder
    {
        return new CallbackViewBuilder();
    }

    /**
     * 创建 PDF 视图（快捷方式）
     *
     * @param string $filePath PDF 文件路径
     * @param string $filename 下载文件名
     * @return BinaryView
     */
    public static function pdf(string $filePath, string $filename = 'document.pdf'): BinaryView
    {
        return new BinaryView($filePath, $filename, 'application/pdf');
    }

    /**
     * 创建 Excel 视图（快捷方式）
     *
     * @param string $filePath Excel 文件路径
     * @param string $filename 下载文件名
     * @return BinaryView
     */
    public static function excel(string $filePath, string $filename = 'data.xlsx'): BinaryView
    {
        return new BinaryView($filePath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * 创建图片视图（快捷方式）
     *
     * @param string $filePath 图片文件路径
     * @param string $filename 下载文件名
     * @param string $mimeType MIME 类型（默认 image/jpeg）
     * @return BinaryView
     */
    public static function image(string $filePath, string $filename = 'image.jpg', string $mimeType = 'image/jpeg'): BinaryView
    {
        return new BinaryView($filePath, $filename, $mimeType);
    }
}
```

**控制器简化用法**：
```php
// 文件模板视图
return View::html('post/index.php', ['posts' => $posts]);
return View::xml('sitemap.xml.php', ['urls' => $urls]);
return View::render('api/response.json.php', $data, 'application/json');

// 数据视图
return View::json($data);
return View::csv($rows, 'export.csv');

// 功能视图
return View::redirect('/login');
return View::binary($file, 'download.pdf', 'application/pdf');
return View::sse($generator);

// 快捷方式
return View::pdf('/path/to/report.pdf');
return View::excel('/path/to/data.xlsx');
return View::image('/path/to/photo.jpg', 'photo.jpg', 'image/png');

// 回调视图
return View::callback($data, 'application/x-protobuf', fn($msg) => $msg->serializeToString());
return View::build()->type('application/json')->handler(fn($d) => json_encode($d))->toView($data);
```

---

### 9. NullView（空视图）

```php
<?php

namespace FLEA\View;

/**
 * 空视图实现
 *
 * 空对象模式，避免空指针判断
 * 用于不需要视图内容的场景（如 204 No Content）
 */
class NullView implements ViewInterface
{
    /**
     * 获取内容类型
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * 获取内容（空字符串）
     */
    public function getContent(): string
    {
        return '';
    }
}
```

**用法示例**：
```php
// 204 No Content 响应
public function actionDelete(): ViewInterface
{
    $this->model->delete($id);
    return (new NullView())->withHeader('X-Message', '删除成功');
}

// 默认返回值，避免 null 检查
public function getView(): ViewInterface
{
    return $this->view ?? new NullView();
}
```

---

## 需要修改的文件列表

| 文件 | 操作 | 说明 |
|------|------|------|
| `src/FLEA/View/ViewInterface.php` | 修改 | 增加 `getContentType()` 和 `getContent()` |
| `src/FLEA/View/StreamingViewInterface.php` | 新增 | 流式视图接口 |
| `src/FLEA/View/FileTemplateView.php` | 新增 | 文件模板视图（原 HtmlView，支持任意文件类型） |
| `src/FLEA/View/JsonView.php` | 新增 | JSON 数据视图 |
| `src/FLEA/View/CsvView.php` | 新增 | CSV 数据视图（支持 Excel 兼容模式） |
| `src/FLEA/View/RedirectView.php` | 新增 | 重定向视图 |
| `src/FLEA/View/BinaryView.php` | 新增 | 二进制文件视图（支持流式输出） |
| `src/FLEA/View/SseView.php` | 新增 | SSE 流式视图 |
| `src/FLEA/View/CallbackView.php` | 新增 | 回调视图（特殊场景扩展） |
| `src/FLEA/View/CallbackViewBuilder.php` | 新增 | 回调视图构建器（链式 API） |
| `src/FLEA/View/RendererConfig.php` | 新增 | 渲染器配置类 |
| `src/FLEA/View/SimpleRenderer.php` | 新增 | 简单 PHP 模板渲染器（静态类） |
| `src/FLEA/View/NullView.php` | 修改 | 改造为空对象模式，符合新接口 |
| `src/FLEA/View.php` | 新增 | 视图工厂类（完整 API） |
| `src/FLEA/Response.php` | 重构 | 改为处理 ViewInterface |
| `src/FLEA/Dispatcher/Simple.php` | 修改 | 处理 ViewInterface 返回值 |
| `src/FLEA.php` | 修改 | 初始化 View 配置（SimpleRenderer::configure） |
| `src/FLEA/View/Simple.php` | 废弃 | 直接废弃，不保留兼容层 |

---

## 方案优势

| 维度 | 优势 |
|------|------|
| **职责分离** | View 负责内容生成，Response 负责 HTTP 响应 |
| **类型安全** | 每种视图类型明确，IDE 友好 |
| **扩展性强** | 新增视图类型只需实现接口 |
| **中间件支持** | Response 统一处理，中间件可拦截修改 |
| **向后兼容** | void 返回的旧代码仍能工作 |
| **符合 ISP** | 接口隔离，不强制实现不必要方法 |
| **流式支持** | StreamingViewInterface 支持 SSE 等场景 |

---

## 待决策事项

全部已解决。

**已解决：**
- ~~PHP 7.4 联合类型问题~~：`BinaryView::getContent()` 使用 `@return string|resource` docblock 代替类型声明
- ~~viewConfig 配置处理~~：通过 `RendererConfig` 类封装配置，`SimpleRenderer::configure()` 设置全局配置
- ~~Simple.php 的处置~~：直接废弃 Simple.php，不保留兼容层，旧代码必须改写
- ~~NullView 的处置~~：改造为符合新接口的空视图，实现空对象模式
- ~~SimpleView 的处置~~：已删除，因为无法提供有价值的兼容性
- ~~View 工厂类类名~~：确定为 `FLEA\View`（与 Cache、Config 保持一致）
- ~~FileTemplateView 的 contentType 参数~~：不设为必填，默认值 `text/html`
- ~~迁移策略~~：旧控制器代码需要自动适配（Dispatcher 兼容 void 返回和旧式 display/fetch 调用）

---

## 附录：Excel 导出最佳实践

### 场景 1：简单数据导出（推荐）

```php
// 使用 CsvView 的 Excel 兼容模式（带 UTF-8 BOM）
return View::csv($rows, 'data.xls', true);
```

**特点**：
- 无需第三方依赖
- Excel 能打开，会提示"文件格式与扩展名不符"（忽略即可）
- 适合简单表格数据

### 场景 2：真正的 Excel 文件（需要第三方库）

```php
// 1. 安装依赖
// composer require phpoffice/phpspreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use FLEA\View\BinaryView;

// 2. 控制器代码
public function actionExportExcel(): ViewInterface
{
    // 生成 Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($data, null, 'A1');

    // 保存到临时文件
    $tempFile = sys_get_temp_dir() . '/export.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);

    // 使用 BinaryView 输出
    return View::binary(
        $tempFile,
        'data.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
}
```

**特点**：
- 需要 phpspreadsheet 库
- 真正的.xlsx 格式
- 支持样式、公式、多工作表
- 大文件需要流式输出（BinaryView 支持）

### 场景 3：PDF 文件下载

```php
// 使用 TCPDF、Dompdf 等生成 PDF 后
return View::binary($pdfFile, 'report.pdf', 'application/pdf');
```

---

## 参考资料

- Symfony Response + Template 分离设计
- PSR-7 ResponseInterface
- Laravel View + Response 设计
