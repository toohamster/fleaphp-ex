<?php

namespace FLEA\Context;

/**
 * 链路追踪上下文
 *
 * 提供分布式链路追踪的 TraceID 和 SpanID 管理。
 * 支持接收外部传入的 trace_id，形成完整的调用链。
 *
 * TraceID 格式：{trace_id}-{span_id}
 * 示例：abc123-1.2.1
 *
 * 主要功能：
 * - 生成唯一 TraceID
 * - 管理 SpanID 层级
 * - 支持外部传入 TraceID（分布式追踪）
 * - 与 Context 集成，方便访问
 *
 * 用法示例：
 * ```php
 * // 框架自动初始化，无需手动调用
 *
 * // 获取 TraceID
 * $traceId = TraceContext::getTraceId();
 *
 * // 获取完整 TraceID（含 SpanID）
 * $fullId = TraceContext::getFullTraceId();
 *
 * // 发起下游调用时生成子 SpanID
 * $childSpan = TraceContext::childSpan();
 * $response = http_post($url, [], ['X-Trace-Id: ' . $childSpan]);
 * ```
 *
 * @package FLEA
 * @subpackage Context
 * @author toohamster
 * @version 2.0.0
 */
class TraceContext
{
    /**
     * @var string 全局追踪 ID
     */
    private static string $traceId = '';

    /**
     * @var string 当前 SpanID
     */
    private static string $spanId = '';

    /**
     * @var bool 是否已初始化
     */
    private static bool $initialized = false;

    /**
     * 初始化 TraceID
     *
     * 优先从请求头获取外部传入的 trace_id，否则生成新的。
     * 支持以下请求头：
     * - X-Trace-Id：FLEA 框架标准
     * - Traceparent：W3C 标准（格式：00-{trace_id}-{span_id}-{flags}）
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // 尝试从请求头获取外部 trace_id
        $externalTraceId = $_SERVER['HTTP_X_TRACE_ID']
                         ?? $_SERVER['HTTP_TRACEPARENT']
                         ?? '';

        if ($externalTraceId) {
            // 解析外部传入的 trace_id
            if (strpos($externalTraceId, '-') !== false) {
                // FLEA 格式：trace_id-span_id
                $parts = explode('-', $externalTraceId);
                self::$traceId = $parts[0];
                self::$spanId = $parts[1] ?? '0';
            } elseif (strpos($externalTraceId, '/') !== false) {
                // W3C Traceparent 格式：00/0af7651916cd43dd8448eb211c80319c/b7ad6b7169203331/01
                // 或简化格式：trace_id/span_id
                $parts = explode('/', $externalTraceId);
                self::$traceId = end($parts);
                self::$spanId = '0';
            } else {
                // 纯 trace_id
                self::$traceId = $externalTraceId;
                self::$spanId = '0';
            }
        } else {
            // 生成新的 trace_id
            self::$traceId = generate_traceid();
            self::$spanId = '0';
        }

        // 存入 Context
        if (\FLEA::isRegistered(\FLEA\Context\Context::class)) {
            flea_context()->set('trace_id', self::$traceId);
            flea_context()->set('span_id', self::$spanId);
        }
    }

    /**
     * 获取 TraceID
     *
     * @return string 全局追踪 ID
     */
    public static function getTraceId(): string
    {
        if (!self::$initialized) {
            self::init();
        }
        return self::$traceId;
    }

    /**
     * 获取 SpanID
     *
     * @return string 当前 SpanID
     */
    public static function getSpanId(): string
    {
        if (!self::$initialized) {
            self::init();
        }
        return self::$spanId;
    }

    /**
     * 获取完整的 TraceID（含 SpanID）
     *
     * @return string 格式：trace_id-span_id
     */
    public static function getFullTraceId(): string
    {
        if (!self::$initialized) {
            self::init();
        }
        return self::$traceId . '-' . self::$spanId;
    }

    /**
     * 生成子 SpanID（用于下游调用）
     *
     * 每次调用会在当前 SpanID 后追加一级，形成层级关系。
     * 例如：0 → 0.1 → 0.1.1 → 0.1.2
     *
     * @return string 新的子 SpanID
     */
    public static function childSpan(): string
    {
        if (!self::$initialized) {
            self::init();
        }

        // 解析当前 span_id 的层级
        if (self::$spanId === '0' || self::$spanId === '') {
            self::$spanId = '1';
        } else {
            // 提取父级 span_id 并递增最后一级
            $parts = explode('.', self::$spanId);
            $lastIndex = count($parts) - 1;
            $parts[$lastIndex] = (int)$parts[$lastIndex] + 1;
            self::$spanId = implode('.', $parts);
        }

        // 更新 Context
        if (\FLEA::isRegistered(\FLEA\Context\Context::class)) {
            flea_context()->set('span_id', self::$spanId);
        }

        return self::$traceId . '-' . self::$spanId;
    }

    /**
     * 从 Context 获取 TraceID（便捷方法）
     *
     * @return string TraceID
     * @deprecated 直接使用 getTraceId()
     */
    public static function fromContext(): string
    {
        return flea_context()->get('trace_id', self::getTraceId());
    }
}
