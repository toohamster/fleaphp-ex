<?php
/**
 * FLEA\Db\TableLink\ManyToManyLink 封装 many to many 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;
use FLEA\Db\TableDataGateway;

/**
 * FLEA\Db\TableLink\ManyToManyLink 封装 many to many 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class ManyToManyLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
     *
     * @var boolean
     */
    public $oneToOne = false;

    /**
     * 在处理中间表时，是否要将中间表当做实体
     *
     * @var boolean
     */
    public $joinTableIsEntity = false;

    /**
     * 中间表是实体时对应的表数据入口
     *
     * @var TableDataGateway
     */
    public $joinTDG = null;

    /**
     * 中间表的名字
     *
     * @var string
     */
    public $joinTable = null;

    /**
     * 中间表的完全限定名
     *
     * @var string
     */
    public $qjoinTable = null;

    /**
     * 中间表中保存关联表主键值的字段
     *
     * @var string
     */
    public $assocForeignKey = null;

    /**
     * 中间表中保存关联表主键值的字段的完全限定名
     *
     * @var string
     */
    public $qassocForeignKey = null;

    /**
     * 中间表对应的表数据入口
     *
     * @var TableDataGateway
     */
    public $joinTableClass = null;

    /**
     * 构造函数
     *
     * @param array $define
     * @param enum $type
     * @param TableDataGateway $mainTDG
     *
     * @return TableLink
     */
    function __construct($define, $type, $mainTDG)
    {
        $this->_optional[] = 'joinTable';
        $this->_optional[] = 'joinTableClass';
        $this->_optional[] = 'assocForeignKey';
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
    function getFindSQL($in)
    {
        static $joinFields = [];

        if (!$this->init) { $this->init(); }

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

        return parent::_getFindSQLBase($sql, $in);
    }

    /**
     * 创建或更新主表记录时，保存关联的数据
     *
     * @param array $row 要保存的关联数据
     * @param mixed $pkv 主表的主键字段值
     *
     * @return boolean
     */
    function saveAssocData(array &$row, $pkv): bool
    {
        if (!$this->init) { $this->init(); }
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
        $existsMiddle = (array)$this->dbo->getCol($sql);

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
                if (!$this->dbo->execute($sql . $this->dbo->qstr($assocId) . ')')) {
                    return false;
                }
            }
        }

        // 最后删除不再需要的关联信息
        if ($this->joinTableIsEntity) {
            $conditions = array($this->foreignKey => $pkv);
            foreach ($removeAssoc as $assocId) {
                $conditions[$this->assocForeignKey] = $assocId;
                if ($this->joinTDG->removeByConditions($conditions) === false) {
                    return false;
                }
            }
        } else {
            $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv} AND {$this->qassocForeignKey} = ";
            foreach ($removeAssoc as $assocId) {
                if (!$this->dbo->execute($sql . $this->dbo->qstr($assocId))) {
                    return false;
                }
            }
        }

        if ($this->counterCache) {
            $sql = "UPDATE {$this->mainTDG->qtableName} SET {$this->counterCache} = (SELECT COUNT(*) FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv}) WHERE {$this->mainTDG->qpk} = {$qpkv}";
            $this->mainTDG->dbo->execute($sql);
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
    function deleteMiddleTableDataByMainForeignKey($qpkv)
    {
        if (!$this->init) { $this->init(); }
        $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qforeignKey} = {$qpkv} ";
        return $this->dbo->execute($sql);
    }

    /**
     * 根据关联表的外键字段值，删除中间表的数据
     *
     * @param mixed $pkv
     *
     * @return boolean
     */
    function deleteMiddleTableDataByAssocForeignKey($pkv)
    {
        if (!$this->init) { $this->init(); }
        $qpkv = $this->dbo->qstr($pkv);
        $sql = "DELETE FROM {$this->qjoinTable} WHERE {$this->qassocForeignKey} = {$qpkv} ";
        return $this->dbo->execute($sql);
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
        if (is_null($this->joinTable)) {
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
