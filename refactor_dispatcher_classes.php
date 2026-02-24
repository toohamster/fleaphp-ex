#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for Dispatcher Classes
 */

$dispatcherFiles = [
    'FLEA/FLEA/Dispatcher/Simple.php' => [
        'oldClass' => 'FLEA_Dispatcher_Simple',
        'newClass' => 'Simple',
        'namespace' => 'FLEA\\Dispatcher',
        'replacements' => [
            'FLEA_Controller_Action' => '\\FLEA\\Controller\\Action',
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Exception_MissingController' => '\\FLEA\\Exception\\MissingController',
            'FLEA_Exception_MissingAction' => '\\FLEA\\Exception\\MissingAction',
        ],
    ],
    'FLEA/FLEA/Dispatcher/Auth.php' => [
        'oldClass' => 'FLEA_Dispatcher_Auth',
        'newClass' => 'Auth',
        'namespace' => 'FLEA\\Dispatcher',
        'replacements' => [
            'FLEA_Controller_Action' => '\\FLEA\\Controller\\Action',
            'FLEA_Dispatcher_Simple' => '\\FLEA\\Dispatcher\\Simple',
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Exception_MissingController' => '\\FLEA\\Exception\\MissingController',
            'FLEA_Exception_MissingAction' => '\\FLEA\\Exception\\MissingAction',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($dispatcherFiles as $file => $config) {
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
    $content = str_replace("extends FLEA_Dispatcher_Simple", "extends \\FLEA\\Dispatcher\\Simple", $content);
    $content = str_replace("extends FLEA_Dispatcher_Auth", "extends \\FLEA\\Dispatcher\\Auth", $content);

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
