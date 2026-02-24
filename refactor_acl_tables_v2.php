#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for ACL Table Classes
 */

$aclTableFiles = [
    'FLEA/FLEA/Acl/Table/Roles.php' => [
        'oldClass' => 'FLEA_Acl_Table_Roles',
        'newClass' => 'Roles',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/Users.php' => [
        'oldClass' => 'FLEA_Acl_Table_Users',
        'newClass' => 'Users',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Rbac_UsersManager' => '\\FLEA\\Rbac\\UsersManager',
        ],
    ],
    'FLEA/FLEA/Acl/Table/UsersHasRoles.php' => [
        'oldClass' => 'FLEA_Acl_Table_UsersHasRoles',
        'newClass' => 'UsersHasRoles',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/UsersHasPermissions.php' => [
        'oldClass' => 'FLEA_Acl_Table_UsersHasPermissions',
        'newClass' => 'UsersHasPermissions',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/Permissions.php' => [
        'oldClass' => 'FLEA_Acl_Table_Permissions',
        'newClass' => 'Permissions',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/UserGroups.php' => [
        'oldClass' => 'FLEA_Acl_Table_UserGroups',
        'newClass' => 'UserGroups',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/UserGroupsHasPermissions.php' => [
        'oldClass' => 'FLEA_Acl_Table_UserGroupsHasPermissions',
        'newClass' => 'UserGroupsHasPermissions',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Acl/Table/UserGroupsHasRoles.php' => [
        'oldClass' => 'FLEA_Acl_Table_UserGroupsHasRoles',
        'newClass' => 'UserGroupsHasRoles',
        'namespace' => 'FLEA\\Acl\\Table',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($aclTableFiles as $file => $config) {
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
