<?php


/**
 * 定义 FLEA_Acl_Table_UserGroupsHasRoles 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: UserGroupsHasRoles.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Db_TableDataGateway');
// }}}

/**
 * FLEA_Acl_Table_UserGroupsHasRoles 用于关联用户组和角色
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Acl_Table_UserGroupsHasRoles extends FLEA_Db_TableDataGateway
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
    public $tableName = 'user_groups_has_roles';

}
