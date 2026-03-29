<?php

namespace FLEA\Acl\Table;

/**
 * 用户组表数据网关
 *
 * 提供用户组数据的存储服务，支持层次结构（嵌套集模型）和多对多关联。
 *
 * 使用嵌套集模型（Nested Set Model）存储层次结构：
 * - left_value: 左值
 * - right_value: 右值
 * - parent_id: 父用户组 ID
 *
 * 关联关系：
 * - ManyToMany: 多个角色（通过 UserGroupsHasRoles 中间表）
 * - ManyToMany: 多个权限（通过 UserGroupsHasPermissions 中间表）
 *
 * 主要功能：
 * - 创建用户组（自动维护嵌套集左右值）
 * - 更新用户组信息
 * - 删除用户组及其子树
 * - 获取用户组路径
 * - 获取子用户组
 *
 * 用法示例：
 * ```php
 * $userGroupsTable = new UserGroups();
 *
 * // 创建用户组
 * $groupId = $userGroupsTable->create(['name' => 'Tech Dept'], $parentId);
 *
 * // 获取用户组路径（从根到当前组）
 * $path = $userGroupsTable->getPath($group);
 *
 * // 获取所有子用户组
 * $subTree = $userGroupsTable->getSubTree($group);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class UserGroups extends TableDataGateway
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
    public string $tableName = 'user_groups';

    /**
     * 用户组关联多个角色和权限
     *
     * @var array
     */
    public array $manyToMany = [
        [
            'tableClass' => \FLEA\Acl\Table\Roles::class,
            'foreignKey' => 'user_group_id',
            'assocForeignKey' => 'role_id',
            'joinTableClass' => \FLEA\Acl\Table\UserGroupsHasRoles::class,
            'mappingName' => 'roles',
        ],
        [
            'tableClass' => \FLEA\Acl\Table\Permissions::class,
            'foreignKey' => 'user_group_id',
            'assocForeignKey' => 'permission_id',
            'joinTableClass' => \FLEA\Acl\Table\UserGroupsHasPermissions::class,
            'mappingName' => 'permissions',
        ],
    ];

    /**
     * 根用户组名
     *
     * @var string
     */
    public string $rootGroupName = '_#_ROOT_GROUP_#_';

    /**
     * 添加一个用户组，返回该用户组的 ID
     *
     * @param array $group
     * @param int $parentId
     *
     * @return int
     */
    public function create(array &$group, int $parentId = 0, bool $saveLinks = true): int
    {
        $parentId = (int)$parentId;
        if ($parentId) {
            $parent = parent::find($parentId);
            if (!$parent) {
                // 指定的父用户组不存在
                throw new \FLEA\Acl_Exception_UserGroupNotFound($parentId);
            }
        } else {
            // 如果未指定 $parentId 为 0 或 null，则创建一个顶级用户组
            $parent = parent::find(['name' => $this->rootGroupName]);
            if (!$parent) {
                // 如果根用户组不存在，则自动创建
                $parent = [
                    'name' => $this->rootGroupName,
                    'description' => '',
                    'left_value' => 1,
                    'right_value' => 2,
                    'parent_id' => -1,
                ];
                if (!parent::create($parent)) {
                    return 0;
                }
            }
            // 确保所有 _#_ROOT_GROUP_#_ 的直接子用户组的 parent_id 都为 0
            $parent[$this->primaryKey] = 0;
        }

        $this->dbo->startTrans();

        // 根据父用户组的左值和右值更新数据
        $sql = "UPDATE {$this->fullTableName} SET left_value = left_value + 2 " .
               "WHERE left_value >= {$parent['right_value']}";
        $this->dbo->execute(sql_statement($sql));
        $sql = "UPDATE {$this->fullTableName} SET right_value = right_value + 2 " .
               "WHERE right_value >= {$parent['right_value']}";
        $this->dbo->execute(sql_statement($sql));

        // 插入新用户组记录
        $group['left_value'] = $parent['right_value'];
        $group['right_value'] = $parent['right_value'] + 1;
        $group['parent_id'] = $parent[$this->primaryKey];
        $ret = parent::create($group);

        if ($ret) {
            $this->dbo->completeTrans();
        } else {
            $this->dbo->completeTrans(false);
        }

        return $ret;
    }

    /**
     * 更新用户组信息
     *
     * @param array $group
     *
     * @return boolean
     */
    public function update(array &$group, bool $saveLinks = true): bool
    {
        unset($group['left_value']);
        unset($group['right_value']);
        unset($group['parent_id']);
        return parent::update($group);
    }

    /**
     * 删除一个用户组及其子用户组树
     *
     * @param int $groupId
     *
     * @return boolean
     */
    public function removeByPkv($groupId, bool $removeLink = true): bool
    {
        $group = parent::find((int)$groupId);
        if (!$group) {
            throw new \FLEA\Acl_Exception_UserGroupNotFound($groupId);
        }

        $this->dbo->startTrans();

        $group['left_value'] = (int)$group['left_value'];
        $group['right_value'] = (int)$group['right_value'];
        $span = $group['right_value'] - $group['left_value'] + 1;
        $conditions = "WHERE left_value >= {$group['left_value']} AND right_value <= {$group['right_value']}";

        $rowset = $this->findAll($conditions, null, null, $this->primaryKey, false);
        foreach ($rowset as $row) {
            if (!parent::removeByPkv($row[$this->primaryKey])) {
                $this->dbo->completeTrans(false);
                return false;
            }
        }

        if (!parent::removeByPkv($groupId)) {
            $this->dbo->completeTrans(false);
            return false;
        }

        $sql = "UPDATE {$this->fullTableName} " .
               "SET left_value = left_value - {$span} " .
               "WHERE left_value > {$group['right_value']}";
        if (!$this->dbo->execute(sql_statement($sql))) {
            $this->dbo->completeTrans(false);
            return false;
        }

        $sql = "UPDATE {$this->fullTableName} " .
               "SET right_value = right_value - {$span} " .
               "WHERE right_value > {$group['right_value']}";
        if (!$this->dbo->execute(sql_statement($sql))) {
            $this->dbo->completeTrans(false);
            return false;
        }

        $this->dbo->completeTrans();
        return true;
    }

    /**
     * 返回根用户组到指定用户组路径上的所有用户组
     *
     * 返回的结果不包括“_#_ROOT_GROUP_#_”根用户组各个用户组同级别的其他用户组。
     * 结果集是一个二维数组，可以用 array_to_tree() 函数转换为层次结构（树型）。
     *
     * @param array $group
     *
     * @return array
     */
    public function getPath(array $group): array
    {
        $group['left_value'] = (int)$group['left_value'];
        $group['right_value'] = (int)$group['right_value'];

        $conditions = "left_value <= {$group['left_value']} AND right_value >= {$group['right_value']}";
        $sort = 'left_value ASC';
        $rowset = $this->findAll($conditions, $sort);
        array_shift($rowset);
        return $rowset;
    }

    /**
     * 返回指定用户组的直接子用户组
     *
     * @param array $group
     *
     * @return array
     */
    public function getSubGroups(array $group): array
    {
        $conditions = "parent_id = {$group[$this->primaryKey]}";
        $sort = 'left_value ASC';
        return $this->findAll($conditions, $sort);
    }

    /**
     * 返回指定用户组为根的整个子用户组树
     *
     * @param array $group
     *
     * @return array
     */
    public function getSubTree(array $group): array
    {
        $group['left_value'] = (int)$group['left_value'];
        $group['right_value'] = (int)$group['right_value'];

        $conditions = "left_value BETWEEN {$group['left_value']} AND {$group['right_value']}";
        $sort = 'left_value ASC';
        return $this->findAll($conditions, $sort);
    }

    /**
     * 获取指定用户组同级别的所有用户组
     *
     * @param array $group
     *
     * @return array
     */
    public function getCurrentLevelGroups(array $group): array
    {
        $group['parent_id'] = (int)$group['parent_id'];
        $conditions = "parent_id = {$group['parent_id']}";
        $sort = 'left_value ASC';
        return $this->findAll($conditions, $sort);
    }

    /**
     * 取得所有用户组
     *
     * @return array
     */
    public function getAllGroups(): array
    {
        return parent::findAll('left_value > 1', 'left_value ASC');
    }

    /**
     * 获取所有顶级用户组（既 _#_ROOT_GROUP_#_ 的直接子用户组）
     *
     * @return array
     */
    public function getAllTopGroups(): array
    {
        $conditions = "parent_id = 0";
        $sort = 'left_value ASC';
        return $this->findAll($conditions, $sort);
    }

    /**
     * 计算所有子用户组的总数
     *
     * @param array $group
     *
     * @return int
     */
    public function calcAllChildCount(array $group): int
    {
        return intval(($group['right_value'] - $group['left_value'] - 1) / 2);
    }

}
