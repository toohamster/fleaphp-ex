#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for RBAC and ACL Manager Classes
 */

$managerFiles = [
    'FLEA/FLEA/Rbac/RolesManager.php' => [
        'oldClass' => 'FLEA_Rbac_RolesManager',
        'newClass' => 'RolesManager',
        'namespace' => 'FLEA\\Rbac',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
            'FLEA_Exception_InvalidACT' => '\\FLEA\\Exception\\InvalidACT',
            'FLEA_Exception_InvalidACTFile' => '\\FLEA\\Exception\\InvalidACTFile',
        ],
    ],
    'FLEA/FLEA/Rbac/UsersManager.php' => [
        'oldClass' => 'FLEA_Rbac_UsersManager',
        'newClass' => 'UsersManager',
        'namespace' => 'FLEA\\Rbac',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Manager.php' => [
        'oldClass' => 'FLEA_Acl_Manager',
        'newClass' => 'Manager',
        'namespace' => 'FLEA\\Acl',
        'replacements' => [
            'FLEA_Helper_Array' => '\\FLEA\\Helper\\Array',
            'FLEA_Exception_MissingACTFile' => '\\FLEA\\Exception\\MissingACTFile',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($managerFiles as $file => $config) {
    echo "Refactoring: $file\n";

    if (!file_exists($file)) {
        echo "  ERROR: File not found\n";
        $failCount++;
        continue;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        echo "  ERROR: Cannot read file\n";
        $failCount++;
        continue;
    }

    // Replace class declaration and extends
    $content = str_replace("class {$config['oldClass']}", "class {$config['newClass']}", $content);

    // Add namespace declaration after <?php
    if (strpos($content, 'namespace ') === false) {
        $content = preg_replace("/^<\?php\n\n?/", "<?php\n\nnamespace {$config['namespace']};\n\n", $content, 1);
    }

    // Replace all class references
    foreach ($config['replacements'] as $oldRef => $newRef) {
        $content = str_replace($oldRef, $newRef, $content);
    }

    // Write back
    if (file_put_contents($file, $content) === false) {
        echo "  ERROR: Cannot write file\n";
        $failCount++;
    } else {
        echo "  SUCCESS\n";
        $successCount++;
    }
}

echo "\n=== Summary ===\n";
echo "Success: $successCount\n";
echo "Failed: $failCount\n";
echo "Total: " . ($successCount + $failCount) . "\n";

exit($failCount > 0 ? 1 : 0);
