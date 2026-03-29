<?php

namespace FLEA\Acl\Table;

/**
 * 用户组 - 角色关联表数据网关
 *
 * 用于关联用户组和角色的多对多关系中间表。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class UserGroupsHasRoles extends TableDataGateway
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
