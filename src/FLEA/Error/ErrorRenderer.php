<?php

namespace FLEA\Error;

/**
 * 错误页面渲染器
 *
 * 负责渲染异常和错误的 HTML 页面，支持开发模式和生产模式。
 * 开发模式显示详细的堆栈跟踪和源码上下文，生产模式显示友好的错误页面。
 *
 * 主要功能：
 * - 开发模式：详细错误页面（堆栈跟踪、源码、请求信息）
 * - 生产模式：友好错误页面（HTTP 状态码、Trace ID）
 * - 自定义错误视图支持（应用层优先）
 * - Trace ID 追踪（与日志系统集成）
 *
 * 错误视图查找顺序：
 * 1. {errorViewsDir}/{httpCode}.php
 * 2. {errorViewsDir}/default.php
 * 3. views/{httpCode}.php
 * 4. views/500.php（默认）
 *
 * 用法示例：
 * ```php
 * // 设置异常处理器
 * set_exception_handler(function(Throwable $ex) {
 *     if (\FLEA::getAppInf('displayErrors')) {
 *         $renderer = new ErrorRenderer($ex);
 *         echo $renderer->render();
 *     } else {
 *         ErrorRenderer::renderProduction($ex);
 *     }
 * });
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class ErrorRenderer
{
    /**
     * @var \Throwable 异常对象
     */
    private \Throwable $ex;

    /**
     * @var string 请求追踪 ID
     */
    private string $traceId;

    public function __construct(\Throwable $ex)
    {
        $this->ex = $ex;
        $this->traceId = \FLEA::isRegistered(\FLEA\Log::class)
            ? \FLEA::registry(\FLEA\Log::class)->getTraceId()
            : '';
    }

    /**
     * 生产模式错误页渲染
     * 查找顺序：应用层 errorViewsDir → 框架默认 views/500.php
     */
    public static function renderProduction(\Throwable $ex): void
    {
        $traceId = \FLEA::isRegistered(\FLEA\Log::class)
            ? \FLEA::registry(\FLEA\Log::class)->getTraceId()
            : '';

        // HTTP 状态码：优先用异常 code，不合法则用 500
        $code = $ex->getCode();
        $httpCode = ($code >= 400 && $code < 600) ? $code : 500;
        if (!headers_sent()) {
            http_response_code($httpCode);
        }

        // 查找模板：应用层优先
        $appDir  = \FLEA::getAppInf('errorViewsDir');
        $candidates = [
            $appDir  ? rtrim($appDir, '/') . "/{$httpCode}.php"  : null,
            $appDir  ? rtrim($appDir, '/') . '/default.php'       : null,
            __DIR__ . "/views/{$httpCode}.php",
            __DIR__ . '/views/500.php',
        ];

        foreach (array_filter($candidates) as $tpl) {
            if (is_readable($tpl)) {
                include $tpl;
                return;
            }
        }
    }

    public function render(): string
    {
        $ex       = $this->ex;
        $class    = get_class($ex);
        $message  = htmlspecialchars($ex->getMessage(), ENT_QUOTES);
        $traceId  = htmlspecialchars($this->traceId, ENT_QUOTES);
        $frames   = $ex->getTrace();
        $file     = $ex->getFile();
        $line     = $ex->getLine();

        $source   = $this->renderSource($file, $line);
        $stack    = $this->renderStack($frames);
        $request  = $this->renderRequest();
        $context  = $this->renderExceptionContext($ex);
        $css      = $this->css();

        return <<<HTML
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$class}</title>
<style>{$css}</style>
</head>
<body>
<div class="container">

  <div class="header">
    <div class="exception-class">{$class}</div>
    <div class="exception-message">{$message}</div>
    <div class="meta">
      <span class="file">{$file} : {$line}</span>
      {$this->renderTraceId($traceId)}
    </div>
  </div>

  {$context}

  <div class="section">
    <div class="section-title">源码上下文</div>
    {$source}
  </div>

  <div class="section">
    <div class="section-title">调用堆栈</div>
    {$stack}
  </div>

  <div class="section">
    <div class="section-title">请求信息</div>
    {$request}
  </div>

