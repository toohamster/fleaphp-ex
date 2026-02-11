<?php


/**
 * 定义 FLEA_Acl_Table_Roles 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Roles.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */


/**
 * FLEA_Acl_Table_Roles 提供了角色数据的存储服务
 *
 * @package Core
 */
class FLEA_Acl_Table_Roles extends FLEA_Db_TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey = 'role_id';

    /**
     * 数据表名字
     *
     * @var string
     */
    public $tableName = 'roles';

    /**
     * 一个角色对应多个权限，一个权限可以指派给多个角色
     *
     * @var array
     */
    public $manyToMany = array(
        array(
            'tableClass' => 'FLEA_Acl_Table_Permissions',
            'foreignKey' => 'role_id',
            'assocForeignKey' => 'permission_id',
            'joinTable' => 'roles_has_permissions',
            'mappingName' => 'permissions',
        ),
    );

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
}
