<?php

namespace FLEA\Rbac;

use FLEA\Db\TableDataGateway;

/**
 * 角色管理器
 *
 * 继承自 TableDataGateway，提供角色表的数据访问接口。
 *
 * 主要功能：
 * - 角色数据的 CRUD 操作
 * - 角色名称字段配置
 *
 * 用法示例：
 * ```php
 * $rolesManager = new RolesManager();
 *
 * // 获取所有角色
 * $roles = $rolesManager->findAll();
 *
 * // 根据 ID 获取角色
 * $role = $rolesManager->findByRoleId(1);
 *
 * // 创建新角色
 * $roleId = $rolesManager->create(['rolename' => 'admin']);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class RolesManager extends TableDataGateway
{
    /**
     * 主键字段名
     * @var string
     */
    public $primaryKey = 'role_id';

    /**
     * 数据表名字
     * @var string
     */
    public string $tableName = 'roles';

    /**
     * 角色名字段
     * @var string
     */
    public string $rolesNameField = 'rolename';

    /**
     * 构造函数
     * @param array $params
     */
    public function __construct(?array $params = null)
    {
        parent::__construct($params);
    }
}
