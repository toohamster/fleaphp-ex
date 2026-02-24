#!/usr/bin/env php
<?php
/**
 * PSR-4 Refactoring Script for TableDataGateway
 */

$file = 'FLEA/FLEA/Db/TableDataGateway.php';

echo "Refactoring: $file\n";

if (!file_exists($file)) {
    echo "  ERROR: File not found\n";
    exit(1);
}

$content = file_get_contents($file);

// Replace class declaration
$content = str_replace("class FLEA_Db_TableDataGateway", "class TableDataGateway", $content);

// Add namespace declaration after <?php
if (strpos($content, 'namespace ') === false) {
    $content = preg_replace("/^<\?php\n\n?/", "<?php\n\nnamespace FLEA\\Db;\n\n", $content, 1);
}

// Replace class references
$replacements = [
    'FLEA_Config' => '\\FLEA\\Config',
    'FLEA_Db_Driver_Abstract' => '\\FLEA\\Db\\Driver\\AbstractDriver',
    'FLEA_Db_TableLink' => '\\FLEA\\Db\\TableLink',
    'FLEA_Db_Exception_MissingPrimaryKey' => '\\FLEA\\Db\\Exception\\MissingPrimaryKey',
    'FLEA_Db_Exception_MetaColumnsFailed' => '\\FLEA\\Db\\Exception\\MetaColumnsFailed',
    'FLEA_Db_Exception_MissingLink' => '\\FLEA\\Db\\Exception\\MissingLink',
    'FLEA_Db_Exception_MissingLinkOption' => '\\FLEA\\Db\\Exception\\MissingLinkOption',
    'FLEA_Db_Exception_InvalidLinkType' => '\\FLEA\\Db\\Exception\\InvalidLinkType',
    'FLEA_Db_Exception_InvalidInsertID' => '\\FLEA\\Db\\Exception\\InvalidInsertID',
    'FLEA_Db_SqlHelper' => '\\FLEA\\Db\\SqlHelper',
];

foreach ($replacements as $oldRef => $newRef) {
    $content = str_replace($oldRef, $newRef, $content);
}

// Write back
if (file_put_contents($file, $content) === false) {
    echo "  ERROR: Cannot write file\n";
    exit(1);
}

echo "  SUCCESS\n";
exit(0);
