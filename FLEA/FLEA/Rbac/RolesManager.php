<?php


/**
 * 定义 FLEA_Rbac_RolesManager 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: RolesManager.php 972 2007-10-09 20:56:54Z qeeyuan $
 */


/**
 * FLEA_Rbac_RolesManager 派生自 FLEA_Db_TableDataGateway，
 * 用于访问保存角色信息的数据表
 *
 * 如果数据表的名字不同，应该从 FLEA_Rbac_RolesManager
 * 派生类并使用自定义的数据表名字、主键字段名等。
 *
 * @package Core
 */
class FLEA_Rbac_RolesManager extends FLEA_Db_TableDataGateway
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
     * 角色名字段
     *
     * @var string
     */
    public $rolesNameField = 'rolename';

    /**
     * 构造函数
     *
     * @param array $params
     *
     * @return FLEA_Rbac_RolesManager
     */
    public function __construct(?array $params = null)
    {
        parent::__construct($params);
    }
}
