<?php

namespace FLEA\Acl\Table;

use FLEA\Db\TableDataGateway;

/**
 * 权限表数据网关
 *
 * 提供权限数据的存储服务。
 *
 * 用法示例：
 * ```php
 * $permissionsTable = new Permissions();
 *
 * // 查找所有权限
 * $permissions = $permissionsTable->findAll();
 *
 * // 创建权限
 * $permissionId = $permissionsTable->create([
 *     'name' => 'post.edit',
 *     'description' => '编辑帖子',
 * ]);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Permissions extends TableDataGateway
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey = 'permission_id';

    /**
     * 数据表名字
     *
     * @var string
     */
    public string $tableName = 'permissions';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
}
