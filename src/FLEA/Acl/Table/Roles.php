<?php

namespace FLEA\Acl\Table;

use FLEA\Db\TableDataGateway;

/**
 * 角色表数据网关
 *
 * 提供角色数据的存储服务，支持多对多关联权限。
 *
 * 关联关系：
 * - ManyToMany: 多个权限（通过 roles_has_permissions 中间表）
 *
 * 用法示例：
 * ```php
 * $rolesTable = new Roles();
 *
 * // 查找角色及其权限
 * $role = $rolesTable->find(1, ['permissions']);
 *
 * // 创建角色
 * $roleId = $rolesTable->create(['rolename' => 'editor']);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Roles extends TableDataGateway
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
     * 一个角色对应多个权限，一个权限可以指派给多个角色
     *
     * @var array
     */
    public array $manyToMany = [
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
