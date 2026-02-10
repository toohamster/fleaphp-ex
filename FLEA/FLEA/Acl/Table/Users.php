<?php


/**
 * 定义 FLEA_Acl_Table_Users 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Users.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Rbac_UsersManager');
// }}}

/**
 * FLEA_Acl_Table_Users 提供用户数据的存储服务
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Acl_Table_Users extends FLEA_Rbac_UsersManager
{
    var $belongsTo = array(
        array(
            'tableClass' => 'FLEA_Acl_Table_UserGroups',
            'foreignKey' => 'user_group_id',
            'mappingName' => 'group',
        ),
    );

    var $manyToMany = array(
        array(
            'tableClass' => 'FLEA_Acl_Table_Roles',
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'role_id',
            'joinTableClass' => 'FLEA_Acl_Table_UsersHasRoles',
            'mappingName' => 'roles',
        ),
        array(
            'tableClass' => 'FLEA_Acl_Table_Permissions',
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'permission_id',
            'joinTableClass' => 'FLEA_Acl_Table_UsersHasPermissions',
            'mappingName' => 'permissions',
        ),
    );
}
