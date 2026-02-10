<?php


/**
 * 定义 FLEA_Db_HasOneLink 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: HasOneLink.php 1449 2008-10-30 06:16:17Z dualface $
 */

/**
 * FLEA_Db_HasOneLink 封装 has one 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_HasOneLink extends FLEA_Db_TableLink
{
    public $oneToOne = true;

    /**
     * 构造函数
     *
     * @param array $define
     * @param enum $type
     * @param FLEA_Db_TableDataGateway $mainTDG
     *
     * @return FLEA_Db_TableLink
     */
    public function __construct($define, $type, $mainTDG)
    {
        parent::__construct($define, $type, $mainTDG);
    }

    /**
     * 返回用于查询关联表数据的SQL语句
     *
     * @param string $in
     *
     * @return string
     */
    public function getFindSQL($in)
    {
        if (!$this->init) { $this->init(); }
        $fields = $this->qforeignKey . ' AS ' . $this->mainTDG->pka . ', ' . $this->dbo->qfields($this->fields, $this->assocTDG->fullTableName, $this->assocTDG->schema);
        $sql = "SELECT {$fields} FROM {$this->assocTDG->qtableName} ";
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
    public function saveAssocData($row, $pkv)
    {
        if (empty($row)) { return true; }
        if (!$this->init) { $this->init(); }
        $row[$this->foreignKey] = $pkv;
        return $this->_saveAssocDataBase($row);
    }

    /**
     * 删除关联的数据
     *
     * @param mixed $qpkv
     *
     * @return boolean
     */
    public function deleteByForeignKey($qpkv)
    {
        if (!$this->init) { $this->init(); }
        $conditions = "{$this->qforeignKey} = {$qpkv}";
        if ($this->linkRemove) {
            return $this->assocTDG->removeByConditions($conditions);
        } else {
            return $this->assocTDG->updateField($conditions, $this->foreignKey, $this->linkRemoveFillValue);
        }
    }

    /**
     * 完全初始化关联对象
     */
    public function init()
    {
        parent::init();
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->mainTDG->primaryKey;
        }
        $this->qforeignKey = $this->dbo->qfield($this->foreignKey, $this->assocTDG->fullTableName, $this->assocTDG->schema);
    }

    /**
     * 统计关联记录数
     *
     * @param array $assocRowset
     * @param string $mappingName
     * @param string $in
     *
     * @return int
     */
    function calcCount($assocRowset, $mappingName, $in)
    {
        if (!$this->init) { $this->init(); }
        $sql = "SELECT {$this->qforeignKey} AS pid, COUNT({$this->qforeignKey}) AS c FROM {$this->assocTDG->qtableName} ";
        $sql = parent::_getFindSQLBase($sql, $in);
        $sql .= " GROUP BY {$this->qforeignKey}";

        $r = $this->dbo->execute($sql);
        while ($row = $this->dbo->fetchAssoc($r)) {
            $assocRowset[$row['pid']][$mappingName] = (int)$row['c'];
        }
        $this->dbo->freeRes($r);
    }
}