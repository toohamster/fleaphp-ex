#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for Database Driver Classes
 */

$driverFiles = [
    'FLEA/FLEA/Db/Driver/AbstractDriver.php' => [
        'oldClass' => 'FLEA_Db_Driver_Abstract',
        'newClass' => 'AbstractDriver',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Db_Exception_InvalidDSN' => '\\FLEA\\Db\\Exception\\InvalidDSN',
            'FLEA_Db_Exception_SqlQuery' => '\\FLEA\\Db\\Exception\\SqlQuery',
            'FLEA_Db_Exception_MetaColumnsFailed' => '\\FLEA\\Db\\Exception\\MetaColumnsFailed',
            'FLEA_Db_Exception_InvalidInsertID' => '\\FLEA\\Db\\Exception\\InvalidInsertID',
            'FLEA_Config' => '\\FLEA\\Config',
        ],
    ],
    'FLEA/FLEA/Db/Driver/Mysql.php' => [
        'oldClass' => 'FLEA_Db_Driver_Mysql',
        'newClass' => 'Mysql',
        'replacements' => [
            'FLEA_Db_Driver_Abstract' => '\\FLEA\\Db\\Driver\\AbstractDriver',
            'FLEA_Db_Exception_SqlQuery' => '\\FLEA\\Db\\Exception\\SqlQuery',
        ],
    ],
    'FLEA/FLEA/Db/Driver/Mysqlt.php' => [
        'oldClass' => 'FLEA_Db_Driver_Mysqlt',
        'newClass' => 'Mysqlt',
        'replacements' => [
            'FLEA_Db_Driver_Mysql' => '\\FLEA\\Db\\Driver\\Mysql',
        ],
    ],
    'FLEA/FLEA/Db/Driver/Sqlitepdo.php' => [
        'oldClass' => 'FLEA_Db_Driver_Sqlitepdo',
        'newClass' => 'Sqlitepdo',
        'replacements' => [
            'FLEA_Db_Driver_Abstract' => '\\FLEA\\Db\\Driver\\AbstractDriver',
            'FLEA_Db_Exception_SqlQuery' => '\\FLEA\\Db\\Exception\\SqlQuery',
            'FLEA_Db_Exception_MetaColumnsFailed' => '\\FLEA\\Db\\Exception\\MetaColumnsFailed',
            'FLEA_Db_Exception_InvalidInsertID' => '\\FLEA\\Db\\Exception\\InvalidInsertID',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($driverFiles as $file => $config) {
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

    // Replace class declaration
    $content = str_replace("class {$config['oldClass']}", "class {$config['newClass']}", $content);
    $content = str_replace("abstract class {$config['oldClass']}", "abstract class {$config['newClass']}", $content);

    // Add namespace declaration after <?php
    if (strpos($content, 'namespace ') === false) {
        $content = preg_replace("/^<\?php\n\n?/", "<?php\n\nnamespace FLEA\\Db\\Driver;\n\n", $content, 1);
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
