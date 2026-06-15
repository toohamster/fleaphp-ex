# FLEA Framework Changelog

## v2.3.0 (2026-04-03)

### Response + 中间件集成（门面/适配器模式重构）

- **新增 `FLEA\Internal\Signal`**：内部发布/订阅机制，控制响应发送时机
- **新增 `FLEA\HttpResponse`**：HTTP 响应数据容器 + 实际发送逻辑（纯数据层，不依赖 Signal）
- **重构 `FLEA\Response`**：改为门面（Facade）模式
  - 单例入口：`Response::current()`，与 `Request::current()` 对称
  - 信号订阅：收到 `response.send` 后通知内部 HttpResponse
  - 委托调用：所有数据操作委托给 HttpResponse
  - 保留 `success()`、`error()`、`paginate()` 快捷方法
  - 新增 `setView()` 方法
  - 所有方法不再包含 `exit`
- **中间件接口和 Pipeline 保持稳定**（`handle()` 保持 `void`，`Pipeline::run()` 保持 `void`）
- **重构 `FLEA\Middleware\CorsMiddleware`**：OPTIONS 请求通过 `Response::current()->setView()` 设置响应
- **重构 `FLEA\Middleware\AuthMiddleware`**：认证失败通过 `Response::current()` 设置状态和视图
- **重构 `FLEA\Middleware\RateLimitMiddleware`**：限流通过 `Response::current()` 设置状态和视图
- **重构 `FLEA\Dispatcher\Simple::handleActionResult()`**：ViewInterface 设置到 `Response::current()`
- **重构 `FLEA::runMVC()`**：Pipeline 执行后通过 `Response::current()` 发布信号并发送

### 设计变更

- 移除所有 `exit` 调用，支持协程/多线程、测试抓取输出、中间件后置逻辑
- 中间件通过 `Response::current()` 操作响应，不调用 `send()`
- Controller action 返回 ViewInterface 自动设置到 Response，由 FLEA::runMVC() 统一发送
- 门面模式 + 适配器模式：职责分离，中间件接口稳定，未来可扩展其他协议适配器
