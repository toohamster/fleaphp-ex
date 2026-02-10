<?php


/**
 * 定义 FLEA_Acl_Table_Permissions 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Permissions.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Db_TableDataGateway');
// }}}

/**
 * FLEA_Acl_Table_Permissions 提供了权限数据的存储服务
 *
 * @package Core
 */
class FLEA_Acl_Table_Permissions extends FLEA_Db_TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey = 'permission_id';

    /**
     * 数据表名字
     *
     * @var string
     */
    public $tableName = 'permissions';

}
