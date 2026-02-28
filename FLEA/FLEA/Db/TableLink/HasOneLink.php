<?php
/**
 * FLEA\Db\TableLink\HasOneLink 封装 has one 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;

/**
 * FLEA\Db\TableLink\HasOneLink 封装 has one 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class HasOneLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
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
        if (!$this->init) { $this->init(); }
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
        if (!$this->init) { $this->init(); }

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
