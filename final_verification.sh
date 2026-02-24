#!/bin/bash
# Final PSR-4 refactoring comprehensive verification script
# Uses php74 for all syntax checks

echo "=== Complete PSR-4 Refactoring Verification ==="
echo ""

PHP_CMD="php74"
ERRORS=0

check_files() {
    local desc="$1"
    shift
    echo "Checking $desc..."
    for file in "$@"; do
        if [ -f "$file" ]; then
            if $PHP_CMD -l "$file" 2>&1 | grep -q "Parse error"; then
                echo "  ✗ $file - SYNTAX ERROR"
                ERRORS=$((ERRORS + 1))
            else
                echo "  ✓ $file"
            fi
        else
            echo "  ✗ $file - FILE NOT FOUND"
            ERRORS=$((ERRORS + 1))
        fi
    done
    echo ""
}

# Check core database classes
check_files "Core DB classes" \
    FLEA/FLEA/Db/ActiveRecord.php \
    FLEA/FLEA/Db/SqlHelper.php \
    FLEA/FLEA/Db/TableLink.php \
    FLEA/FLEA/Db/TableDataGateway.php

# Check driver classes
check_files "Driver classes" \
    FLEA/FLEA/Db/Driver/AbstractDriver.php \
    FLEA/FLEA/Db/Driver/Mysql.php \
    FLEA/FLEA/Db/Driver/Mysqlt.php \
    FLEA/FLEA/Db/Driver/Sqlitepdo.php

# Check TableLink classes
check_files "TableLink classes" \
    FLEA/FLEA/Db/TableLink/HasOneLink.php \
    FLEA/FLEA/Db/TableLink/BelongsToLink.php \
    FLEA/FLEA/Db/TableLink/HasManyLink.php \
    FLEA/FLEA/Db/TableLink/ManyToManyLink.php

# Check DB Exception classes
echo "Checking DB Exception classes..."
for file in FLEA/FLEA/Db/Exception/*.php; do
    if [ -f "$file" ]; then
        if $PHP_CMD -l "$file" 2>&1 | grep -q "Parse error"; then
            echo "  ✗ $file - SYNTAX ERROR"
            ERRORS=$((ERRORS + 1))
        else
            echo "  ✓ $file"
        fi
    fi
done
echo ""

# Check Controller classes
check_files "Controller classes" \
    FLEA/FLEA/Controller/Action.php

# Check Dispatcher classes
check_files "Dispatcher classes" \
    FLEA/FLEA/Dispatcher/Auth.php \
    FLEA/FLEA/Dispatcher/Simple.php \
    FLEA/FLEA/Dispatcher/Exception/CheckFailed.php

# Check RBAC and ACL classes
check_files "RBAC and ACL classes" \
    FLEA/FLEA/Rbac.php \
    FLEA/FLEA/Acl.php \
    FLEA/FLEA/Rbac/RolesManager.php \
    FLEA/FLEA/Rbac/UsersManager.php \
    FLEA/FLEA/Acl/Manager.php \
    FLEA/FLEA/Rbac/Exception/InvalidACT.php \
    FLEA/FLEA/Rbac/Exception/InvalidACTFile.php

# Check ACL Table classes
check_files "ACL Table classes"
for file in FLEA/FLEA/Acl/Table/*.php; do
    if [ -f "$file" ]; then
        if $PHP_CMD -l "$file" 2>&1 | grep -q "Parse error"; then
            echo "  ✗ $file - SYNTAX ERROR"
            ERRORS=$((ERRORS + 1))
        else
            echo "  ✓ $file"
        fi
    fi
done
echo ""

# Check Helper classes
check_files "Helper classes" \
    FLEA/FLEA/Helper/Array.php \
    FLEA/FLEA/Helper/FileSystem.php \
    FLEA/FLEA/Helper/Verifier.php \
    FLEA/FLEA/Helper/Pager.php \
    FLEA/FLEA/Helper/FileUploader.php \
    FLEA/FLEA/Helper/FileUploader/File.php \
    FLEA/FLEA/Helper/Html.php \
    FLEA/FLEA/Helper/Image.php \
    FLEA/FLEA/Helper/ImgCode.php \
    FLEA/FLEA/Helper/SendFile.php \
    FLEA/FLEA/Helper/Yaml.php

# Check View and Session classes
check_files "View and Session classes" \
    FLEA/FLEA/View/Simple.php \
    FLEA/FLEA/Session/Db.php

# Check Root classes
check_files "Root classes" \
    FLEA/FLEA/Ajax.php \
    FLEA/FLEA/Language.php \
    FLEA/FLEA/Log.php \
    FLEA/FLEA/WebControls.php \
    FLEA/FLEA/Rbac.php \
    FLEA/FLEA/Acl.php

# Check FLEA.php
check_files "FLEA.php" FLEA/FLEA.php

echo "=== Summary ==="
if [ $ERRORS -eq 0 ]; then
    echo "✓ All checks passed!"
    exit 0
else
    echo "✗ $ERRORS error(s) found"
    exit 1
fi
