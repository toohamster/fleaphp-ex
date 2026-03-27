<?php

namespace FLEA\Acl\Table;

/**
 * \FLEA\Acl\Table\UsersHasPermissions 用于关联用户和权限
 *
 */
class UsersHasPermissions extends \FLEA\Db\TableDataGateway
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
    public string $tableName = 'users_has_permissions';

}
