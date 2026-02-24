#!/bin/bash

# PSR-4 数据库类重构脚本
# 用于批量更新数据库相关类的命名空间

echo "=== PSR-4 数据库类重构脚本 ===\n"

# 定义要重构的类
declare -A classes=(
    ["FLEA_Db_TableDataGateway"]="FLEA\\Db\\TableDataGateway"
    ["FLEA_Db_ActiveRecord"]="FLEA\\Db\\ActiveRecord"
    ["FLEA_Db_SqlHelper"]="FLEA\\Db\\SqlHelper"
    ["FLEA_Db_TableLink"]="FLEA\\Db\\TableLink"
)

declare -A exceptions=(
    ["FLEA_Db_Exception_InvalidDSN"]="FLEA\\Db\\Exception\\InvalidDSN"
    ["FLEA_Db_Exception_SqlQuery"]="FLEA\\Db\\Exception\\SqlQuery"
    ["FLEA_Db_Exception_MissingPrimaryKey"]="FLEA\\Db\\Exception\\MissingPrimaryKey"
    ["FLEA_Db_Exception_MetaColumnsFailed"]="FLEA\\Db\\Exception\\MetaColumnsFailed"
    ["FLEA_Db_Exception_MissingLinkOption"]="FLEA\\Db\\Exception\\MissingLinkOption"
    ["FLEA_Db_Exception_MissingLink"]="FLEA\\Db\\Exception\\MissingLink"
    ["FLEA_Db_Exception_InvalidLinkType"]="FLEA\\Db\\Exception\\InvalidLinkType"
    ["FLEA_Db_Exception_InvalidInsertID"]="FLEA\\Db\\Exception\\InvalidInsertID"
)

# 函数：更新文件的命名空间
update_namespace() {
    local file=$1
    local old_class=$2
    local new_class=$3

    echo "正在处理: $file"

    # 创建备份
    cp "$file" "$file.backup"

    # 添加命名空间声明（如果文件是类定义）
    if grep -q "^class $old_class" "$file"; then
        sed -i '' "1a\\
\\
namespace FLEA\Db;
\\
" "$file"
        echo "  ✓ 添加命名空间: FLEA\\Db"
    fi

    # 更新类名
    sed -i '' "s/class $old_class /class $(basename $new_class)/g" "$file"

    # 更新异常引用
    sed -i '' 's/new FLEA_Exception_/new \\FLEA\\Exception\\/' "$file"
    sed -i '' 's/throw new FLEA_Exception_/throw new \\FLEA\\Exception\\/' "$file"
    sed -i '' 's/extends FLEA_Exception/extends \\FLEA\\Exception/' "$file"
    sed -i '' 's/instanceof FLEA_Exception/instanceof \\FLEA\\Exception/' "$file"

    # 更新配置引用
    sed -i '' 's/FLEA_Config::getInstance()/\\FLEA\\Config::getInstance()/g' "$file"

    # 更新其他数据库类引用
    sed -i '' 's/FLEA_Db_/\\FLEA\\Db\\/' "$file"
    sed -i '' 's/extends FLEA_Db_Driver_/extends \\FLEA\\Db\\Driver\\/' "$file"
    sed -i '' 's/extends FLEA_Db_TableLink_/extends \\FLEA\\Db\\TableLink\\/' "$file"

    echo "  ✓ 更新类引用\n"
}

echo "由于这是一个大规模重构，建议采用以下方式：\n"

echo "1. 手动重构核心类（TableDataGateway, ActiveRecord）\n"
echo "2. 使用脚本辅助重构其他类\n"
echo "3. 详细计划请参考 DB_PSR4_REFACTORING_PLAN.md\n"

echo "创建重构工具... "

# 创建一个 PHP 重构脚本
cat > /tmp/refactor_db_classes.php << 'EOF'
<?php
/**
 * PSR-4 数据库类重构辅助脚本
 */

function refactorClassFile($file, $oldClass, $newNamespace, $newClassName) {
    echo "正在处理: $file\n";

    $content = file_get_contents($file);

    // 创建备份
    copy($file, $file . '.backup');

    // 检查是否已有命名空间
    if (strpos($content, 'namespace ') !== false) {
        echo "  ⚠ 文件已有命名空间，跳过\n";
        return false;
    }

    // 找到类的开始位置
    if (!preg_match('/^class\s+' . preg_quote($oldClass) . '/m', $content)) {
        echo "  ⚠ 未找到类定义，跳过\n";
        return false;
    }

    // 在 <?php 标签后添加命名空间
    if (strpos($content, '<?php') === 0) {
        $content = preg_replace(
            '/^<\?php\n/',
            "<?php\n\nnamespace $newNamespace;\n\n",
            $content,
            1
        );
    }

    // 更新类名
    $content = preg_replace(
        '/^class\s+' . preg_quote($oldClass) . '/m',
        "class $newClassName",
        $content
    );

    // 更新异常引用
    $content = str_replace('new FLEA_Exception_', 'new \FLEA\Exception\\', $content);
    $content = str_replace('throw new FLEA_Exception_', 'throw new \FLEA\Exception\\', $content);
    $content = str_replace('extends FLEA_Exception', 'extends \FLEA\Exception', $content);
    $content = str_replace('instanceof FLEA_Exception', 'instanceof \FLEA\Exception', $content);

    // 更新配置引用
    $content = str_replace('FLEA_Config::getInstance()', '\FLEA\Config::getInstance()', $content);

    // 更新数据库类引用
    $content = str_replace('FLEA_Db_', '\FLEA\Db\\', $content);

    // 更新驱动类引用
    $content = str_replace('FLEA_Db_Driver_', '\FLEA\Db\Driver\\', $content);

    // 更新表链接类引用
    $content = str_replace('FLEA_Db_TableLink_', '\FLEA\Db\TableLink\\', $content);

    // 更新注解中的类名
    $content = str_replace('FLEA_Db_', '\FLEA\Db\\', $content);

    // 保存文件
    file_put_contents($file, $content);

    echo "  ✓ 重构完成\n\n";
    return true;
}

echo "重构辅助脚本已创建\n";
EOF

php /tmp/refactor_db_classes.php

echo "\n=== 数据库类重构说明 ===\n"
echo "由于重构涉及大量文件和复杂依赖关系，建议按以下顺序进行：\n\n"
echo "1. 先重构异常类（最简单，无依赖）\n"
echo "2. 然后重构表链接类（依赖 TableLink）\n"
echo "3. 然后重构驱动类（依赖 Abstract）\n"
echo "4. 最后重构核心类（依赖最多）\n\n"
echo "每个类重构后，立即运行测试验证。\n"
