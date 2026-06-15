# Response + 中间件集成方案

**日期**：2026-04-03
**版本**：v2.3.0
**状态**：已完成

---

## 一、核心设计思想

### 1.1 职责划分

| 组件 | 职责 |
|------|------|
| **View** | 内容生成（HTML、JSON、CSV、重定向等） |
| **Response** | View 的包装器，提供快捷方法，受控发送响应 |
| **Signal** | 内部信号机制（发布/订阅模式），控制响应发送时机 |
| **中间件** | 请求过滤，可短路返回 Response |
| **Dispatcher** | 执行 action，将 ViewInterface 包装为 Response |
| **Pipeline** | 执行中间件链，返回最终结果 |
| **FLEA::run()** | 统一入口，发布信号并发送响应 |

### 1.2 流程图

```
请求
  ↓
FLEA::run()
  │
  ├─ 路由匹配失败 → View::html('404') → Response::fromView()
  │                                         ↓
  │                                    Signal::publish('response.send')
  │                                         ↓
  │                                    response->send()
  │
  └─ 路由匹配成功
       │
       ├─ 创建 Pipeline
       │
       ├─ 注册中间件
       │
       ├─ 创建 $dispatch 闭包
       │      │
       │      └─ Dispatcher::dispatching()
       │           └─ action() → ViewInterface → Response::fromView()
       │
       ├─ Pipeline::run($dispatch)
       │      │
       │      ├─ 中间件 1::handle($next) → 前置逻辑 → $next() → 后置逻辑 → 返回
       │      ├─ 中间件 2::handle($next) → 短路？return Response::error()
       │      └─ 返回 Response 或 null
       │
       └─ 处理返回值
            ├─ Signal::publish('response.send')  ← 发布"允许发送"信号
            ├─ Response → send() → 检查信号 → 发送响应
            └─ null → 旧代码已自行输出（兼容旧代码）
```

---

## 二、Signal 信号机制（内部类）

### 2.1 核心职责

**Signal 是内嵌的发布/订阅机制**：
- 控制响应发送时机
- 防止中间件中途发送响应
- 不抽取成独立组件，仅框架内部使用

### 2.2 代码结构

```php
<?php

namespace FLEA\Internal;

/**
 * 内部信号机制（发布/订阅模式）
 *
 * 用于控制响应发送时机，防止中间件中途发送响应
 *
 * @internal 框架内部使用，不对外暴露
 */
final class Signal
{
    /**
     * @var array<string, list<callable>> 事件监听器列表
     */
    private static array $listeners = [];

    /**
     * 订阅事件
     *
     * @param string $event 事件名称
     * @param callable $callback 回调函数
     */
    public static function subscribe(string $event, callable $callback): void
    {
        self::$listeners[$event][] = $callback;
    }

    /**
     * 发布事件
     *
     * @param string $event 事件名称
     */
    public static function publish(string $event): void
    {
        foreach (self::$listeners[$event] ?? [] as $callback) {
            $callback();
        }
    }

    /**
     * 清除所有监听器（用于测试）
     */
    public static function clear(): void
    {
        self::$listeners = [];
    }
}
```

---

## 三、Response 类设计

### 3.1 核心职责

**Response 是 View 的包装器**：
- 包装任何 ViewInterface 对象
- 处理 HTTP 响应头、状态码
- 提供 `send()` 方法发送响应（受 Signal 控制）
- 提供静态快捷方法供中间件等调用

### 3.2 代码结构

