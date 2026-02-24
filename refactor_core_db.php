#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for Core Database Classes
 * Refactors FLEA_Db_* classes to FLEA\Db\* namespace
 */

$coreDbFiles = [
    'FLEA/FLEA/Db/ActiveRecord.php' => [
        'oldClass' => 'FLEA_Db_ActiveRecord',
        'newNamespace' => 'FLEA\\Db',
        'newClass' => 'ActiveRecord',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Db/SqlHelper.php' => [
        'oldClass' => 'FLEA_Db_SqlHelper',
        'newNamespace' => 'FLEA\\Db',
        'newClass' => 'SqlHelper',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
        ],
    ],
    'FLEA/FLEA/Db/TableLink.php' => [
        'oldClass' => 'FLEA_Db_TableLink',
        'newNamespace' => 'FLEA\\Db',
        'newClass' => 'TableLink',
        'replacements' => [
            'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
            'FLEA_Db_Exception_MissingLinkOption' => '\\FLEA\\Db\\Exception\\MissingLinkOption',
            'FLEA_Db_Exception_InvalidLinkType' => '\\FLEA\\Db\\Exception\\InvalidLinkType',
            'FLEA_Exception_NotImplemented' => '\\FLEA\\Exception\\NotImplemented',
            'FLEA_Db_SqlHelper' => '\\FLEA\\Db\\SqlHelper',
            'FLEA_Db_Driver_Abstract' => '\\FLEA\\Db\\Driver\\Abstract',
            'FLEA_Db_TableLink' => '\\FLEA\\Db\\TableLink',
            'FLEA_Db_HasOneLink' => '\\FLEA\\Db\\TableLink\\HasOneLink',
            'FLEA_Db_BelongsToLink' => '\\FLEA\\Db\\TableLink\\BelongsToLink',
            'FLEA_Db_HasManyLink' => '\\FLEA\\Db\\TableLink\\HasManyLink',
            'FLEA_Db_ManyToManyLink' => '\\FLEA\\Db\\TableLink\\ManyToManyLink',
        ],
    ],
];

$successCount = 0;
$failCount = 0;

foreach ($coreDbFiles as $file => $config) {
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
        $content = preg_replace("/^<\?php\n\n?/", "<?php\n\nnamespace {$config['newNamespace']};\n\n", $content, 1);
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
