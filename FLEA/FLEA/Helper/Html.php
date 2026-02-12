<?php


/**
 * 定义一组便于生成表单元控件的方法
 *
 * @author toohamster
 * @package Core
 * @version $Id: Html.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * 生成一个下拉列表框
 *
 * @param string $name
 * @param array $arr
 * @param mixed $selected
 * @param string $extra
 */
function html_dropdown_list(string $name, array $arr, $selected = null, ?string $extra = null): void
{
    echo "<select name=\"{$name}\" {$extra} >\n";
    foreach ($arr as $value => $title) {
        echo '<option value="' . h($value) . '"';
        if ($selected == $value) { echo ' selected'; }
        echo '>' . h($title) . "&nbsp;&nbsp;</option>\n";
    }
    echo "</select>\n";
}

/**
 * 生成一组单选框
 *
 * @param string $name
 * @param array $arr
 * @param mixed $checked
 * @param string $separator
 * @param string $extra
 */
function html_radio_group(string $name, array $arr, $checked = null, string $separator = '', ?string $extra = null): void
{
    $ix = 0;
    foreach ($arr as $value => $title) {
        $value_h = h($value);
        $title = t($title);
        echo "<input name=\"{$name}\" type=\"radio\" id=\"{$name}_{$ix}\" value=\"{$value_h}\" ";
        if ($value == $checked) {
            echo "checked=\"checked\"";
        }
        echo " {$extra} />";
        echo "<label for=\"{$name}_{$ix}\">{$title}</label>";
        echo $separator;
        $ix++;
        echo "\n";
    }
}

/**
 * 生成一组多选框
 *
 * @param string $name
 * @param array $arr
 * @param array $selected
 * @param string $separator
 * @param string $extra
 */
function html_checkbox_group(string $name, array $arr, $selected = [], string $separator = '', ?string $extra = null): void
{
    $ix = 0;
    if (!is_array($selected)) {
        $selected = array($selected);
    }
    foreach ($arr as $value => $title) {
        $value_h = h($value);
        $title = t($title);
        echo "<input name=\"{$name}[]\" type=\"checkbox\" id=\"{$name}_{$ix}\" value=\"{$value_h}\" ";
        if (in_array($value, $selected)) {
            echo "checked=\"checked\"";
        }
        echo " {$extra} />";
        echo "<label for=\"{$name}_{$ix}\">{$title}</label>";
        echo $separator;
        $ix++;
        echo "\n";
    }
}

/**
 * 生成一个多选框
 *
 * @param string $name
 * @param int $value
 * @param boolean $checked
 * @param string $label
 * @param string $extra
 */
function html_checkbox(string $name, int $value = 1, bool $checked = false, string $label = '', ?string $extra = null): void
{
    echo "<input name=\"{$name}\" type=\"checkbox\" id=\"{$name}_1\" value=\"{$value}\"";
    if ($checked) { echo " checked"; }
    echo " {$extra} />\n";
    if ($label) {
        echo "<label for=\"{$name}_1\">" . h($label) . "</label>\n";
    }
}

/**
 * 生成一个文本输入框
 *
 * @param string $name
 * @param string $value
 * @param int $width
 * @param int $maxLength
 * @param string $extra
 */
function html_textbox(string $name, string $value = '', ?int $width = null, ?int $maxLength = null, ?string $extra = null): void
{
    echo "<input name=\"{$name}\" type=\"text\" value=\"" . h($value) . "\" ";
    if ($width) {
        echo "size=\"{$width}\" ";
    }
    if ($maxLength) {
        echo "maxlength=\"{$maxLength}\" ";
    }
    echo " {$extra} />\n";
}

/**
 * 生成一个密码输入框
 *
 * @param string $name
 * @param string $value
 * @param int $width
 * @param int $maxLength
 * @param string $extra
 */
function html_password(string $name, string $value = '', ?int $width = null, ?int $maxLength = null, ?string $extra = null): void
{
    echo "<input name=\"{$name}\" type=\"password\" value=\"" . h($value) . "\" ";
    if ($width) {
        echo "size=\"{$width}\" ";
    }
    if ($maxLength) {
        echo "maxlength=\"{$maxLength}\" ";
    }
    echo " {$extra} />\n";
}

/**
 * 生成一个多行文本输入框
 *
 * @param string $name
 * @param string $value
 * @param int $width
 * @param int $height
 * @param string $extra
 */
function html_textarea(string $name, string $value = '', ?int $width = null, ?int $height = null, ?string $extra = null): void
{
    echo "<textarea name=\"{$name}\"";
    if ($width) { echo "cols=\"{$width}\" "; }
    if ($height) { echo "rows=\"{$height}\" "; }
    echo " {$extra} >";
    echo h($value);
    echo "</textarea>\n";
}

/**
 * 生成一个隐藏域
 *
 * @param string $name
 * @param string $value
 * @param string $extra
 */
function html_hidden(string $name, string $value = '', ?string $extra = null): void
{
    echo "<input name=\"{$name}\" type=\"hidden\" value=\"";
    echo h($value);
    echo "\" {$extra} />\n";
}

/**
 * 生成一个文件上传域
 *
 * @param string $name
 * @param int $width
 * @param string $extra
 */
function html_filefield(string $name, ?int $width = null, ?string $extra = null): void
{
    echo "<input name=\"{$name}\" type=\"file\"";
    if ($width) {
        echo " size=\"{$width}\"";
    }
    echo " {$extra} />\n";
}

/**
 * 生成 form 标记
 *
 * @param string $name
 * @param string $action
 * @param string $method
 * @param string $onsubmit
 * @param string $extra
 */
function html_form(string $name, string $action, string $method='post', string $onsubmit='', ?string $extra = null): void
{
    echo "<form name=\"{$name}\" action=\"{$action}\" method=\"{$method}\" ";
    if ($onsubmit) {
        echo "onsubmit=\"{$onsubmit}\"";
    }
    echo " {$extra} >\n";
}

/**
 * 关闭 form 标记
 */
function html_form_close(): void
{
    echo "</form>\n";
}
