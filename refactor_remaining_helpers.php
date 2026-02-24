#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for Remaining Helper Classes
 */

$helperFiles = [
    'FLEA/FLEA/Helper/FileUploader.php' => [
        'oldClass' => 'FLEA_Helper_FileUploader',
        'newClass' => 'FileUploader',
        'namespace' => 'FLEA\\Helper',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
            'FLEA_Config' => '\\FLEA\\Config',
        ],
    ],
    'FLEA/FLEA/Helper/FileUploader/File.php' => [
        'oldClass' => 'FLEA_Helper_FileUploader_File',
        'newClass' => 'File',
        'namespace' => 'FLEA\\Helper\\FileUploader',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
        ],
    ],
    'FLEA/FLEA/Helper/Image.php' => [
        'oldClass' => 'FLEA_Helper_Image',
        'newClass' => 'Image',
        'namespace' => 'FLEA\\Helper',
        'replacements' => [
            'FLEA_Exception' => '\\FLEA\\Exception',
        ],
    ],
    'FLEA/FLEA/Helper/ImgCode.php' => [
        'oldClass' => 'FLEA_Helper_ImgCode',
        'newClass' => 'ImgCode',
        'namespace' => 'FLEA\\Helper',
        'replacements' => [],
    ],
    'FLEA/FLEA/Helper/SendFile.php' => [
        'oldClass' => 'FLEA_Helper_SendFile',
        'newClass' => 'SendFile',
        'namespace' => 'FLEA\\Helper',
        'replacements' => [],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($helperFiles as $file => $config) {
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
