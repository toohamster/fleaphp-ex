<?php

require('FLEA.php');

$dbDSN = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'login' => 'root',
    'password' => '',
    'database' => 'test'
];

FLEA::setAppInf('dbDSN', $dbDSN);
FLEA::setAppInf('internalCacheDir', 'D:/temp');

$acl = FLEA::getSingleton('FLEA_Acl_Manager');
/* @var $acl FLEA_Acl_Manager */

$user = $acl->getUserWithPermissions(['username' => 'liaoyulei']);
dump($user);