```php
<?php

namespace FLEA;

use FLEA\Internal\Signal;
use FLEA\View\ViewInterface;
use FLEA\View\StreamingViewInterface;
use FLEA\View\RedirectView;
use FLEA\View\CsvView;
use FLEA\View\BinaryView;
use FLEA\View\JsonView;

/**
 * HTTP 响应包装器
 *
 * 包装 ViewInterface，处理响应头、状态码，提供发送方法
 * send() 方法受 Signal 控制，只有收到 'response.send' 信号后才能发送
 */
class Response
{
    private ViewInterface $view;
    private int $statusCode = 200;
    private array $headers = [];
    private bool $canSend = false;

    /**
     * 构造函数（私有）
     */
    private function __construct(ViewInterface $view)
    {
        $this->view = $view;

        // 订阅"允许发送"信号
        Signal::subscribe('response.send', function() {
            $this->canSend = true;
        });
    }

    /**
     * 从 View 创建 Response
     */
    public static function fromView(ViewInterface $view): self
    {
        return new self($view);
    }

    /**
     * 错误响应（快捷方法）
     */
    public static function error(string $message, int $httpCode = 400, int $errCode = -1): self
    {
        return new self(View::json([
            'code'    => $errCode,
            'message' => $message,
            'data'    => null,
        ], $httpCode));
    }

    /**
     * 成功响应（快捷方法）
     */
    public static function success($data = null, string $message = 'ok', int $httpCode = 200): self
    {
        return new self(View::json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], $httpCode));
    }

    /**
     * 分页响应（快捷方法）
     */
    public static function paginate(array $items, int $total, int $page, int $pageSize): self
    {
        return self::success([
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 添加响应头
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * 设置状态码
     */
    public function withStatus(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * 获取 View 对象
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 获取响应头
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 发送响应
     *
     * 注意：
     * 1. 此方法不调用 exit，由调用者决定何时终止
     * 2. 只有在收到 'response.send' 信号后才能发送
     * 3. 这样设计是为了：
     *    - 防止中间件中途发送响应
     *    - 支持协程/多线程环境（exit 会终止整个进程）
     *    - 支持测试时抓取输出内容
     *    - 支持中间件后置逻辑执行
     *
     * @throws \RuntimeException 如果在未收到信号时调用
     */
    public function send(): void
    {
        if (!$this->canSend) {
            throw new \RuntimeException(
                'Response::send() can only be called after FLEA::run() publishes "response.send" signal. ' .
                'In middleware, return Response instead of calling send().'
            );
        }

        // 流式视图
        if ($this->view instanceof StreamingViewInterface) {
            $this->view->stream();
            return;
        }

        // 重定向
        if ($this->view instanceof RedirectView) {
            http_response_code($this->view->getStatusCode());
            header("Location: " . $this->view->getUrl());
            return;
        }

        // 设置状态码
        http_response_code($this->statusCode);

        // 设置响应头
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Content-Type
        header("Content-Type: " . $this->view->getContentType() . "; charset=utf-8");

        // 下载头
        if ($this->view instanceof CsvView || $this->view instanceof BinaryView) {
            header("Content-Disposition: attachment; filename=\"" . $this->view->getFilename() . "\"");
        }

        // JSON 状态码
        if ($this->view instanceof JsonView) {
            http_response_code($this->view->getStatusCode());
        }

        // 输出内容
        $content = $this->view->getContent();
        if (is_resource($content)) {
            fpassthru($content);
            fclose($content);
        } else {
            echo $content;
        }
    }
}
```

---

## 四、中间件设计

### 4.1 MiddlewareInterface

```php
<?php

namespace FLEA\Middleware;

/**
 * 中间件接口
 */
interface MiddlewareInterface
{
    /**
     * 处理请求
     *
     * @param callable $next 下一个中间件或请求处理器
     * @return mixed 返回 Response 表示短路，返回 $next() 的结果表示继续
     */
    public function handle(callable $next);
}
```

### 4.2 CorsMiddleware（不短路）

```php
<?php

namespace FLEA\Middleware;

/**
 * CORS 中间件
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        // 前置：设置 CORS 头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // 执行下一个
        $result = $next();

        // 后置：暴露头
        header('Access-Control-Expose-Headers: X-Trace-Id');

        // 返回结果
        return $result;
    }
}
```

### 4.3 AuthMiddleware（可短路）

