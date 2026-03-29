<?php

namespace FLEA\Acl\Table;

/**
 * 用户 - 权限关联表数据网关
 *
 * 用于关联用户和权限的多对多关系中间表。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class UsersHasPermissions extends TableDataGateway
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
