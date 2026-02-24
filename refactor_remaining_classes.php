<?php

/**
 * 批量转换剩余的PSR-4类
 */

$classesToConvert = [
    // Rbac/Exception classes
    [
        'file' => 'FLEA/FLEA/Rbac/Exception/InvalidACT.php',
        'oldClass' => 'FLEA_Rbac_Exception_InvalidACT',
        'newNamespace' => 'FLEA\\Rbac\\Exception',
        'newClass' => 'InvalidACT'
    ],
    [
        'file' => 'FLEA/FLEA/Rbac/Exception/InvalidACTFile.php',
        'oldClass' => 'FLEA_Rbac_Exception_InvalidACTFile',
        'newNamespace' => 'FLEA\\Rbac\\Exception',
        'newClass' => 'InvalidACTFile'
    ],

    // Dispatcher/Exception classes
    [
        'file' => 'FLEA/FLEA/Dispatcher/Exception/CheckFailed.php',
        'oldClass' => 'FLEA_Dispatcher_Exception_CheckFailed',
        'newNamespace' => 'FLEA\\Dispatcher\\Exception',
        'newClass' => 'CheckFailed'
    ],

    // Db/Driver classes
    [
        'file' => 'FLEA/FLEA/Db/Driver/Sqlitepdo.php',
        'oldClass' => 'FLEA_Db_Driver_Sqlite',
        'newNamespace' => 'FLEA\\Db\\Driver',
        'newClass' => 'Sqlitepdo'
    ],

    // Helper classes
    [
        'file' => 'FLEA/FLEA/Helper/Verifier.php',
        'oldClass' => 'FLEA_Helper_Verifier',
        'newNamespace' => 'FLEA\\Helper',
        'newClass' => 'Verifier'
    ],
    [
        'file' => 'FLEA/FLEA/Helper/Pager.php',
        'oldClass' => 'FLEA_Helper_Pager',
        'newNamespace' => 'FLEA\\Helper',
        'newClass' => 'Pager'
    ],

    // Exception classes
    [
        'file' => 'FLEA/FLEA/Exception/CacheDisabled.php',
        'oldClass' => 'FLEA_Exception_CacheDisabled',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'CacheDisabled'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/ExpectedClass.php',
        'oldClass' => 'FLEA_Exception_ExpectedClass',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'ExpectedClass'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/InvalidArguments.php',
        'oldClass' => 'FLEA_Exception_InvalidArguments',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'InvalidArguments'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/MissingArguments.php',
        'oldClass' => 'FLEA_Exception_MissingArguments',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'MissingArguments'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/MissingAction.php',
        'oldClass' => 'FLEA_Exception_MissingAction',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'MissingAction'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/MissingController.php',
        'oldClass' => 'FLEA_Exception_MissingController',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'MissingController'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/ValidationFailed.php',
        'oldClass' => 'FLEA_Exception_ValidationFailed',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'ValidationFailed'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/FileOperation.php',
        'oldClass' => 'FLEA_Exception_FileOperation',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'FileOperation'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/NotImplemented.php',
        'oldClass' => 'FLEA_Exception_NotImplemented',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'NotImplemented'
    ],
    [
        'file' => 'FLEA/FLEA/Exception/MustOverwrite.php',
        'oldClass' => 'FLEA_Exception_MustOverwrite',
        'newNamespace' => 'FLEA\\Exception',
        'newClass' => 'MustOverwrite'
    ],

    // Acl/Exception classes
    [
        'file' => 'FLEA/FLEA/Acl/Exception/UserGroupNotFound.php',
        'oldClass' => 'FLEA_Acl_Exception_UserGroupNotFound',
        'newNamespace' => 'FLEA\\Acl\\Exception',
        'newClass' => 'UserGroupNotFound'
    ],
];

foreach ($classesToConvert as $class) {
    echo "Converting {$class['oldClass']}...\n";

    $content = file_get_contents($class['file']);

    // 替换类名
    $content = str_replace(
        "class {$class['oldClass']}",
        "class {$class['newClass']}",
        $content
    );

    // 添加 namespace 声明（在 <?php 之后）
    $content = preg_replace(
        '/(<\?php\s+)/',
        "$1\nnamespace {$class['newNamespace']};\n",
        $content
    );

    // 替换父类引用 FLEA_Exception → \FLEA\Exception
    $content = preg_replace(
        '/extends FLEA_Exception/',
        'extends \\FLEA\\Exception',
        $content
    );

    // 替换所有的 extends FLEA_Exception 为 extends \FLEA\Exception
    $content = str_replace('extends FLEA_Exception', 'extends \\FLEA\\Exception', $content);

    // 替换 throw new FLEA_Exception 为 throw new \FLEA\Exception
    $content = preg_replace(
        '/throw new FLEA_Exception\(/',
        'throw new \\FLEA\\Exception(',
        $content
    );

    file_put_contents($class['file'], $content);

    echo "✓ Converted {$class['oldClass']} to {$class['newNamespace']}\\{$class['newClass']}\n";
}

echo "\n✓ All remaining classes converted to PSR-4!\n";