```php
<?php

namespace FLEA\Middleware;

/**
 * 认证中间件
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        $token = \FLEA\Request::current()->bearerToken();

        // 验证失败，短路返回 Response
        if (!$token || !$this->validate($token)) {
            return \FLEA\Response::error('Unauthorized', 401);
        }

        // 验证通过，继续执行
        return $next();
    }

    private function validate(string $token): bool
    {
        $tokens = (array)\FLEA::getAppInf('authTokens');
        return in_array($token, $tokens, true);
    }
}
```

### 4.4 RateLimitMiddleware（可短路）

```php
<?php

namespace FLEA\Middleware;

/**
 * 限流中间件
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        $max    = (int)(\FLEA::getAppInf('rateLimitMax') ?? 60);
        $window = (int)(\FLEA::getAppInf('rateLimitWindow') ?? 60);
        $by     = \FLEA::getAppInf('rateLimitBy') ?? 'ip';

        $key   = 'rate:' . $this->resolveKey($by);
        $cache = \FLEA\Cache::provider();
        $count = (int)($cache->get($key) ?? 0);

        // 超限，短路返回 Response
        if ($count >= $max) {
            return \FLEA\Response::error('Too Many Requests', 429);
        }

        // 更新计数
        $cache->set($key, $count + 1, $window);

        // 继续执行
        return $next();
    }

    private function resolveKey(string $by): string
    {
        return $by === 'token'
            ? (\FLEA\Request::current()->bearerToken() ?? 'anonymous')
            : \FLEA\Request::current()->ip();
    }
}
```

---

## 五、Pipeline 设计

### 5.1 Pipeline 类

```php
<?php

namespace FLEA\Middleware;

/**
 * 中间件管道（洋葱模型）
 */
class Pipeline
{
    private array $middlewares = [];

    public static function create(): self
    {
        return new self();
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 执行中间件链
     *
     * @param callable $destination 最终处理器
     * @return mixed 返回 Response 或 null
     */
    public function run(callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn(callable $carry, MiddlewareInterface $mw) => fn() => $mw->handle($carry),
            $destination
        );

        return $pipeline();
    }
}
```

---

## 六、Dispatcher 设计

### 6.1 修改 handleActionResult()

```php
// src/FLEA/Dispatcher/Simple.php

/**
 * 处理 Action 返回值
 *
 * 将 ViewInterface 包装为 Response
 *
 * @param mixed $result Action 方法的返回值
 * @return Response|null
 */
protected function handleActionResult($result)
{
    if ($result instanceof \FLEA\View\ViewInterface) {
        return \FLEA\Response::fromView($result);
    }

    // void 返回或 null：旧代码已自行输出
    return null;
}
```

---

## 七、FLEA 核心设计

### 7.1 FLEA::run() 完整逻辑

```php
// src/FLEA.php

use FLEA\Internal\Signal;

public function run(): void
{
    // 路由匹配
    if (!\FLEA\Router::dispatch()) {
        $response = Response::fromView(View::html('404.html', ['statusCode' => 404]));
        Signal::publish('response.send');
        $response->send();
        return;
    }

    // 创建 Pipeline
    $pipeline = \FLEA\Middleware\Pipeline::create();

    // 注册全局中间件
    foreach (self::$middlewares as $mw) {
        $pipeline->pipe($mw);
    }

    // 注册路由级中间件
    foreach (\FLEA\Router::getMatchedMiddlewares() as $mw) {
        $pipeline->pipe($mw);
    }

    // 创建最终处理器
    $dispatch = function() {
        $dispatcher = \FLEA\Router::createDispatcher();
        return $dispatcher->dispatching();
    };

    // 执行 Pipeline，获取返回值
    $result = $pipeline->run($dispatch);

    // 统一处理返回值
    if ($result instanceof Response) {
        // 发布"允许发送"信号
        Signal::publish('response.send');
        // 发送响应
        $result->send();
        return;
    }

    // null: 旧代码已自行输出
}
```

---

## 八、修改清单

