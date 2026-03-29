<?php

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;

/**
 * HasOne 关联实现类
 *
 * 封装一对一的 HasOne 关联关系，支持关联查询和关联数据保存。
 * HasOne 关联表示主表记录在关联表中有一条对应的记录。
 *
 * 主要功能：
 * - 构建关联查询 SQL
 * - 保存关联数据（创建或更新）
 * - 自动初始化关联配置
 *
 * 用法示例：
 * ```php
 * // 在 TableDataGateway 子类中定义关联
 * class User extends TableDataGateway {
 *     public $hasMany = [
 *         'profile' => [
 *             'className' => 'Profile',
 *             'foreignKey' => 'user_id',
 *         ],
 *     ];
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     TableLink
 */
class HasOneLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
     *
     * HasOne 关联始终是一对一关系。
     *
     * @var boolean
     */
    public bool $oneToOne = true;

    /**
     * 返回用于查询关联表数据的SQL语句
     *
     * @param string $in
     *
     * @return string
     */
    public function getFindSQL(string $in): string
    {
        if (!$this->initialized) { $this->init(); }
        $fields = $this->qforeignKey . ' AS ' . $this->mainTDG->pka . ', ' . $this->assocTDG->qfields($this->fields);

        $sql = "SELECT {$fields} FROM {$this->assocTDG->qtableName} ";

        return parent::getFindSQLBase($sql, $in);
    }

    /**
     * 创建或更新主表记录时，保存关联的数据
     *
     * @param array $row 要保存的关联数据
     * @param mixed $pkv 主表的主键字段值
     *
     * @return boolean
     */
    public function saveAssocData(array &$row, $pkv): bool
    {
        if (!$this->initialized) { $this->init(); }

        $row[$this->foreignKey] = $pkv;
        return $this->saveAssocDataBase($row);
    }

    /**
     * 完全初始化关联对象
     */
    public function init(): void
    {
        parent::init();
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->mainTDG->primaryKey;
        }
        $this->qforeignKey = $this->dbo->qfield($this->foreignKey, $this->assocTDG->fullTableName, $this->assocTDG->schema);
    }
}
