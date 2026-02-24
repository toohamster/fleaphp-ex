#!/bin/bash

echo "========================================="
echo "检查所有对象参数引用符号 (&)"
echo "========================================="
echo

echo "说明: 在PHP5+中，对象默认按引用传递，不需要 & 符号"
echo "      只有数组参数需要保留 & 符号"
echo

# 查找函数参数中的对象引用（需要移除）
echo "检查函数参数中的对象引用（需要移除）:"
object_refs=$(grep -rn "function.*(& \$[a-zA-Z_]*," FLEA/FLEA --include="*.php" | grep -v "_Errors" | grep -v "test" | grep -v "DEBUG\|DEPLOY")
if [ -z "$object_refs" ]; then
    echo "✓ 没有找到需要移除的对象参数引用"
else
    echo "$object_refs"
fi
echo

echo "检查函数参数末尾的对象引用（需要移除）:"
object_refs_end=$(grep -rn "function.*(& \$[a-zA-Z_]*)" FLEA/FLEA --include="*.php" | grep -v "_Errors" | grep -v "test" | grep -v "DEBUG\|DEPLOY")
if [ -z "$object_refs_end" ]; then
    echo "✓ 没有找到需要移除的对象参数引用"
else
    echo "$object_refs_end"
fi
echo

# 查找数组参数引用（应该保留）
echo "检查数组参数引用（应该保留）:"
array_refs=$(grep -rn "& \$.*array\|& \$.*row\|& \$.*item\|& \$.*data\|& \$.*rules\|& \$.*assocRowset\|& \$.*fieldValues\|& \$.*reference" FLEA/FLEA --include="*.php" | grep "function" | grep -v "_Errors" | grep -v "test" | grep -v "DEBUG\|DEPLOY")
if [ -z "$array_refs" ]; then
    echo "✓ 没有找到数组参数引用"
else
    echo "$array_refs"
fi
echo

echo "========================================="
echo "检查完成"
echo "========================================="