| 文件 | 修改内容 |
|------|----------|
| `src/FLEA/Internal/Signal.php` | **新增**：内部信号机制（发布/订阅模式） |
| `src/FLEA/Response.php` | 重构为包装器，新增 `error()`, `success()`, `paginate()`, `withHeader()`, `withStatus()`，`send()` 受 Signal 控制 |
| `src/FLEA/Middleware/MiddlewareInterface.php` | `handle()` 不声明返回类型 |
| `src/FLEA/Middleware/CorsMiddleware.php` | 改为返回 `$next()` 的结果 |
| `src/FLEA/Middleware/AuthMiddleware.php` | 改为 `return Response::error()` 和 `return $next()` |
| `src/FLEA/Middleware/RateLimitMiddleware.php` | 改为 `return Response::error()` 和 `return $next()` |
| `src/FLEA/Middleware/Pipeline.php` | `run()` 返回结果，不声明类型 |
| `src/FLEA/Dispatcher/Simple.php` | `handleActionResult()` 包装 ViewInterface 为 Response |
| `src/FLEA.php` | `run()` 发布 'response.send' 信号并处理 Pipeline 返回的 Response |

---

## 九、方案特点

| 特点 | 说明 |
|------|------|
| **发布/订阅控制** | 通过 Signal 机制控制响应发送时机 |
| **防止误用** | 中间件调用 send() 会抛异常 |
| **清晰** | View 生成内容，Response 包装，Signal 控制 |
| **灵活** | 中间件可短路返回 Response，也可继续执行 |
| **兼容** | void 返回的旧代码仍能工作 |
| **无 exit** | 支持协程/多线程、测试抓取输出、中间件后置逻辑 |
| **内嵌实现** | Signal 仅几十行代码，不抽取成独立组件 |

---

## 十、使用示例

### 10.1 Controller

```php
// 直接使用 Response 快捷方法
class UserController extends Controller
{
    public function actionIndex()
    {
        return Response::success($data);
    }

    public function actionNotFound()
    {
        return Response::error('Not found', 404);
    }
}
```

### 10.2 中间件

```php
// 短路中间件
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        if (!$this->validate($token)) {
            return Response::error('Unauthorized', 401);
        }
        return $next();
    }
}

// 非短路中间件
class CorsMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        header('Access-Control-Allow-Origin: *');
        $result = $next();
        header('Access-Control-Expose-Headers: X-Trace-Id');
        return $result;
    }
}
```

---

## 十一、设计模式说明

### 11.1 发布/订阅模式

```
Response (订阅者)          Signal (事件总线)         FLEA (发布者)
     │                           │                       │
     ├─ subscribe()              │                       │
     │                           │                       │
     │                           │                       │
     │                           ├─ publish() ←──────────┤
     │                           │                       │
     ├─ $canSend = true          │                       │
     │                           │                       │
     │                           │                       │
     └─ send() (检查$canSend)    │                       │
```

### 11.2 方案优势

| 优势 | 说明 |
|------|------|
| **解耦** | Response 不依赖 FLEA，只依赖 Signal |
| **语义清晰** | "发布/订阅"是成熟的设计模式 |
| **可扩展** | 可以添加多个监听器 |
| **调试友好** | 可以记录事件发布日志 |
| **内嵌实现** | 不需要独立组件，维护成本低 |

---

## 十二、执行步骤

### 依赖关系

```
Step 1: Signal.php ─────────────────────────────┐
                                                ↓
Step 2: Response.php ──────────────────────────┐
                                                ↓
Step 3: MiddlewareInterface.php ───────────────┐┐
Step 4: Pipeline.php ─────────────────────────┘│
                                               │
Step 5: CorsMiddleware.php ────────────────────┤
Step 6: AuthMiddleware.php ────────────────────┤ (依赖 Response + MiddlewareInterface)
Step 7: RateLimitMiddleware.php ───────────────┤
                                               │
Step 8: Dispatcher/Simple.php ─────────────────┤
                                               │
Step 9: FLEA.php ──────────────────────────────┘ (依赖以上所有)
```

### 步骤列表

