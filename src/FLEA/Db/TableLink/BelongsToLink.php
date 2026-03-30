<?php

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;

/**
 * BelongsTo 关联实现类
 *
 * 封装多对一的 BelongsTo 关联关系，表示当前表属于另一个表。
 * BelongsTo 关联的外键字段存储在当前表中，指向关联表的主键。
 *
 * 主要特点：
 * - 构造函数中禁用 linkUpdate/Create/Remove，防止级联操作
 * - 支持反向关联查询
 * - 自动初始化外键配置
 *
 * 用法示例：
 * ```php
 * // Post 表属于 User 表（外键 user_id 在 post 表中）
 * class Post extends TableDataGateway {
 *     public $belongsTo = [
 *         'author' => [
 *             'className' => 'User',
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
class BelongsToLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
     *
     * BelongsTo 关联是一对一关系（多条记录可以属于同一个目标）。
     *
     * @var boolean
     */
    public bool $oneToOne = true;

    /**
     * 构造函数
     *
     * @param array $define
     * @param enum $type
     * @param \FLEA\Db\TableDataGateway $mainTDG
     */
    public function __construct(array $define, int $type, \FLEA\Db\TableDataGateway $mainTDG)
    {
        $this->linkUpdate = $this->linkCreate = $this->linkRemove = false;
        parent::__construct($define, $type, $mainTDG);
    }

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
        $fields = $this->mainTDG->qpk . ' AS ' . $this->mainTDG->pka . ', ' . $this->dbo->qfields($this->fields, $this->assocTDG->fullTableName, $this->assocTDG->schema);

        $sql = "SELECT {$fields} FROM {$this->assocTDG->qtableName} LEFT JOIN {$this->mainTDG->qtableName} ON {$this->mainTDG->qpk} {$in} WHERE {$this->qforeignKey} = {$this->assocTDG->qpk} ";
        $in = '';
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
        if (empty($row)) { return true; }
        if (!$this->initialized) { $this->init(); }
        return $this->saveAssocDataBase($row);
    }

    /**
     * 完全初始化关联对象
     */
    public function init(): void
    {
        parent::init();
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->assocTDG->primaryKey;
        }
        $this->qforeignKey = $this->dbo->qfield($this->foreignKey, $this->mainTDG->fullTableName, $this->mainTDG->schema);
    }
}
