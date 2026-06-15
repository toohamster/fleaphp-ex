# FLEA Framework Changelog

## v2.3.0 (2026-04-03)

### Response + 中间件集成（重构）

- **新增 `FLEA\Internal\Signal`**：内部发布/订阅机制，控制响应发送时机
- **重构 `FLEA\Response`**：改为 View 包装器，`send()` 受 Signal 控制，不再包含 `exit`
  - 新增 `fromView()` 工厂方法
  - 新增 `success()`、`error()`、`paginate()` 静态快捷方法（返回 Response 对象，不直接输出）
  - 新增 `withHeader()`、`withStatus()` 链式方法
  - `send()` 只在收到 `response.send` 信号后才能执行，防止中间件中途发送
- **重构 `FLEA\Middleware\MiddlewareInterface`**：`handle()` 不再声明 `void` 返回类型，允许返回 Response
- **重构 `FLEA\Middleware\Pipeline`**：`run()` 返回 Pipeline 执行结果，不再 void
- **重构 `FLEA\Middleware\CorsMiddleware`**：改为返回 `$next()` 结果，OPTIONS 请求返回 Response 而非 exit
- **重构 `FLEA\Middleware\AuthMiddleware`**：认证失败返回 `Response::error()`，通过返回 `$next()` 继续
- **重构 `FLEA\Middleware\RateLimitMiddleware`**：限流返回 `Response::error()`，通过返回 `$next()` 继续
- **重构 `FLEA\Dispatcher\Simple::handleActionResult()`**：包装 ViewInterface 为 Response 返回，不再直接调用 `send()`
- **重构 `FLEA::runMVC()`**：统一使用 Pipeline 执行，发布 `response.send` 信号后再调用 `send()`

### 设计变更

- 移除所有 `exit` 调用，支持协程/多线程、测试抓取输出、中间件后置逻辑
- 中间件遵循洋葱模型，通过返回 Response 短路，不直接调用 `send()`
- Controller action 返回 ViewInterface 自动包装为 Response，由 FLEA::runMVC() 统一发送
