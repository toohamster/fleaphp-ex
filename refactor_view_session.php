#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for View and Session Classes
 */

$subdirFiles = [
    'FLEA/FLEA/View/Simple.php' => [
        'oldClass' => 'FLEA_View_Simple',
        'newClass' => 'Simple',
        'namespace' => 'FLEA\\View',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
        ],
    ],
    'FLEA/FLEA/Session/Db.php' => [
        'oldClass' => 'FLEA_Session_Db',
        'newClass' => 'Db',
        'namespace' => 'FLEA\\Session',
        'replacements' => [
            'FLEA_Config' => '\\FLEA\\Config',
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($subdirFiles as $file => $config) {
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
