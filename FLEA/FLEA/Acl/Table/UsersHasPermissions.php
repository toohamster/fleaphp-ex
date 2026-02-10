<?php


/**
 * 定义 FLEA_Acl_Table_UsersHasPermissions 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: UsersHasPermissions.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Db_TableDataGateway');
// }}}

/**
 * FLEA_Acl_Table_UsersHasPermissions 用于关联用户和权限
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Acl_Table_UsersHasPermissions extends FLEA_Db_TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**
     * 数据表名称
     *
     * @var string
     */
    public $tableName = 'users_has_permissions';

}
