<?php
/**
 * Str::extract() 测试脚本
 *
 * 验证所有测试用例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FLEA\Helper\Str;

echo "=== Str::extract() 测试 ===\n\n";

// 用例 1：基本连字符分隔
echo "用例 1: 基本连字符分隔\n";
$result = Str::extract('380-250-80-j', '{width}-{height}-{quality}-{format}');
print_r($result);
// 期望：['width'=>'380', 'height'=>'250', 'quality'=>'80', 'format'=>'j']

// 用例 2：URL 路径
echo "\n用例 2: URL 路径\n";
$result = Str::extract('/2012/08/12/test.html', '/{year}/{month}/{day}/{title}.html');
print_r($result);
// 期望：['year'=>'2012', 'month'=>'08', 'day'=>'12', 'title'=>'test']

// 用例 3：特殊字符（@、/、: 等）
echo "\n用例 3: 特殊字符\n";
$result = Str::extract('John Doe <john@example.com> (http://example.com)', '{name} <{email}> ({url})');
print_r($result);
// 期望：['name'=>'John Doe', 'email'=>'john@example.com', 'url'=>'http://example.com']

// 用例 4：反引号分隔符 + 空白压缩
echo "\n用例 4: 反引号分隔符 + 空白压缩\n";
$result = Str::extract(
    'from 4th October  to 10th  October',
    'from {from} to {to}',
    ['collapse_whitespace' => true, 'strip_values' => true]
);
print_r($result);
// 期望：['from'=>'4th October', 'to'=>'10th October']

// 用例 5：忽略大小写
echo "\n用例 5: 忽略大小写\n";
$result = Str::extract(
    'Convert 1500 Grams to Kilograms',
    'convert {quantity} {from_unit} to {to_unit}',
    ['case_insensitive' => true]
);
print_r($result);
// 期望：['quantity'=>'1500', 'from_unit'=>'Grams', 'to_unit'=>'Kilograms']

// 用例 6：冒号分隔符 + 空结尾
echo "\n用例 6: 冒号分隔符 + 空结尾\n";
$result = Str::extract(
    'The time is 4:35pm here at Lima, Peru',
    'The time is :time here at :city',
    ['delimiters' => [':', '']]
);
print_r($result);
// 期望：['time'=>'4:35pm', 'city'=>'Lima, Peru']

// 用例 7：失败场景（不匹配）
echo "\n用例 7: 失败场景（不匹配）\n";
$result = Str::extract('/post/abc', '/post/{id}');
print_r($result);
// 期望：[]

// 用例 8：基本单参数
echo "\n用例 8: 基本单参数\n";
$result = Str::extract('hello', '{greeting}');
print_r($result);

// 用例 9：多参数
echo "\n用例 9: 多参数\n";
$result = Str::extract('a b c d e', '{a} {b} {c} {d} {e}');
print_r($result);

// 用例 10：空白压缩
echo "\n用例 10: 空白压缩\n";
$result = Str::extract('hello    world', 'hello {name}', ['collapse_whitespace' => true]);
print_r($result);

echo "\n=== 测试完成 ===\n";
