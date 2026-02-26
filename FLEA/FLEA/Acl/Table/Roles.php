<?php

namespace FLEA\Acl\Table;


/**
 * 定义 \FLEA\Acl\Table\Roles 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Roles.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */


/**
 * \FLEA\Acl\Table\Roles 提供了角色数据的存储服务
 *
 * @package Core
 */
class Roles extends \FLEA\Db\TableDataGateway
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
    public $manyToMany = [
        [
            'tableClass' => \FLEA\Acl\Table\Permissions::class,
            'foreignKey' => 'role_id',
            'assocForeignKey' => 'permission_id',
            'joinTable' => 'roles_has_permissions',
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
