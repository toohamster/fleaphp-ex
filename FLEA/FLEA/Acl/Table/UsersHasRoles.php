<?php

namespace FLEA\Acl\Table;


/**
 * 定义 \FLEA\Acl\Table\UsersHasRoles 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: UsersHasRoles.php 1060 2008-05-04 05:02:59Z qeeyuan $
 */


/**
 * \FLEA\Acl\Table\UsersHasRoles 用于关联用户和角色
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class UsersHasRoles extends \FLEA\Db\TableDataGateway
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
    public string $tableName = 'users_has_roles';

}
