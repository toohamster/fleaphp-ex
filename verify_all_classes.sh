#!/bin/bash

echo "========================================="
echo "验证所有PSR-4重构的类文件（72个）"
echo "========================================="
echo

# 统计总数
total=0
passed=0
failed=0

# 定义所有需要验证的文件
files=(
    # Database classes (18 classes)
    "FLEA/FLEA/Db/ActiveRecord.php"
    "FLEA/FLEA/Db/Driver/AbstractDriver.php"
    "FLEA/FLEA/Db/Driver/Mysql.php"
    "FLEA/FLEA/Db/Driver/Mysqlt.php"
    "FLEA/FLEA/Db/Driver/Sqlitepdo.php"
    "FLEA/FLEA/Db/SqlHelper.php"
    "FLEA/FLEA/Db/TableDataGateway.php"
    "FLEA/FLEA/Db/TableLink.php"
    "FLEA/FLEA/Db/TableLink/HasOneLink.php"
    "FLEA/FLEA/Db/TableLink/BelongsToLink.php"
    "FLEA/FLEA/Db/TableLink/HasManyLink.php"
    "FLEA/FLEA/Db/TableLink/ManyToManyLink.php"
    "FLEA/FLEA/Db/Exception/InvalidDSN.php"
    "FLEA/FLEA/Db/Exception/InvalidInsertID.php"
    "FLEA/FLEA/Db/Exception/InvalidLinkType.php"
    "FLEA/FLEA/Db/Exception/MetaColumnsFailed.php"
    "FLEA/FLEA/Db/Exception/MissingDSN.php"
    "FLEA/FLEA/Db/Exception/MissingLink.php"
    "FLEA/FLEA/Db/Exception/MissingLinkOption.php"
    "FLEA/FLEA/Db/Exception/MissingPrimaryKey.php"
    "FLEA/FLEA/Db/Exception/PrimaryKeyExists.php"
    "FLEA/FLEA/Db/Exception/SqlQuery.php"

    # Root FLEA classes (6 classes)
    "FLEA/FLEA/Ajax.php"
    "FLEA/FLEA/Language.php"
    "FLEA/FLEA/Log.php"
    "FLEA/FLEA/WebControls.php"
    "FLEA/FLEA/Rbac.php"
    "FLEA/FLEA/Acl.php"

    # View/Session classes (2 classes)
    "FLEA/FLEA/View/Simple.php"
    "FLEA/FLEA/Session/Db.php"

    # Controller class (1 class)
    "FLEA/FLEA/Controller/Action.php"

    # Dispatcher classes (3 classes)
    "FLEA/FLEA/Dispatcher/Simple.php"
    "FLEA/FLEA/Dispatcher/Auth.php"
    "FLEA/FLEA/Dispatcher/Exception/CheckFailed.php"

    # RBAC/ACL Manager classes (3 classes)
    "FLEA/FLEA/Rbac/RolesManager.php"
    "FLEA/FLEA/Rbac/UsersManager.php"
    "FLEA/FLEA/Acl/Manager.php"

    # RBAC Exception classes (2 classes)
    "FLEA/FLEA/Rbac/Exception/InvalidACT.php"
    "FLEA/FLEA/Rbac/Exception/InvalidACTFile.php"

    # ACL Table classes (8 classes)
    "FLEA/FLEA/Acl/Table/Roles.php"
    "FLEA/FLEA/Acl/Table/Users.php"
    "FLEA/FLEA/Acl/Table/UsersHasRoles.php"
    "FLEA/FLEA/Acl/Table/UsersHasPermissions.php"
    "FLEA/FLEA/Acl/Table/Permissions.php"
    "FLEA/FLEA/Acl/Table/UserGroups.php"
    "FLEA/FLEA/Acl/Table/UserGroupsHasPermissions.php"
    "FLEA/FLEA/Acl/Table/UserGroupsHasRoles.php"

    # Helper classes (10 classes - 5 previously + 5 new)
    "FLEA/FLEA/Helper/Array.php"
    "FLEA/FLEA/Helper/FileSystem.php"
    "FLEA/FLEA/Helper/FileUploader.php"
    "FLEA/FLEA/Helper/FileUploader/File.php"
    "FLEA/FLEA/Helper/Html.php"
    "FLEA/FLEA/Helper/Image.php"
    "FLEA/FLEA/Helper/ImgCode.php"
    "FLEA/FLEA/Helper/Pager.php"
    "FLEA/FLEA/Helper/SendFile.php"
    "FLEA/FLEA/Helper/Verifier.php"
    "FLEA/FLEA/Helper/Yaml.php"

    # Exception classes (9 classes)
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

    # Acl Exception class (1 class)
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
    echo "✓ 所有72个类文件验证通过！"
    exit 0
else
    echo "✗ 部分类文件验证失败，请检查！"
    exit 1
fi
