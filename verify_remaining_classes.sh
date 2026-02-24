#!/bin/bash

echo "========================================="
echo "验证所有剩余PSR-4重构的类文件"
echo "========================================="
echo

# 统计总数
total=0
passed=0
failed=0

# 定义所有需要验证的文件
files=(
    "FLEA/FLEA/Rbac/Exception/InvalidACT.php"
    "FLEA/FLEA/Rbac/Exception/InvalidACTFile.php"
    "FLEA/FLEA/Dispatcher/Exception/CheckFailed.php"
    "FLEA/FLEA/Db/Driver/Sqlitepdo.php"
    "FLEA/FLEA/Helper/Verifier.php"
    "FLEA/FLEA/Helper/Pager.php"
    "FLEA/FLEA/Exception/CacheDisabled.php"
    "FLEA/FLEA/Exception/ExpectedClass.php"
    "FLEA/FLEA/Exception/InvalidArguments.php"
    "FLEA/FLEA/Exception/MissingArguments.php"
    "FLEA/FLEA/Exception/MissingAction.php"
    "FLEA/FLEA/Exception/MissingController.php"
    "FLEA/FLEA/Exception/ValidationFailed.php"
    "FLEA/FLEA/Exception/FileOperation.php"
    "FLEA/FLEA/Exception/NotImplemented.php"
    "FLEA/FLEA/Exception/MustOverwrite.php"
    "FLEA/FLEA/Acl/Exception/UserGroupNotFound.php"
)

for file in "${files[@]}"; do
    total=$((total + 1))
    echo -n "验证: $file ... "

    if php74 -l "$file" > /dev/null 2>&1; then
        echo "✓ 通过"
        passed=$((passed + 1))
    else
        echo "✗ 失败"
        failed=$((failed + 1))
        php74 -l "$file"
    fi
done

echo
echo "========================================="
echo "验证完成"
echo "========================================="
echo "总数: $total"
echo "通过: $passed"
echo "失败: $failed"
echo

if [ $failed -eq 0 ]; then
    echo "✓ 所有类文件验证通过！"
    exit 0
else
    echo "✗ 部分类文件验证失败，请检查！"
    exit 1
fi
