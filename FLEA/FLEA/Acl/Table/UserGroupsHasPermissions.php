<?php

namespace FLEA\Acl\Table;

/**
 * \FLEA\Acl\Table\UserGroupsHasPermissions 用于关联用户组和权限
 *
 */
class UserGroupsHasPermissions extends \FLEA\Db\TableDataGateway
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
    public string $tableName = 'user_groups_has_permissions';

}
