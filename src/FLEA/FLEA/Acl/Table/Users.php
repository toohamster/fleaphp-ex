<?php

namespace FLEA\Acl\Table;

/**
 * \FLEA\Acl\Table\Users 提供用户数据的存储服务
 *
 */
class Users extends \FLEA\Rbac\UsersManager
{
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
