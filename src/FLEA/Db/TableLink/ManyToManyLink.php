<?php

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;
use FLEA\Db\TableDataGateway;

/**
 * ManyToMany 关联实现类
 *
 * 封装多对多的 ManyToMany 关联关系，通过中间表实现双向关联。
 * ManyToMany 关联允许主表和关联表之间建立多对多的映射关系。
 *
 * 主要功能：
 * - 构建关联查询 SQL（支持中间表为实体或非实体）
 * - 保存关联数据（插入、更新、删除中间表记录）
 * - 支持中间表为实体表（可存储额外数据）
 * - 支持计数器缓存（counter cache）
 * - 自动初始化中间表配置
 *
 * 用法示例：
 * ```php
 * // Post 和 Tag 的多对多关系（通过 post_tag 中间表）
 * class Post extends TableDataGateway {
 *     public $manyToMany = [
 *         'tags' => [
 *             'className' => 'Tag',
 *             'joinTable' => 'post_tag',           // 可选：自定义中间表名
 *             'foreignKey' => 'post_id',           // 主表在中间表的外键
 *             'assocForeignKey' => 'tag_id',       // 关联表在中间表的外键
 *             'counterCache' => 'tag_count',       // 可选：计数器缓存
 *         ],
 *     ];
 * }
 *
 * // 中间表为实体（可存储额外数据，如创建时间）
 * class Post extends TableDataGateway {
 *     public $manyToMany = [
 *         'categories' => [
 *             'className' => 'Category',
 *             'joinTableClass' => 'PostCategory',  // 中间表实体类
 *             'foreignKey' => 'post_id',
 *             'assocForeignKey' => 'category_id',
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
class ManyToManyLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
     *
     * ManyToMany 关联是一对多关系，设置为 false。
     *
     * @var boolean
     */
    public bool $oneToOne = false;

    /**
     * 在处理中间表时，是否要将中间表当做实体
     *
     * 当设置 joinTableClass 时，中间表被视为实体表，可以存储额外数据。
     *
     * @var boolean
     */
    public bool $joinTableIsEntity = false;

    /**
     * 中间表是实体时对应的表数据入口
     *
     * @var TableDataGateway|null
     */
    public ?TableDataGateway $joinTDG = null;

    /**
     * 中间表的名字
     *
     * @var string
     */
    public string $joinTable = '';

    /**
     * 中间表的完全限定名
     *
     * @var string
     */
    public string $qjoinTable = '';

    /**
     * 中间表中保存关联表主键值的字段
     *
     * @var string|null
     */
    public $assocForeignKey = null;

    /**
     * 中间表中保存关联表主键值的字段的完全限定名
     *
     * @var string
     */
    public string $qassocForeignKey = '';

    /**
     * 中间表对应的表数据入口类名
     *
     * @var string|null
     */
    public $joinTableClass = null;

    /**
     * 构造函数
     *
     * @param array $define
     * @param enum $type
     * @param TableDataGateway $mainTDG
     */
    public function __construct(array $define, int $type, TableDataGateway $mainTDG)
    {
        $this->optional[] = 'joinTable';
        $this->optional[] = 'joinTableClass';
        $this->optional[] = 'assocForeignKey';
        parent::__construct($define, $type, $mainTDG);

        if ($this->joinTableClass != '') {
            $this->joinTableIsEntity = true;
        }
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
        static $joinFields = [];

        if (!$this->initialized) { $this->init(); }

        $fields = $this->qforeignKey . ' AS ' . $this->mainTDG->pka . ', ' . $this->assocTDG->qfields($this->fields);

        if ($this->joinTableIsEntity) {
            if (!isset($joinFields[$this->joinTDG->fullTableName])) {
                $f = '';
                foreach ($this->joinTDG->meta as $field) {
                    $f .= ', ' . $this->joinTDG->qfield($field['name']) . '  AS join_' . $field['name'];
                }
                $joinFields[$this->joinTDG->fullTableName] = $f;
            }
            $fields .= $joinFields[$this->joinTDG->fullTableName];

            $sql = "SELECT {$fields} FROM {$this->joinTDG->qtableName} INNER JOIN {$this->assocTDG->qtableName} ON {$this->assocTDG->qpk} = {$this->qassocForeignKey} ";
        } else {
            $sql = "SELECT {$fields} FROM {$this->qjoinTable} INNER JOIN {$this->assocTDG->qtableName} ON {$this->assocTDG->qpk} = {$this->qassocForeignKey} ";
        }

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
        $apkvs = [];
        $entityRowset = [];

        foreach ($row as $arow) {
            if (!is_array($arow)) {
                $apkvs[] = $arow;
                continue;
            }
            if (!isset($arow[$this->assocForeignKey])) {
                // 如果关联记录尚未保存到数据库，则创建一条新的关联记录
                $newrowid = $this->assocTDG->create($arow);
                if ($newrowid == false) {
                    return false;
                }
                $apkv = $newrowid;
            } else {
                $apkv = $arow[$this->assocForeignKey];
            }
            $apkvs[] = $apkv;
            if ($this->joinTableIsEntity && isset($arow['#JOIN#'])) {
                $entityRowset[$apkv] =& $arow['#JOIN#'];
            }
        }

        // 首先取出现有的关联信息
        $qpkv = $this->dbo->qstr($pkv);
        $sql = "SELECT {$this->qassocForeignKey} FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv} ";
        $existsMiddle = (array)$this->dbo->getCol(sql_statement($sql));

        // 然后确定要添加的关联信息
        $insertAssoc = array_diff($apkvs, $existsMiddle);
        $removeAssoc = array_diff($existsMiddle, $apkvs);

        if ($this->joinTableIsEntity) {
            $insertEntityRowset = [];
            foreach ($insertAssoc as $assocId) {
                if (isset($entityRowset[$assocId])) {
                    $row = $entityRowset[$assocId];
                } else {
                    $row = [];
                }
                $row[$this->foreignKey] = $pkv;
                $row[$this->assocForeignKey] = $assocId;
                $insertEntityRowset[] = $row;
            }
            if ($this->joinTDG->createRowset($insertEntityRowset) === false) {
                return false;
            }
        } else {
            $sql = "INSERT INTO {$this->qjoinTable} ({$this->qforeignKey}, {$this->qassocForeignKey}) VALUES ({$qpkv}, ";
            foreach ($insertAssoc as $assocId) {
                if (!$this->dbo->execute(sql_statement($sql . $this->dbo->qstr($assocId) . ')'))) {
                    return false;
                }
            }
        }

        // 最后删除不再需要的关联信息
        if ($this->joinTableIsEntity) {
            $conditions = [$this->foreignKey => $pkv];
            foreach ($removeAssoc as $assocId) {
                $conditions[$this->assocForeignKey] = $assocId;
                if ($this->joinTDG->removeByConditions($conditions) === false) {
                    return false;
                }
            }
        } else {
            $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv} AND {$this->qassocForeignKey} = ";
            foreach ($removeAssoc as $assocId) {
                if (!$this->dbo->execute(sql_statement($sql . $this->dbo->qstr($assocId)))) {
                    return false;
                }
            }
        }

        if ($this->counterCache) {
            $sql = "UPDATE {$this->mainTDG->qtableName} SET {$this->counterCache} = (SELECT COUNT(*) FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv}) WHERE {$this->mainTDG->qpk} = {$qpkv}";
            $this->mainTDG->dbo->execute(sql_statement($sql));
        }

        return true;
    }

    /**
     * 根据主表的外键字段值，删除中间表的数据
     *
     * @param mixed $qpkv
     *
     * @return boolean
     */
    public function deleteMiddleTableDataByMainForeignKey($qpkv)
    {
        if (!$this->initialized) { $this->init(); }
        $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv} ";
        return $this->dbo->execute(sql_statement($sql));
    }

    /**
     * 根据关联表的外键字段值，删除中间表的数据
     *
     * @param mixed $pkv
     *
     * @return boolean
     */
    public function deleteMiddleTableDataByAssocForeignKey($pkv)
    {
        if (!$this->initialized) { $this->init(); }
        $qpkv = $this->dbo->qstr($pkv);
        $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qassocForeignKey} = {$qpkv} ";
        return $this->dbo->execute(sql_statement($sql));
    }

    /**
     * 完全初始化关联对象
     */
    public function init(): void
    {
        parent::init();
        if ($this->joinTableClass) {
            $this->joinTDG = \FLEA::getSingleton($this->joinTableClass);
            $this->joinTable = $this->joinTDG->tableName;
            $joinSchema = $this->joinTDG->schema;
        } else {
            $joinSchema = $this->mainTDG->schema;
        }
        if ($this->joinTable === '') {
            $this->joinTable = $this->getMiddleTableName($this->mainTDG->tableName, $this->assocTableName);
        }
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->mainTDG->primaryKey;
        }
        $this->joinTable = $this->dbo->dsn['prefix'] . $this->joinTable;
        $this->qjoinTable = $this->dbo->qtable($this->joinTable, $joinSchema);
        $this->qforeignKey = $this->dbo->qfield($this->foreignKey, $this->joinTable, $joinSchema);
        if (is_null($this->assocForeignKey)) {
            $this->assocForeignKey = $this->assocTDG->primaryKey;
        }
        $this->qassocForeignKey = $this->dbo->qfield($this->assocForeignKey, $this->joinTable, $this->mainTDG->schema);
    }
}
