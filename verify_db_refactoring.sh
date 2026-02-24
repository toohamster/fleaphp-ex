#!/bin/bash
# Database classes PSR-4 refactoring verification script
# Uses php74 for all syntax checks

echo "=== Database Classes PSR-4 Refactoring Verification ==="
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
