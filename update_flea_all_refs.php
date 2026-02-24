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
    // Controller references
    'FLEA_Controller_Action' => '\\FLEA\\Controller\\Action',
    'FLEA_Dispatcher_Auth' => '\\FLEA\\Dispatcher\\Auth',
    'FLEA_Dispatcher_Simple' => '\\FLEA\\Dispatcher\\Simple',

    // RBAC references
    'FLEA_Rbac' => '\\FLEA\\Rbac',
    'FLEA_Acl' => '\\FLEA\\Acl',

    // Helper references
    'FLEA_Helper_Array' => '\\FLEA\\Helper\\Array',
    'FLEA_Helper_FileSystem' => '\\FLEA\\Helper\\FileSystem',
    'FLEA_Helper_Verifier' => '\\FLEA\\Helper\\Verifier',
    'FLEA_Helper_Pager' => '\\FLEA\\Helper\\Pager',
    'FLEA_Helper_Image' => '\\FLEA\\Helper\\Image',
    'FLEA_Helper_Simple' => '\\FLEA\\Helper\\Simple',
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
