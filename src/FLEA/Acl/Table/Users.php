<?php

namespace FLEA\Acl\Table;

/**
 * 用户表数据网关
 *
 * 提供用户数据的存储服务，继承自 UsersManager，支持多对多关联。
 *
 * 关联关系：
 * - BelongsTo: 属于一个用户组（user_group_id）
 * - ManyToMany: 多个角色（通过 UsersHasRoles 中间表）
 * - ManyToMany: 多个权限（通过 UsersHasPermissions 中间表）
 *
 * 用法示例：
 * ```php
 * $usersTable = new Users();
 *
 * // 查找用户及其关联数据
 * $user = $usersTable->find(1, ['group', 'roles', 'permissions']);
 *
 * // 创建用户
 * $userId = $usersTable->create([
 *     'username' => 'newuser',
 *     'email' => 'user@example.com',
 *     'user_group_id' => 1,
 * ]);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Rbac\UsersManager
 */
class Users extends \FLEA\Rbac\UsersManager
{
    /**
     * 用户属于一个用户组
     *
     * @var array
     */
    public array $belongsTo = [
        [
            'tableClass' => \FLEA\Acl\Table\UserGroups::class,
            'foreignKey' => 'user_group_id',
            'mappingName' => 'group',
        ],
    ];

    public array $manyToMany = [
        [
            'tableClass' => \FLEA\Acl\Table\Roles::class,
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'role_id',
            'joinTableClass' => \FLEA\Acl\Table\UsersHasRoles::class,
            'mappingName' => 'roles',
        ],
        [
            'tableClass' => \FLEA\Acl\Table\Permissions::class,
            'foreignKey' => 'user_id',
            'assocForeignKey' => 'permission_id',
            'joinTableClass' => \FLEA\Acl\Table\UsersHasPermissions::class,
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
