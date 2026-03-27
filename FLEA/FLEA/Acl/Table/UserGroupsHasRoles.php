<?php

namespace FLEA\Acl\Table;

/**
 * \FLEA\Acl\Table\UserGroupsHasRoles 用于关联用户组和角色
 *
 */
class UserGroupsHasRoles extends \FLEA\Db\TableDataGateway
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
    public string $tableName = 'user_groups_has_roles';

}
