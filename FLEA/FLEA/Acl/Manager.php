<?php

namespace FLEA\Acl;

class Manager
{
    /**
     * 所有使用到的表数据对象类名称
     * @var array
     */
    public array $tableClass = [
        'users' =>                  \FLEA\Acl\Table\Users::class,
        'roles' =>                  \FLEA\Acl\Table\Roles::class,
        'userGroups' =>             \FLEA\Acl\Table\UserGroups::class,
        'permissions' =>            \FLEA\Acl\Table\Permissions::class,
        'userGroupsHasRoles' =>     \FLEA\Acl\Table\UserGroupsHasRoles::class,
        'userGroupsHasPermissions' => \FLEA\Acl\Table\UserGroupsHasPermissions::class,
        'userHasRoles' =>           \FLEA\Acl\Table\UsersHasRoles::class,
        'userHasPermissions' =>     \FLEA\Acl\Table\UsersHasPermissions::class,
    ];

    public function __construct(array $tableClass = [])
    {
        $this->tableClass = array_merge($this->tableClass, (array)$tableClass);
    }

    /**
     * 获取指定用户，及其权限信息
     * @param array $conditions
     */
    public function getUserWithPermissions($conditions): ?array
    {
        $tableUsers = \FLEA::getSingleton($this->tableClass['users']);
        /* @var $tableUsers \FLEA\Acl\Table\Users */
        $user = $tableUsers->find($conditions);
        if (empty($user)) { return null; }

        // 取得用户所在用户组的层次数据
        $tableUserGroups = \FLEA::getSingleton($this->tableClass['userGroups']);
        /* @var $tableUserGroups \FLEA\Acl\Table\UserGroups */
        $rowset = $tableUserGroups->getPath($user['user_group_id']);

        // 找出用户组的单一路径（从根到当前组）
        $ret  = array_to_tree($rowset, 'user_group_id', 'parent_id', 'subgroups', true);
        $refs = $ret['refs'];

        $groupid = $user['user_group_id'];
        $path = [];
        while (isset($refs[$groupid])) {
            array_unshift($path, $refs[$groupid]);
            $groupid = $refs[$groupid]['parent_id'];
        }

        // 整理角色信息：沿路径继承，后者可覆盖前者
        $userRoles = [];
        foreach ($path as $group) {
            foreach ((array)($group['roles'] ?? []) as $role) {
                $roleid = $role['role_id'];
                if ($role['_join_is_include']) {
                    $userRoles[$roleid] = ['role_id' => $roleid, 'name' => $role['name']];
                } else {
                    unset($userRoles[$roleid]);
                }
            }
        }

        // 用户自身的角色覆盖用户组继承的角色
        foreach ((array)($user['roles'] ?? []) as $role) {
            $roleid = $role['role_id'];
            if ($role['_join_is_include']) {
                $userRoles[$roleid] = ['role_id' => $roleid, 'name' => $role['name']];
            } else {
                unset($userRoles[$roleid]);
            }
        }

        $user['roles'] = $userRoles;
        return $user;
    }
}
