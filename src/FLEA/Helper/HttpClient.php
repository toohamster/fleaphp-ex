<?php

namespace FLEA\Helper;

/**
 * HTTP 客户端（轻量级 cURL 封装）
 *
 * 提供简单的服务间 HTTP 调用功能，支持 TraceID 自动传递。
 *
 * 主要功能：
 * - 支持 GET、POST、PUT、DELETE、PATCH 方法
 * - 自动解析 JSON 响应
 * - 支持自定义请求头和超时设置
 * - 自动传递 TraceID（用于链路追踪）
 *
 * 返回结构：
 * ```php
 * [
 *     'success'    => true/false,
 *     'statusCode' => 200,
 *     'headers'    => [...],
 *     'body'       => '...',
 *     'data'       => [...],  // 自动 json_decode 后的数据
 *     'error'      => '...',  // 错误消息
 * ]
 * ```
 *
 * 用法示例：
 * ```php
 * // 简单 GET
 * $result = \FLEA\Helper\HttpClient::get('http://api.example.com/users');
 *
 * // 带选项 GET
 * $result = \FLEA\Helper\HttpClient::get('http://api.example.com/users', [
 *     'headers' => ['X-Custom' => 'value'],
 *     'timeout' => 5,
 * ]);
 *
 * // POST 请求
 * $result = \FLEA\Helper\HttpClient::post('http://api.example.com/users', [
 *     'name' => 'John',
 *     'email' => 'john@example.com',
 * ]);
 *
 * // 检查响应
 * if ($result['success']) {
 *     $data = $result['data'];  // 已自动解析 JSON
 * } else {
 *     $error = $result['error'];
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.2.1
 */
class HttpClient
{
    /**
     * 默认超时时间（秒）
     */
    const DEFAULT_TIMEOUT = 5;

    /**
     * 发送 GET 请求
     *
     * 用法示例：
     * ```php
     * $result = HttpClient::get('http://api.example.com/users');
     * $result = HttpClient::get('http://api.example.com/users', [
     *     'headers' => ['X-Trace-Id' => 'abc123'],
     *     'timeout' => 10,
     * ]);
     * ```
     *
     * @param string $url     请求 URL
     * @param array  $options 选项（headers、timeout 等）
     *
     * @return array 响应结果
     */
    public static function get(string $url, array $options = []): array
    {
        return self::request('GET', $url, null, $options);
    }

    /**
     * 发送 POST 请求
     *
     * 用法示例：
     * ```php
     * $result = HttpClient::post('http://api.example.com/users', [
     *     'name' => 'John',
     *     'email' => 'john@example.com',
     * ]);
     * ```
     *
     * @param string       $url     请求 URL
     * @param array|string $data    请求数据（数组自动转为 JSON）
     * @param array        $options 选项（headers、timeout 等）
     *
     * @return array 响应结果
     */
    public static function post(string $url, $data = null, array $options = []): array
    {
        return self::request('POST', $url, $data, $options);
    }

    /**
     * 发送 PUT 请求
     *
     * 用法示例：
     * ```php
     * $result = HttpClient::put('http://api.example.com/users/1', [
     *     'name' => 'John Updated',
     * ]);
     * ```
     *
     * @param string       $url     请求 URL
     * @param array|string $data    请求数据
     * @param array        $options 选项
     *
     * @return array 响应结果
     */
    public static function put(string $url, $data = null, array $options = []): array
    {
        return self::request('PUT', $url, $data, $options);
    }

    /**
     * 发送 DELETE 请求
     *
     * 用法示例：
     * ```php
     * $result = HttpClient::delete('http://api.example.com/users/1');
     * ```
     *
     * @param string $url     请求 URL
     * @param array  $options 选项
     *
     * @return array 响应结果
     */
    public static function delete(string $url, array $options = []): array
    {
        return self::request('DELETE', $url, null, $options);
    }

    /**
     * 发送 PATCH 请求
     *
     * 用法示例：
     * ```php
     * $result = HttpClient::patch('http://api.example.com/users/1', [
     *     'name' => 'John Updated',
     * ]);
     * ```
     *
     * @param string       $url     请求 URL
     * @param array|string $data    请求数据
     * @param array        $options 选项
     *
     * @return array 响应结果
     */
    public static function patch(string $url, $data = null, array $options = []): array
    {
        return self::request('PATCH', $url, $data, $options);
    }

    /**
     * 发送 HTTP 请求（底层通用方法）
     *
     * @param string       $method  HTTP 方法（GET、POST、PUT 等）
     * @param string       $url     请求 URL
     * @param array|string $data    请求数据
     * @param array        $options 选项
     *
     * @return array 响应结果
     */
    public static function request(string $method, string $url, $data = null, array $options = []): array
    {
        // 初始化 cURL
        $ch = curl_init();

        // 设置 URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // 设置请求方法
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // 超时设置
        $timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        // 返回响应体而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 返回响应头
        curl_setopt($ch, CURLOPT_HEADER, false);

        // 获取响应头信息
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$responseHeaders) {
            $len = strlen($header);
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header, 2);
                $responseHeaders[trim($name)] = trim($value);
            }
            return $len;
        });
        $responseHeaders = [];

        // 设置请求头
        $headers = $options['headers'] ?? [];

        // 自动添加 TraceID（用户未设置时）
        if (empty($headers['X-Trace-Id'])) {
            $traceId = \FLEA\Context\TraceContext::getFullTraceId();
            if ($traceId) {
                $headers['X-Trace-Id'] = $traceId;
            }
        }

        // 设置 Content-Type 和 Accept
        if (empty($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }
        if (empty($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }

        // 格式化请求头
        $headerArray = [];
        foreach ($headers as $name => $value) {
            $headerArray[] = "{$name}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        // 设置请求数据
        if ($data !== null) {
            if (is_array($data)) {
                // 数组自动转为 JSON
                $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $body = $data;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // 执行请求
        $body = curl_exec($ch);

        // 获取状态码
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 检查错误
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        // 关闭 cURL
        curl_close($ch);

        // 构建响应结果
        $result = [
            'success'    => false,
            'statusCode' => $statusCode,
            'headers'    => $responseHeaders,
            'body'       => $body,
            'data'       => null,
            'error'      => null,
        ];

        // 处理错误
        if ($errno !== 0) {
            $result['error'] = sprintf('cURL Error (%d): %s', $errno, $error);
            return $result;
        }

        // 判断是否成功（HTTP 2xx）
        if ($statusCode >= 200 && $statusCode < 300) {
            $result['success'] = true;
        }

        // 自动解析 JSON 响应
        if ($body !== '' && $body !== null) {
            // 检查 Content-Type 是否是 JSON
            $contentType = $responseHeaders['Content-Type'] ?? '';
            if (strpos($contentType, 'application/json') !== false || strpos($contentType, 'text/json') !== false) {
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result['data'] = $decoded;
                }
            } else {
                // 尝试解析（兼容没有 Content-Type 的情况）
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result['data'] = $decoded;
                }
            }
        }

        return $result;
    }
}