| 步骤 | 文件 | 操作 | 依赖 | 状态 |
|------|------|------|------|------|
| 1 | `src/FLEA/Internal/Signal.php` | 新建文件 | 无 | ✅ |
| 2 | `src/FLEA/Response.php` | 重写 | Signal | ✅ |
| 3 | `src/FLEA/Middleware/MiddlewareInterface.php` | 修改接口 | 无 | ✅ |
| 4 | `src/FLEA/Middleware/Pipeline.php` | 重写 | MiddlewareInterface | ✅ |
| 5 | `src/FLEA/Middleware/CorsMiddleware.php` | 修改 | Pipeline + MiddlewareInterface | ✅ |
| 6 | `src/FLEA/Middleware/AuthMiddleware.php` | 修改 | Pipeline + Response | ✅ |
| 7 | `src/FLEA/Middleware/RateLimitMiddleware.php` | 修改 | Pipeline + Response | ✅ |
| 8 | `src/FLEA/Dispatcher/Simple.php` | 修改 | Response | ✅ |
| 9 | `src/FLEA.php` | 修改 | Signal + Pipeline + Response | ✅ |
| 10 | `FLEA/CHANGES.md` | 追加 | 以上全部 | ✅ |
| 11 | `SPEC.md` | 同步 | 以上全部 | ✅ |
| 12 | `GIT_COMMIT.md` | 追加 | 以上全部 | ✅ |

### 注意事项

1. 每个步骤完成后标记为 ✅
2. 遇到 Edit 匹配失败只试 1 次，立即换 Write 重写
3. 所有步骤完成后更新文档，等用户 review 确认再 git commit

---

## 十三、优化方案（门面 + 适配器模式）

### 13.1 原方案的问题

原方案中 `Response` 承担了太多职责：
- 数据容器（status、headers、view）
- 信号订阅（subscribe signal）
- 信号检查（canSend）
- 实际发送（header/echo）

中间件需要 `return Response`，Pipeline 需要 `return $result`，改动涉及整个中间件链。

### 13.2 优化后的架构

**核心思路：门面（Facade）+ 适配器（Adapter）**

```
Response（门面）              HttpResponse（适配器）
├── 单例入口                    ├── 数据容器（status/headers/view）
├── 信号订阅                    ├── 实际发送逻辑
├── 快捷方法                    └── 不依赖 Signal
└── 委托调用
```

**类比 Request：**
```
Request::current()    →  包装 $_SERVER/$_GET/$_POST
Response::current()   →  包装 HttpResponse
```

### 13.3 职责划分

| 组件 | 职责 |
|------|------|
| **View** | 内容生成（HTML、JSON、CSV、重定向等） |
| **Response（门面）** | 统一入口、信号订阅、快捷方法、委托调用 |
| **HttpResponse（适配器）** | 响应数据容器 + 实际发送 |
| **Signal** | 内部发布/订阅机制，控制发送时机 |
| **中间件** | 通过 `Response::current()` 操作响应，短路时设置状态即可 |
| **Pipeline** | 保持 void 返回，不改动接口 |
| **Dispatcher** | 执行 action，将 ViewInterface 设置到 Response |
| **FLEA::runMVC()** | 发布信号，触发发送 |

### 13.4 流程图

```
请求
  ↓
FLEA::runMVC()
  │
  ├─ 路由匹配失败 → Response::current().setView().withStatus(404)
  │                    ↓
  │               Signal::publish('response.send')
  │                    ↓
  │               Response::send() → 委托 HttpResponse 发送
  │
  └─ 路由匹配成功
       │
       ├─ 创建 Pipeline（接口不变，run() 返回 void）
       │
       ├─ 注册中间件
       │
       ├─ 创建 $dispatch 闭包
       │      │
       │      └─ Dispatcher::dispatching()
       │           └─ action() → ViewInterface
       │                → Response::current().setView(view)
       │
       ├─ Pipeline::run($dispatch)  ← void，不返回值
       │      │
       │      ├─ 中间件 1::handle($next) → 前置 → Response::current().withHeader(...) → $next() → 后置
       │      ├─ 中间件 2::handle($next) → 短路？Response::current().setView(...).withStatus(401)
       │      └─ 不 return 任何东西
       │
       └─ Pipeline 结束
            ├─ Signal::publish('response.send')  ← 发布信号
            ├─ Response::send() → HttpResponse::send()
            └─ 发送响应
```