</div>
</body>
</html>
HTML;
    }

    private function renderTraceId(string $traceId): string
    {
        if (!$traceId) { return ''; }
        return <<<HTML
      <span class="trace-id">
        TraceId: <code>{$traceId}</code>
        <button onclick="navigator.clipboard.writeText('{$traceId}')">复制</button>
      </span>
HTML;
    }

    /**
     * 针对特定异常类型渲染额外上下文信息
     */
    private function renderExceptionContext(\Throwable $ex): string
    {
        $rows = [];

        if ($ex instanceof \FLEA\Exception\MissingController || $ex instanceof \FLEA\Exception\MissingAction) {
            $rows = [
                '控制器名'   => $ex->controllerName ?? '-',
                '控制器类'   => $ex->controllerClass ?? '-',
                'Action 名'  => $ex->actionName ?? '-',
                'Action 方法' => $ex->actionMethod ?? '-',
            ];
        } elseif ($ex instanceof \FLEA\Exception\ExpectedClass) {
            $rows = [
                '类名'     => $ex->className ?? '-',
                '类文件'   => $ex->classFile ?? '（未指定）',
                '文件存在' => isset($ex->fileExists) ? ($ex->fileExists ? '是' : '否') : '-',
            ];
        } elseif ($ex instanceof \FLEA\Exception\ExpectedFile) {
            $rows = ['文件路径' => $ex->getMessage()];
        } elseif ($ex instanceof \FLEA\Db\Exception\SqlQuery) {
            $rows = ['SQL' => $ex->sql ?? '-'];
        }

        if (empty($rows)) { return ''; }

        $html = '<div class="section"><div class="section-title">异常上下文</div><table class="ctx-table">';
        foreach ($rows as $label => $value) {
            $label = htmlspecialchars($label, ENT_QUOTES);
            $value = htmlspecialchars((string)$value, ENT_QUOTES);
            $html .= "<tr><th>{$label}</th><td>{$value}</td></tr>";
        }
        return $html . '</table></div>';
    }

    private function renderSource(string $file, int $errorLine, int $context = 10): string
    {
        if (!is_readable($file)) { return '<div class="no-source">源文件不可读</div>'; }

        $lines = file($file);
        $start = max(0, $errorLine - $context - 1);
        $end   = min(count($lines) - 1, $errorLine + $context - 1);

        $html = '<div class="source-wrap"><pre class="source">';
        for ($i = $start; $i <= $end; $i++) {
            $num     = $i + 1;
            $code    = htmlspecialchars($lines[$i], ENT_QUOTES);
            $current = $num === $errorLine ? ' class="error-line"' : '';
            $html   .= "<span{$current}><em>{$num}</em>{$code}</span>";
        }
        return $html . '</pre></div>';
    }

    private function renderStack(array $frames): string
    {
        if (empty($frames)) { return '<div class="empty">无堆栈信息</div>'; }

        $html = '';
        foreach ($frames as $i => $frame) {
            $file     = htmlspecialchars($frame['file'] ?? '[internal]', ENT_QUOTES);
            $line     = $frame['line'] ?? 0;
            $class    = isset($frame['class']) ? htmlspecialchars($frame['class'], ENT_QUOTES) . '::' : '';
            $func     = htmlspecialchars($frame['function'] ?? '', ENT_QUOTES);
            $args     = $this->formatArgs($frame['args'] ?? []);
            $source   = isset($frame['file']) ? $this->renderSource($frame['file'], $frame['line'], 5) : '';

            $html .= <<<HTML
<details>
  <summary><span class="frame-num">#{$i}</span> <span class="frame-fn">{$class}{$func}()</span> <span class="frame-file">{$file}:{$line}</span></summary>
  <div class="frame-body">
    {$source}
    <div class="args-title">参数</div>
    <pre class="args">{$args}</pre>
  </div>
</details>
HTML;
        }
        return $html;
    }

    private function formatArgs(array $args): string
    {
        $out = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $out[] = strlen($arg) > 100 ? '"' . htmlspecialchars(substr($arg, 0, 100), ENT_QUOTES) . '..."' : '"' . htmlspecialchars($arg, ENT_QUOTES) . '"';
            } elseif (is_array($arg)) {
                $out[] = 'array(' . count($arg) . ')';
            } elseif (is_object($arg)) {
                $out[] = get_class($arg);
            } elseif (is_null($arg)) {
                $out[] = 'null';
            } else {
                $out[] = htmlspecialchars((string)$arg, ENT_QUOTES);
            }
        }
        return implode("\n", $out) ?: '(无参数)';
    }

    private function renderRequest(): string
    {
        $url    = htmlspecialchars(($_SERVER['REQUEST_URI'] ?? 'CLI'), ENT_QUOTES);
        $method = htmlspecialchars(($_SERVER['REQUEST_METHOD'] ?? 'CLI'), ENT_QUOTES);
        $get    = htmlspecialchars(json_encode($_GET,  JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES);
        $post   = htmlspecialchars(json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES);

        return <<<HTML
<div class="request-info">
  <div><strong>{$method}</strong> {$url}</div>
  <details><summary>\$_GET</summary><pre>{$get}</pre></details>
  <details><summary>\$_POST</summary><pre>{$post}</pre></details>
</div>
HTML;
    }

    private function css(): string
    {
        return <<<CSS
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #1a1a2e; color: #e0e0e0; font: 14px/1.6 'Consolas','Monaco',monospace; }
.container { max-width: 1100px; margin: 0 auto; padding: 24px; }
.header { background: #16213e; border-left: 4px solid #e74c3c; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
.exception-class { color: #e74c3c; font-size: 18px; font-weight: bold; margin-bottom: 8px; }
.exception-message { color: #f0f0f0; font-size: 15px; margin-bottom: 12px; }
.meta { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; font-size: 12px; color: #888; }
.trace-id code { color: #3498db; }
.trace-id button { margin-left: 6px; padding: 2px 8px; background: #2c3e50; color: #aaa; border: 1px solid #444; border-radius: 3px; cursor: pointer; font-size: 11px; }
.trace-id button:hover { background: #3d5166; }
.section { background: #16213e; border-radius: 4px; margin-bottom: 16px; overflow: hidden; }
.section-title { padding: 10px 16px; background: #0f3460; color: #aaa; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
.source-wrap { overflow-x: auto; }
pre.source { padding: 0; counter-reset: line; }
pre.source span { display: block; padding: 1px 16px; white-space: pre; }
pre.source span em { display: inline-block; width: 40px; color: #555; font-style: normal; user-select: none; }
pre.source span.error-line { background: rgba(231,76,60,0.2); border-left: 3px solid #e74c3c; }
pre.source span.error-line em { color: #e74c3c; }
details { border-bottom: 1px solid #0d1b2a; }
details:last-child { border-bottom: none; }
summary { padding: 10px 16px; cursor: pointer; list-style: none; display: flex; gap: 12px; align-items: baseline; }
summary:hover { background: #1e2d45; }
.frame-num { color: #555; font-size: 11px; min-width: 28px; }
.frame-fn { color: #3498db; }
.frame-file { color: #666; font-size: 12px; margin-left: auto; }
.frame-body { padding: 0 16px 16px; }
.args-title { color: #666; font-size: 11px; margin: 10px 0 4px; text-transform: uppercase; }
pre.args { color: #95a5a6; font-size: 12px; }
.request-info { padding: 16px; }
.request-info > div { margin-bottom: 10px; }
.request-info pre { color: #95a5a6; font-size: 12px; padding: 8px; background: #0d1b2a; border-radius: 3px; overflow-x: auto; }
.no-source, .empty { padding: 16px; color: #666; }
.ctx-table { width: 100%; border-collapse: collapse; padding: 16px; display: block; }
.ctx-table th { width: 120px; padding: 8px 16px; color: #888; font-weight: normal; text-align: left; vertical-align: top; }
.ctx-table td { padding: 8px 16px; color: #e0e0e0; word-break: break-all; font-family: monospace; }
CSS;
    }
}
