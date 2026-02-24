<?php

/**
 * 更新所有文件中的PSR-0类名注释为PSR-4命名空间
 */

$replacements = [
    // Rbac/Exception classes
    'FLEA_Rbac_Exception_InvalidACT' => '\FLEA\Rbac\Exception\InvalidACT',
    'FLEA_Rbac_Exception_InvalidACTFile' => '\FLEA\Rbac\Exception\InvalidACTFile',

    // Dispatcher/Exception classes
    'FLEA_Dispatcher_Exception_CheckFailed' => '\FLEA\Dispatcher\Exception\CheckFailed',

    // Db/Driver classes
    'FLEA_Db_Driver_Abstract' => '\FLEA\Db\Driver\AbstractDriver',
    'FLEA_Db_Driver_Mysql' => '\FLEA\Db\Driver\Mysql',
    'FLEA_Db_Driver_Mysqlt' => '\FLEA\Db\Driver\Mysqlt',
    'FLEA_Db_Driver_Sqlitepdo' => '\FLEA\Db\Driver\Sqlitepdo',

    // Db classes
    'FLEA_Db_ActiveRecord' => '\FLEA\Db\ActiveRecord',
    'FLEA_Db_SqlHelper' => '\FLEA\Db\SqlHelper',
    'FLEA_Db_TableDataGateway' => '\FLEA\Db\TableDataGateway',
    'FLEA_Db_TableLink' => '\FLEA\Db\TableLink',
    'FLEA_Db_TableLink_HasOneLink' => '\FLEA\Db\TableLink\HasOneLink',
    'FLEA_Db_TableLink_BelongsToLink' => '\FLEA\Db\TableLink\BelongsToLink',
    'FLEA_Db_TableLink_HasManyLink' => '\FLEA\Db\TableLink\HasManyLink',
    'FLEA_Db_TableLink_ManyToManyLink' => '\FLEA\Db\TableLink\ManyToManyLink',

    // Db Exception classes
    'FLEA_Db_Exception_InvalidDSN' => '\FLEA\Db\Exception\InvalidDSN',
    'FLEA_Db_Exception_InvalidInsertID' => '\FLEA\Db\Exception\InvalidInsertID',
    'FLEA_Db_Exception_InvalidLinkType' => '\FLEA\Db\Exception\InvalidLinkType',
    'FLEA_Db_Exception_MetaColumnsFailed' => '\FLEA\Db\Exception\MetaColumnsFailed',
    'FLEA_Db_Exception_MissingDSN' => '\FLEA\Db\Exception\MissingDSN',
    'FLEA_Db_Exception_MissingLink' => '\FLEA\Db\Exception\MissingLink',
    'FLEA_Db_Exception_MissingLinkOption' => '\FLEA\Db\Exception\MissingLinkOption',
    'FLEA_Db_Exception_MissingPrimaryKey' => '\FLEA\Db\Exception\MissingPrimaryKey',
    'FLEA_Db_Exception_PrimaryKeyExists' => '\FLEA\Db\Exception\PrimaryKeyExists',
    'FLEA_Db_Exception_SqlQuery' => '\FLEA\Db\Exception\SqlQuery',

    // Helper classes
    'FLEA_Helper_Array' => '\FLEA\Helper\Array',
    'FLEA_Helper_FileSystem' => '\FLEA\Helper\FileSystem',
    'FLEA_Helper_Html' => '\FLEA\Helper\Html',
    'FLEA_Helper_Pager' => '\FLEA\Helper\Pager',
    'FLEA_Helper_Verifier' => '\FLEA\Helper\Verifier',
    'FLEA_Helper_Yaml' => '\FLEA\Helper\Yaml',
    'FLEA_Helper_FileUploader' => '\FLEA\Helper\FileUploader',
    'FLEA_Helper_FileUploader_File' => '\FLEA\Helper\FileUploader\File',
    'FLEA_Helper_Image' => '\FLEA\Helper\Image',
    'FLEA_Helper_ImgCode' => '\FLEA\Helper\ImgCode',
    'FLEA_Helper_SendFile' => '\FLEA\Helper\SendFile',

    // Exception classes
    'FLEA_Exception' => '\FLEA\Exception',
    'FLEA_Exception_CacheDisabled' => '\FLEA\Exception\CacheDisabled',
    'FLEA_Exception_ExpectedClass' => '\FLEA\Exception\ExpectedClass',
    'FLEA_Exception_ExpectedFile' => '\FLEA\Exception\ExpectedFile',
    'FLEA_Exception_FileOperation' => '\FLEA\Exception\FileOperation',
    'FLEA_Exception_InvalidArguments' => '\FLEA\Exception\InvalidArguments',
    'FLEA_Exception_MissingAction' => '\FLEA\Exception\MissingAction',
    'FLEA_Exception_MissingArguments' => '\FLEA\Exception\MissingArguments',
    'FLEA_Exception_MissingController' => '\FLEA\Exception\MissingController',
    'FLEA_Exception_MustOverwrite' => '\FLEA\Exception\MustOverwrite',
    'FLEA_Exception_NotImplemented' => '\FLEA\Exception\NotImplemented',
    'FLEA_Exception_NotExistsKeyName' => '\FLEA\Exception\NotExistsKeyName',
    'FLEA_Exception_ExistsKeyName' => '\FLEA\Exception\ExistsKeyName',
    'FLEA_Exception_TypeMismatch' => '\FLEA\Exception\TypeMismatch',
    'FLEA_Exception_ValidationFailed' => '\FLEA\Exception\ValidationFailed',

    // Root classes
    'FLEA_Controller_Action' => '\FLEA\Controller\Action',
    'FLEA_Dispatcher_Auth' => '\FLEA\Dispatcher\Auth',
    'FLEA_Dispatcher_Simple' => '\FLEA\Dispatcher\Simple',
    'FLEA_View_Simple' => '\FLEA\View\Simple',
    'FLEA_Session_Db' => '\FLEA\Session\Db',
    'FLEA_Rbac' => '\FLEA\Rbac',
    'FLEA_Rbac_RolesManager' => '\FLEA\Rbac\RolesManager',
    'FLEA_Rbac_UsersManager' => '\FLEA\Rbac\UsersManager',
    'FLEA_Acl' => '\FLEA\Acl',
    'FLEA_Acl_Manager' => '\FLEA\Acl\Manager',
    'FLEA_Acl_Exception_UserGroupNotFound' => '\FLEA\Acl\Exception\UserGroupNotFound',
];

function updateCommentsInDirectory($dir, $replacements) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $updatedFiles = 0;

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        // Skip test and error files
        if (strpos($file->getPathname(), '_Errors') !== false) {
            continue;
        }
        if (strpos($file->getPathname(), 'test') !== false) {
            continue;
        }
        if (strpos($file->getPathname(), 'DEBUG') !== false ||
            strpos($file->getPathname(), 'DEPLOY') !== false) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $originalContent = $content;
        $modified = false;

        // Replace all old class names with new namespaces
        foreach ($replacements as $oldName => $newName) {
            if (strpos($content, $oldName) !== false) {
                $content = str_replace($oldName, $newName, $content);
                $modified = true;
            }
        }

        // Write back only if modified
        if ($modified) {
            file_put_contents($file->getPathname(), $content);
            $updatedFiles++;
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }

    return $updatedFiles;
}

// Start from FLEA/FLEA directory
$directory = 'FLEA/FLEA';
$updatedCount = updateCommentsInDirectory($directory, $replacements);

echo "\n=========================================\n";
echo "注释更新完成\n";
echo "=========================================\n";
echo "共更新了 {$updatedCount} 个文件\n";