### 13.5 Response 类（门面）

```php
<?php

namespace FLEA;

use FLEA\Internal\Signal;
use FLEA\View\ViewInterface;

/**
 * HTTP 响应门面（Facade）
 *
 * 统一入口，管理单例、订阅信号、委托给 HttpResponse
 *
 * 用法：
 * ```php
 * Response::current()->withStatus(401)->setView(View::json([...]))
 * Response::success($data)
 * Response::error('Not found', 404)
 * ```
 */
class Response
{
    /**
     * @var self|null 单例实例
     */
    private static $instance = null;

    /**
     * @var HttpResponse 内部适配器
     */
    private $http;

    /**
     * 构造函数（私有）
     */
    private function __construct()
    {
        $this->http = new HttpResponse();

        // 门面负责订阅信号
        Signal::subscribe('response.send', function () {
            $this->http->allowSend();
        });
    }

    /**
     * 获取当前响应实例
     *
     * @return self
     */
    public static function current()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 从 View 创建（快捷工厂）
     *
     * @param ViewInterface $view
     * @return self
     */
    public static function fromView(ViewInterface $view)
    {
        $instance = self::current();
        $instance->http->setView($view);
        return $instance;
    }

    /**
     * 错误响应（快捷方法）
     *
     * @param string $message
     * @param int $httpCode
     * @param int $errCode
     * @return self
     */
    public static function error($message, $httpCode = 400, $errCode = -1)
    {
        $res = self::current();
        $res->http->setView(View::json([
            'code'    => $errCode,
            'message' => $message,
            'data'    => null,
        ], $httpCode));
        $res->http->withStatus($httpCode);
        return $res;
    }

    /**
     * 成功响应（快捷方法）
     *
     * @param mixed $data
     * @param string $message
     * @param int $httpCode
     * @return self
     */
    public static function success($data = null, $message = 'ok', $httpCode = 200)
    {
        $res = self::current();
        $res->http->setView(View::json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], $httpCode));
        $res->http->withStatus($httpCode);
        return $res;
    }

    /**
     * 分页响应（快捷方法）
     *
     * @param array $items
     * @param int $total
     * @param int $page
     * @param int $pageSize
     * @return self
     */
    public static function paginate(array $items, $total, $page, $pageSize)
    {
        return self::success([
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 添加响应头
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function withHeader($name, $value)
    {
        $this->http->withHeader($name, $value);
        return $this;
    }

    /**
     * 设置状态码
     *
     * @param int $statusCode
     * @return self
     */
    public function withStatus($statusCode)
    {
        $this->http->withStatus($statusCode);
        return $this;
    }

    /**
     * 设置视图
     *
     * @param ViewInterface $view
     * @return self
     */
    public function setView(ViewInterface $view)
    {
        $this->http->setView($view);
        return $this;
    }

    /**
     * 获取 View 对象
     *
     * @return ViewInterface|null
     */
    public function getView()
    {
        return $this->http->getView();
    }

    /**
     * 获取状态码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->http->getStatusCode();
    }

    /**
     * 获取响应头
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->http->getHeaders();
    }

    /**
     * 判断是否有内容
     *
     * @return bool
     */
    public function hasContent()
    {
        return $this->http->getView() !== null;
    }

    /**
     * 发送响应（委托给 HttpResponse）
     *
     * @throws \RuntimeException 如果在未收到信号时调用
     */
    public function send()
    {
        $this->http->send();
    }
}
```

### 13.6 HttpResponse 类（适配器）

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
 * HTTP 响应适配器
 *
 * 响应数据容器 + 实际发送逻辑。
 * 不依赖 Signal，只接收指令。
 */
