<?php

namespace FLEA\Acl\Table;


/**
 * 定义 \FLEA\Acl_Table_Users 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Users.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */


/**
 * \FLEA\Acl_Table_Users 提供用户数据的存储服务
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class Users extends \FLEA\Rbac\UsersManager
{
    public $belongsTo = [
        [
            'tableClass' => '\FLEA\Acl_Table_UserGroups',
            'foreignKey' => 'user_group_id',
            'mappingName' => 'group',
        ],
    ];

    public $manyToMany = [
        [
            'tableClass' => '\FLEA\Acl_Table_Roles',
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'role_id',
            'joinTableClass' => '\FLEA\Acl_Table_UsersHasRoles',
            'mappingName' => 'roles',
        ],
        [
            'tableClass' => '\FLEA\Acl_Table_Permissions',
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'permission_id',
            'joinTableClass' => '\FLEA\Acl_Table_UsersHasPermissions',
            'mappingName' => 'permissions',
        ],
    ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
}
