<?php


/**
 * 定义 FLEA_Db_BelongsToLink 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: BelongsToLink.php 1449 2008-10-30 06:16:17Z dualface $
 */

/**
 * FLEA_Db_BelongsToLink 封装 belongs to 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_BelongsToLink extends FLEA_Db_TableLink
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
    public function getFindSQL($in)
    {
        if (!$this->init) { $this->init(); }
        $fields = $this->mainTDG->qpk . ' AS ' . $this->mainTDG->pka . ', ' . $this->dbo->qfields($this->fields, $this->assocTDG->fullTableName, $this->assocTDG->schema);

        $sql = "SELECT {$fields} FROM {$this->assocTDG->qtableName} LEFT JOIN {$this->mainTDG->qtableName} ON {$this->mainTDG->qpk} {$in} WHERE {$this->qforeignKey} = {$this->assocTDG->qpk} ";
        $in = '';
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
    function saveAssocData($row, $pkv)
    {
        if (empty($row)) { return true; }
        if (!$this->init) { $this->init(); }
        return $this->_saveAssocDataBase($row);
    }

    /**
     * 完全初始化关联对象
     */
    public function init()
    {
        parent::init();
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->assocTDG->primaryKey;
        }
        $this->qforeignKey = $this->dbo->qfield($this->foreignKey, $this->mainTDG->fullTableName, $this->mainTDG->schema);
    }
}