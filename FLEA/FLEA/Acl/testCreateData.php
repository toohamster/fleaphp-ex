<?php

require('FLEA.php');

$dbDSN = [
    'driver'    => 'mysqlt',
    'host'      => 'localhost',
    'login'     => 'root',
    'password'  => '',
    'database'  => 'test'
];

FLEA::setAppInf('dbDSN', $dbDSN);
FLEA::setAppInf('internalCacheDir', 'D:/temp');

$dbo =& FLEA::getDBO();
$dbo->startTrans();

/**
 * 建立全部需要的权限
 */
$tablePermissions = FLEA::getSingleton('FLEA_Acl_Table_Permissions');
/* @var $tablePermissions FLEA_Acl_Table_Permissions */
$permissions = [
    ['name' => '/Project/Create'],
    ['name' => '/Project/View'],
    ['name' => '/Project/Edit'],
    ['name' => '/Project/Delete'],
    ['name' => '/Bug/Create'],
    ['name' => '/Bug/View'],
    ['name' => '/Bug/Edit'],
    ['name' => '/Bug/AddComment'],
    ['name' => '/Bug/SetFixed'],
    ['name' => '/Bug/SetClosed'],
    ['name' => '/Bug/Delete'],
];
$tablePermissions->createRowset($permissions);

$permissions = $tablePermissions->findAll();
FLEA::loadHelper('array');
$permissions = array_to_hashmap($permissions, 'name');

/**
 * 建立角色，并将权限绑定到角色上
 */
$tableRoles = FLEA::getSingleton('FLEA_Acl_Table_Roles');
/* @var $tableRoles FLEA_Acl_Table_Roles */
$role = [
    'name' => 'ProjectManager',
    'permissions' => [
        $permissions['/Project/Create'],
        $permissions['/Project/View'],
        $permissions['/Project/Edit'],
        $permissions['/Project/Delete'],
        $permissions['/Bug/Delete'],
    ],
];
$tableRoles->create($role);

$role = [
    'name' => 'Developer',
    'permissions' => [
        $permissions['/Project/View'],
        $permissions['/Bug/View'],
        $permissions['/Bug/AddComment'],
        $permissions['/Bug/SetFixed'],
        $permissions['/Bug/Delete'],
    ],
];
$tableRoles->create($role);

$role = [
    'name' => 'Tester',
    'permissions' => [
        $permissions['/Project/Create'],
        $permissions['/Bug/Create'],
        $permissions['/Bug/Edit'],
        $permissions['/Bug/View'],
        $permissions['/Bug/AddComment'],
        $permissions['/Bug/SetClosed'],
    ],
];
$tableRoles->create($role);

/**
 * 读取所有角色信息，并以角色名为索引
 */
$roles = $tableRoles->findAll();
$roles = array_to_hashmap($roles, 'name');

/**
 * 创建用户组层次，并指定角色
 *
 * 开发组
 *   |
 *   +----- QeePHP Team
 *   |
 *   +----- PHPChina Team
 *   |
 *   \----- 测试组
 */
$tableUserGroups = FLEA::getSingleton('FLEA_Acl_Table_UserGroups');
/* @var $tableUserGroups FLEA_Acl_Table_UserGroups */
$group = [
    'name' => '开发组',
    'roles' => [
        $roles['Developer'],
    ]
];
$tableUserGroups->create($group);
$parent = $tableUserGroups->find(['name' => '开发组']);

$group = [
    'name' => 'QeePHP Team',
    'parent_id' => $parent['user_group_id'],
    'roles' => [
        $roles['Developer'],
    ]
];
$tableUserGroups->create($group);

$group = [
    'name' => 'PHPChina Team',
    'parent_id' => $parent['user_group_id'],
    'roles' => [
        $roles['Developer'],
    ]
];
$tableUserGroups->create($group);

$group = [
    'name' => '测试组',
    'parent_id' => $parent['user_group_id'],
    'roles' => [
        $roles['Tester'],
        /**
         * 将 is_include 指定为 0，表示该用户组排除了”Developer”角色
         */
        array_merge($roles['Developer'], ['#JOIN#' => ['is_include' => 0]]),
    ]
];
$tableUserGroups->create($group);

$groups = $tableUserGroups->findAll();
$groups = array_to_hashmap($groups, 'name');

/**
 * 创建用户，并分配到各个组
 */
$tableUsers = FLEA::getSingleton('FLEA_Acl_Table_Users');
/* @var $tableUsers FLEA_Acl_Table_Users */
$users = [
    [
        'username' => 'liaoyulei',
        'password' => '123456',
        'email' => 'liaoyulei@qeeyuan.com',
        'user_group_id' => $groups['QeePHP Team']['user_group_id'],
    ],
    [
        'username' => 'liwei',
        'password' => '123456',
        'email' => 'liwei@qeeyuan.com',
        'user_group_id' => $groups['QeePHP Team']['user_group_id'],
    ],
    [
        'username' => 'liye',
        'password' => '123456',
        'email' => 'liye@qeeyuan.com',
        'user_group_id' => $groups['QeePHP Team']['user_group_id'],
    ],
    [
        'username' => 'dali',
        'password' => '123456',
        'email' => 'dali@qeeyuan.com',
        'user_group_id' => $groups['QeePHP Team']['user_group_id'],
    ],
];
$tableUsers->createRowset($users);

/**
 * 为用户指派单独的角色
 */
$user = $tableUsers->find(['username' => 'liaoyulei']);
$user['roles'][] = $roles['ProjectManager'];
$tableUsers->update($user);

$user = $tableUsers->find(['username' => 'liye']);
$user['roles'][] = $roles['Tester'];
$tableUsers->update($user);

$user = $tableUsers->find(['username' => 'dali']);
$user['roles'][] = $roles['Tester'];
$tableUsers->update($user);



$users = [
    [
        'username' => '米粒子',
        'password' => '123456',
        'email' => 'milizi@phpchina.com',
        'user_group_id' => $groups['PHPChina Team']['user_group_id'],
    ],
    [
        'username' => '默默',
        'password' => '123456',
        'email' => 'momo@phpchina.com',
        'user_group_id' => $groups['PHPChina Team']['user_group_id'],
    ],
    [
        'username' => '冰刺猬',
        'password' => '123456',
        'email' => 'bingciwei@phpchina.com',
        'user_group_id' => $groups['PHPChina Team']['user_group_id'],
    ],
];
$tableUsers->createRowset($users);

$user = $tableUsers->find(['username' => '米粒子']);
$user['roles'][] = $roles['ProjectManager'];
$tableUsers->update($user);

$users = [
    [
        'username' => '肥同小可',
        'password' => '123456',
        'email' => 'feitongxiaoke@phpchina.com',
        'user_group_id' => $groups['测试组']['user_group_id'],
    ],
    [
        'username' => '雷茂峰',
        'password' => '123456',
        'email' => 'leimaofeng@phpchina.com',
        'user_group_id' => $groups['测试组']['user_group_id'],
    ],
];
$tableUsers->createRowset($users);

$user = $tableUsers->find(['username' => '肥同小可']);
$user['roles'][] = $roles['Developer'];
$tableUsers->update($user);


$dbo->completeTrans();
