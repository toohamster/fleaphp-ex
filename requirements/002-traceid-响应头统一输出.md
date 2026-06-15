# 需求分析：TraceID 响应头统一输出

## 需求来源
用户在会话中发现 X-Trace-Id 响应头丢失问题。

## 需求描述

### 背景
1. 框架实现了 TraceContext 用于链路追踪
2. TraceID 在 `FLEA::init()` 中输出为响应头 `X-Trace-Id`
3. 多个地方重复输出该响应头，导致代码冗余

### 目标
- 统一在 `FLEA::init()` 中输出 X-Trace-Id 响应头
- 移除其他地方的重复输出
- 确保异常处理器在 init 之前被调用时也能输出 TraceID

## 实现方案

### 已完成的修改

| 文件 | 修改内容 | 状态 |
|------|----------|------|
| src/FLEA.php | 第 678 行输出 X-Trace-Id | 保留 |
| src/FLEA/Response.php | 移除 sendHeaders() 中的 TraceID 输出 | 已删除 |
| src/Functions.php | 异常处理器保留 TraceID 输出 | 保留（init 前可能需要） |

### 保留 TraceID 输出的场景

1. **FLEA::init()** - 主流程，正常请求都会执行
2. **__FLEA_EXCEPTION_HANDLER** - 异常可能在 init 之前抛出

## 验证方法

```bash
# 启动开发服务器
php bin/flea-cli --project-dir=demo

# 访问页面，检查响应头
curl -I http://127.0.0.1:8081/index.php
```

预期响应头包含：
```
X-Trace-Id: abc123-0
```

## 相关文件

- src/FLEA.php
- src/FLEA/Response.php
- src/Functions.php
- src/FLEA/Context/TraceContext.php
- src/FLEA/Error/ErrorRenderer.php
