<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>服务器错误</title>
<style>
body { margin: 0; background: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.box { text-align: center; padding: 48px; }
.code { font-size: 72px; font-weight: 700; color: #dee2e6; line-height: 1; }
.title { font-size: 24px; color: #495057; margin: 16px 0 8px; }
.desc { color: #868e96; font-size: 14px; }
.trace { margin-top: 24px; font-size: 12px; color: #ced4da; font-family: monospace; }
</style>
</head>
<body>
<div class="box">
  <div class="code">500</div>
  <div class="title">服务器内部错误</div>
  <div class="desc">抱歉，服务器遇到了一个错误，请稍后再试。</div>
  <?php if (!empty($traceId)): ?>
  <div class="trace">错误追踪码：<?= htmlspecialchars($traceId) ?></div>
  <?php endif; ?>
</div>
</body>
</html>