class HttpResponse
{
    /**
     * @var ViewInterface|null 视图对象
     */
    private $view = null;

    /**
     * @var int HTTP 状态码
     */
    private $statusCode = 200;

    /**
     * @var array 自定义响应头
     */
    private $headers = [];

    /**
     * @var bool 是否允许发送
     */
    private $canSend = false;

    /**
     * 允许发送（由 Response 门面调用）
     */
    public function allowSend()
    {
        $this->canSend = true;
    }

    /**
     * 设置视图
     *
     * @param ViewInterface $view
     * @return self
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * 添加响应头
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * 设置状态码
     *
     * @param int $statusCode
     * @return self
     */
    public function withStatus($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * 获取 View 对象
     *
     * @return ViewInterface|null
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 获取状态码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取响应头
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 发送响应
     *
     * @throws \RuntimeException 如果未收到允许发送的指令
     */
    public function send()
    {
        if (!$this->canSend) {
            throw new \RuntimeException(
                'Response can only be sent after FLEA::runMVC() publishes "response.send" signal.'
            );
        }

        // 流式视图
        if ($this->view instanceof StreamingViewInterface) {
            $this->view->stream();
            return;
        }

        // 重定向
        if ($this->view instanceof RedirectView) {
            http_response_code($this->view->getStatusCode());
            header('Location: ' . $this->view->getUrl());
            return;
        }

        // 设置状态码
        http_response_code($this->statusCode);

        // 设置自定义响应头
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Content-Type
        header('Content-Type: ' . $this->view->getContentType() . '; charset=utf-8');

        // 下载头
        if ($this->view instanceof CsvView || $this->view instanceof BinaryView) {
            header('Content-Disposition: attachment; filename="' . $this->view->getFilename() . '"');
        }

        // JSON 状态码
        if ($this->view instanceof JsonView) {
            http_response_code($this->view->getStatusCode());
        }

        // 输出内容
        $content = $this->view->getContent();
        if (is_resource($content)) {
            fpassthru($content);
            fclose($content);
        } else {
            echo $content;
        }
    }
}
```

### 13.7 中间件设计（接口不变）

```php
<?php

namespace FLEA\Middleware;

/**
 * 中间件接口（保持原样）
 */
interface MiddlewareInterface
{
    /**
     * 处理请求
     *
     * @param callable $next 下一个中间件或请求处理器
     * @return void
     */
    public function handle(callable $next): void;
}
```

**短路中间件（AuthMiddleware）：**
```php
public function handle(callable $next): void
{
    $token = \FLEA\Request::current()->bearerToken();

    if (!$token || !$this->validate($token)) {
        Response::current()->setView(View::json([
            'code'    => -1,
            'message' => 'Unauthorized',
            'data'    => null,
        ]))->withStatus(401);
        return;  // 不调用 $next，短路
    }

    $next();
}
```

**非短路中间件（CorsMiddleware）：**
```php
public function handle(callable $next): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    $next();

    header('Access-Control-Expose-Headers: X-Trace-Id');
}
```

### 13.8 Pipeline 设计（接口不变）

```php
class Pipeline
{
    private array $middlewares = [];

    public static function create(): self
    {
        return new self();
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 执行中间件链
     *
     * @param callable $destination 最终处理器
     * @return void
     */
    public function run(callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn(callable $carry, MiddlewareInterface $mw) => fn() => $mw->handle($carry),
            $destination
        );

        $pipeline();
    }
}
```

### 13.9 Dispatcher 设计

```php
// src/FLEA/Dispatcher/Simple.php

