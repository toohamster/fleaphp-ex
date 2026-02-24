<?php

namespace FLEA\Acl\Table;


/**
 * 定义 FLEA_Acl_Table_UserGroupsHasPermissions 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: UserGroupsHasPermissions.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */


/**
 * FLEA_Acl_Table_UserGroupsHasPermissions 用于关联用户组和权限
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class UserGroupsHasPermissions extends \FLEA\Db\TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey = 'user_group_id';

    /**
     * 数据表名称
     *
     * @var string
     */
    public $tableName = 'user_groups_has_permissions';

}
