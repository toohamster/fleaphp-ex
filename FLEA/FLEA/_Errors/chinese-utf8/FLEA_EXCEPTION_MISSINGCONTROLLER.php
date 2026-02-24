<?php
require(__DIR__ . '/../_common/header.php');
/* @var $ex FLEA_Exception_MissingController */
?>

<h1>请求的控制器 <strong><?php echo $ex->controllerName; ?></strong> 没有定义</h1>

<div class="error">
<h2>详细错误原因：</h2>
您请求访问控制器 <strong><?php echo $ex->controllerName; ?></strong>
的动作 <strong><?php echo $ex->actionName; ?></strong>。<br />
控制器 <strong><?php echo $ex->controllerName; ?></strong>
对应的类 <strong><?php echo $ex->controllerClass; ?></strong>
不存在。
</div>

<p>
<?php dump($ex->arguments, '调用参数'); ?>
</p>

<div class="tip">
<h2>解决：</h2>
请检查 <strong><?php echo $ex->controllerClass; ?></strong> 类是否已定义。

<?php
// 将命名空间类名转换为文件路径用于显示
$controllerClassFilename = str_replace('\\', DIRECTORY_SEPARATOR, $ex->controllerClass) . '.php';
?>
<p><strong><?php echo $controllerClassFilename; ?></strong></p>
</div>

<?php
$code = <<<EOT
<?php

// {$controllerClassFilename}

class {$ex->controllerClass} extends FLEA_Controller_Action
{

    function {$ex->actionMethod}()
    {

    }
}
EOT;
__error_highlight_string($code);
?>

<div class="track">
<?php __error_dump_trace($ex); ?>
</div>
