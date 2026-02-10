<?php
/**
 * Script to convert var declarations to visibility modifiers for PHP 7.4
 */

function convertVarToVisibility(string $filePath): void
{
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }

    $content = file_get_contents($filePath);
    $original = $content;

    // Pattern to match var declarations
    // This pattern handles:
    // - var $property
    // - var $property = value
    // - Multiple var declarations on one line
    $pattern = '/^\s*var\s+([^\s;=]+)(\s*=\s*[^;]+)?;/m';

    $content = preg_replace($pattern, '    public $1$2;', $content);

    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Converted: $filePath\n";
    } else {
        echo "No changes: $filePath\n";
    }
}

// Process all PHP files in FLEA directory
$files = [
    __DIR__ . '/FLEA/FLEA.php',
    __DIR__ . '/FLEA/FLEA/Db/TableDataGateway.php',
    __DIR__ . '/FLEA/FLEA/Db/ActiveRecord.php',
    __DIR__ . '/FLEA/FLEA/Db/Driver/Abstract.php',
    __DIR__ . '/FLEA/FLEA/Controller/Action.php',
    __DIR__ . '/FLEA/FLEA/Dispatcher/Simple.php',
    __DIR__ . '/FLEA/FLEA/Dispatcher/Auth.php',
    __DIR__ . '/FLEA/FLEA/Rbac.php',
    __DIR__ . '/FLEA/FLEA/Ajax.php',
    __DIR__ . '/FLEA/FLEA/WebControls.php',
    __DIR__ . '/FLEA/FLEA/View/Simple.php',
    __DIR__ . '/FLEA/FLEA/Language.php',
    __DIR__ . '/FLEA/FLEA/Log.php',
    __DIR__ . '/FLEA/FLEA/Acl.php',
    __DIR__ . '/FLEA/FLEA/Db/TableLink.php',
    __DIR__ . '/FLEA/FLEA/Rbac/UsersManager.php',
    __DIR__ . '/FLEA/FLEA/Rbac/RolesManager.php',
    __DIR__ . '/FLEA/FLEA/Session/Db.php',
    __DIR__ . '/FLEA/FLEA/Helper/Pager.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        convertVarToVisibility($file);
    }
}

echo "\nBatch conversion complete.\n";
