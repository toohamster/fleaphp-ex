<?php

namespace FLEA\Rbac;

class RolesManager extends \FLEA\Db\TableDataGateway
{
    /**
     * 主键字段名
     * @var string
     */
    public $primaryKey = 'role_id';

    /**
     * 数据表名字
     * @var string
     */
    public string $tableName = 'roles';

    /**
     * 角色名字段
     * @var string
     */
    public string $rolesNameField = 'rolename';

    /**
     * 构造函数
     * @param array $params
     */
    public function __construct(?array $params = null)
    {
        parent::__construct($params);
    }
}
