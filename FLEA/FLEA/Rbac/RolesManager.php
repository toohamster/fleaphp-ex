<?php

namespace FLEA\Rbac;


/**
 * 定义 \FLEA\Rbac_RolesManager 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: RolesManager.php 972 2007-10-09 20:56:54Z qeeyuan $
 */


/**
 * \FLEA\Rbac_RolesManager 派生自 \FLEA\Db\TableDataGateway，
 * 用于访问保存角色信息的数据表
 *
 * 如果数据表的名字不同，应该从 \FLEA\Rbac_RolesManager
 * 派生类并使用自定义的数据表名字、主键字段名等。
 *
 * @package Core
 */
class RolesManager extends \FLEA\Db\TableDataGateway
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
    public string $tableName = 'roles';

    /**
     * 角色名字段
     *
     * @var string
     */
    public $rolesNameField = 'rolename';

    /**
     * 构造函数
     *
     * @param array $params
     */
    public function __construct(?array $params = null)
    {
        parent::__construct($params);
    }
}