protected function handleActionResult($result)
{
    if ($result instanceof \FLEA\View\ViewInterface) {
        \FLEA\Response::current()->setView($result);
    }
    // void 返回或 null：旧代码已自行输出，不做处理
}
```

### 13.10 FLEA::runMVC() 设计

```php
public static function runMVC(): void
{
    self::init();

    $dispatch = function () {
        $dispatcherClass = self::getAppInf('dispatcher');
        if (!class_exists($dispatcherClass, true)) {
            throw new \FLEA\Exception\ExpectedClass($dispatcherClass);
        }
        $dispatcher = new $dispatcherClass($_GET);
        self::register($dispatcher, $dispatcherClass);
        $dispatcher->dispatching();
    };

    // 一切走 Router
    if (self::getAppInf('routerDefaultRoute') !== false) {
        \FLEA\Router::registerFallback(
            self::getAppInf('defaultController'),
            self::getAppInf('defaultAction')
        );
    }

    // 路由匹配失败
    if (!\FLEA\Router::dispatch()) {
        Response::current()->setView(View::html('404.html', ['statusCode' => 404]))->withStatus(404);
        Signal::publish('response.send');
        Response::send();
        return;
    }

    // 中间件管道
    $pipeline = \FLEA\Middleware\Pipeline::create();
    foreach (self::$middlewares as $mw) {
        $pipeline->pipe($mw);
    }
    foreach (\FLEA\Router::getMatchedMiddlewares() as $mw) {
        $pipeline->pipe($mw);
    }

    $pipeline->run($dispatch);

    // 发布信号并发送
    $response = Response::current();
    if ($response->hasContent()) {
        Signal::publish('response.send');
        $response->send();
    }
    // null: 旧代码已自行输出
}
```

### 13.11 与原方案对比

| 维度 | 原方案 | 优化方案 |
|------|--------|----------|
| **Response** | 单类，容器+发送+信号 | 门面，只负责入口/信号/委托 |
| **HttpResponse** | 不存在 | 新增，数据容器+实际发送 |
| **MiddlewareInterface** | `handle()` 去掉 `void` | 保持 `void` |
| **Pipeline::run()** | 返回 Response | 保持 `void` |
| **中间件改动** | 需改为 `return $next()` / `return Response::error()` | 短路中间件改为设置 Response，非短路中间件不改 |
| **改动范围** | 9 个文件 | 8 个文件（新增 HttpResponse，中间件接口和 Pipeline 不动） |

### 13.12 优化方案特点

| 特点 | 说明 |
|------|------|
| **门面模式** | `Response::current()` 统一入口，与 `Request::current()` 对称 |
| **适配器模式** | `HttpResponse` 是 HTTP 协议的具体实现，不依赖框架组件 |
| **接口稳定** | MiddlewareInterface 和 Pipeline 接口不变，存量中间件无需改动 |
| **职责分离** | Response 管协调，HttpResponse 管数据，Signal 管时机 |
| **可扩展** | 未来可加 `CliResponse`、`WebSocketResponse` 等适配器 |
| **无 exit** | 支持协程/多线程、测试抓取输出、中间件后置逻辑 |

### 13.13 优化方案修改清单

| 步骤 | 文件 | 操作 | 依赖 | 状态 |
|------|------|------|------|------|
| 1 | `src/FLEA/HttpResponse.php` | **新建** | 无 | ☐ |
| 2 | `src/FLEA/Response.php` | **重写**（门面+委托） | Signal, HttpResponse | ☐ |
| 3 | `src/FLEA/Middleware/CorsMiddleware.php` | 不动 | — | ☐ |
| 4 | `src/FLEA/Middleware/AuthMiddleware.php` | 改为 `Response::current()->setView(...)->withStatus(...)` | Response | ☐ |
| 5 | `src/FLEA/Middleware/RateLimitMiddleware.php` | 改为 `Response::current()->setView(...)->withStatus(...)` | Response | ☐ |
| 6 | `src/FLEA/Dispatcher/Simple.php` | `handleActionResult()` 改为设置 Response | Response | ☐ |
| 7 | `src/FLEA.php` | `runMVC()` 改为发布信号+发送 | Signal, Response | ☐ |
| 8 | `FLEA/CHANGES.md` | 追加 | 以上全部 | ☐ |
| 9 | `SPEC.md` | 同步 | 以上全部 | ☐ |
| 10 | `GIT_COMMIT.md` | 追加 | 以上全部 | ☐ |