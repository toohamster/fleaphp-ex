#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for FLEA Root Classes
 */

$rootFiles = [
    'FLEA/FLEA/Ajax.php' => [
        'oldClass' => 'FLEA_Ajax',
        'newClass' => 'Ajax',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
        ],
    ],
    'FLEA/FLEA/Language.php' => [
        'oldClass' => 'FLEA_Language',
        'newClass' => 'Language',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Config' => '\\FLEA\\Config',
        ],
    ],
    'FLEA/FLEA/Log.php' => [
        'oldClass' => 'FLEA_Log',
        'newClass' => 'Log',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Config' => '\\FLEA\\Config',
        ],
    ],
    'FLEA/FLEA/WebControls.php' => [
        'oldClass' => 'FLEA_WebControls',
        'newClass' => 'WebControls',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Helper_Array' => '\\FLEA\\Helper\\Array',
        ],
    ],
    'FLEA/FLEA/Rbac.php' => [
        'oldClass' => 'FLEA_Rbac',
        'newClass' => 'Rbac',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Config' => '\\FLEA\\Config',
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Exception_InvalidACTFile' => '\\FLEA\\Exception\\InvalidACTFile',
            'FLEA_Exception_InvalidACT' => '\\FLEA\\Exception\\InvalidACT',
            'FLEA_Helper_Array' => '\\FLEA\\Helper\\Array',
        ],
    ],
    'FLEA/FLEA/Acl.php' => [
        'oldClass' => 'FLEA_Acl',
        'newClass' => 'Acl',
        'namespace' => 'FLEA',
        'replacements' => [
            'FLEA_Config' => '\\FLEA\\Config',
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Exception_MissingACTFile' => '\\FLEA\\Exception\\MissingACTFile',
            'FLEA_Helper_Array' => '\\FLEA\\Helper\\Array',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($rootFiles as $file => $config) {
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
