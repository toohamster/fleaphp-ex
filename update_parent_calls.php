#!/usr/bin/env php
<?php

// 批量更新父类构造函数调用脚本

$replacements = [
    'parent::FLEA_Exception(' => 'parent::__construct(',
    'parent::FLEA_Db_TableDataGateway(' => 'parent::__construct(',
    'parent::FLEA_Db_TableLink(' => 'parent::__construct(',
    'parent::FLEA_Dispatcher_Simple(' => 'parent::__construct(',
    'parent::FLEA_Db_Driver_Abstract(' => 'parent::__construct(',
    'parent::FLEA_Rbac_UsersManager(' => 'parent::__construct(',
    'parent::FLEA_Rbac_RolesManager(' => 'parent::__construct(',
];

$basePath = __DIR__ . '/FLEA';

foreach ($replacements as $old => $new) {
    echo "正在处理: $old -> $new\n";
    
    // 使用find命令查找所有PHP文件并进行替换
    $command = "find '$basePath' -name '*.php' -exec sed -i '' 's/$old/$new/g' {} +";
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ 完成替换: $old\n";
    } else {
        echo "✗ 替换失败: $old\n";
    }
}

echo "父类构造函数调用更新完成！\n";