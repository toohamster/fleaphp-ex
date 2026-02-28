<?php
/**
 * FLEA\Db\TableLink\BelongsToLink 封装 belongs to 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;

/**
 * FLEA\Db\TableLink\BelongsToLink 封装 belongs to 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class BelongsToLink extends TableLink
{
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
        if (!$this->init) { $this->init(); }
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
        if (!$this->init) { $this->init(); }
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
