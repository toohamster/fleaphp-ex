#!/usr/bin/env php
<?php
/**
 * Update FLEA.php references to use new PSR-4 namespaces
 */

$file = 'FLEA/FLEA.php';

echo "Updating FLEA.php references\n";

if (!file_exists($file)) {
    echo "  ERROR: File not found\n";
    exit(1);
}

$content = file_get_contents($file);

// Replace class references
$replacements = [
    'FLEA_Db_TableDataGateway' => '\\FLEA\\Db\\TableDataGateway',
    'FLEA_Db_ActiveRecord' => '\\FLEA\\Db\\ActiveRecord',
    'FLEA_Db_SqlHelper' => '\\FLEA\\Db\\SqlHelper',
    'FLEA_Db_TableLink' => '\\FLEA\\Db\\TableLink',
    'FLEA_Db_Driver_Abstract' => '\\FLEA\\Db\\Driver\\AbstractDriver',
    'FLEA_Db_Driver_Mysql' => '\\FLEA\\Db\\Driver\\Mysql',
    'FLEA_Db_Driver_Mysqlt' => '\\FLEA\\Db\\Driver\\Mysqlt',
    'FLEA_Db_Driver_Sqlitepdo' => '\\FLEA\\Db\\Driver\\Sqlitepdo',
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
