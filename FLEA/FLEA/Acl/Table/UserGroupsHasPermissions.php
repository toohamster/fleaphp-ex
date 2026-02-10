<?php


/**
 * 定义 FLEA_Acl_Table_UserGroupsHasPermissions 类
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Core
 * @version $Id: UserGroupsHasPermissions.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Db_TableDataGateway');
// }}}

/**
 * FLEA_Acl_Table_UserGroupsHasPermissions 用于关联用户组和权限
 *
 * @package Core
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Acl_Table_UserGroupsHasPermissions extends FLEA_Db_TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    var $primaryKey = 'user_group_id';

    /**
     * 数据表名称
     *
     * @var string
     */
    var $tableName = 'user_groups_has_permissions';

}
