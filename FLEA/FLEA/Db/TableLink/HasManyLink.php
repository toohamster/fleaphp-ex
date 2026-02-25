<?php
/**
 * FLEA\Db\TableLink\HasManyLink 封装 has many 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */

namespace FLEA\Db\TableLink;

use FLEA\Db\TableLink;

/**
 * FLEA\Db\TableLink\HasManyLink 封装 has many 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class HasManyLink extends TableLink
{
    /**
     * 组合关联数据时是否是一对一
     *
     * @var boolean
     */
    public $oneToOne = false;

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
        $fields = $this->qforeignKey . ' AS ' . $this->mainTDG->pka . ', ' . $this->assocTDG->qfields($this->fields);

        $sql = "SELECT {$fields} FROM {$this->assocTDG->qtableName} ";

        return parent::_getFindSQLBase($sql, $in);
    }

    /**
     * 创建或更新主表记录时，保存关联的数据
     *
     * @param array $rowset 要保存的关联数据
     * @param mixed $pkv 主表的主键字段值
     *
     * @return boolean
     */
    function saveAssocData(array &$rowset, $pkv): bool
    {
        if (!$this->init) { $this->init(); }

        $qpkv = $this->dbo->qstr($pkv);

        // 首先取出现有的关联信息
        $sql = "SELECT {$this->assocTDG->qpk} FROM {$this->assocTDG->qtableName} WHERE {$this->qforeignKey} = {$qpkv} ";
        $existsAssoc = (array)$this->dbo->getCol(sql_statement($sql));

        // 确定要添加的关联信息
        $insertAssoc = [];
        $updateAssoc = [];

        foreach ($rowset as $row) {
            if (!is_array($row)) {
                $insertAssoc[] = $row;
                continue;
            }

            $apkv = isset($row[$this->assocTDG->primaryKey]) ? $row[$this->assocTDG->primaryKey] : null;
            if ($apkv === null || !in_array($apkv, $existsAssoc)) {
                $insertAssoc[] = $row;
            } else {
                $updateAssoc[] = $row;
            }
        }

        $removeAssoc = array_diff($existsAssoc, array_map(function($row) {
            return isset($row[$this->assocTDG->primaryKey]) ? $row[$this->assocTDG->primaryKey] : null;
        }, $rowset));

        // 插入新的关联记录
        if (!empty($insertAssoc)) {
            foreach ($insertAssoc as $row) {
                if (is_array($row)) {
                    $row[$this->foreignKey] = $pkv;
                    if ($this->assocTDG->create($row) === false) {
                        return false;
                    }
                }
            }
        }

        // 更新关联记录
        if (!empty($updateAssoc)) {
            foreach ($updateAssoc as $row) {
                $conditions = [
                    $this->foreignKey => $pkv,
                    $this->assocTDG->primaryKey => $row[$this->assocTDG->primaryKey],
                ];
                if ($this->assocTDG->update($row, $conditions) === false) {
                    return false;
                }
            }
        }

        // 删除不再需要的关联记录
        if (!empty($removeAssoc)) {
            foreach ($removeAssoc as $apkv) {
                $conditions = [
                    $this->foreignKey => $pkv,
                    $this->assocTDG->primaryKey => $apkv,
                ];
                if ($this->assocTDG->removeByConditions($conditions) === false) {
                    return false;
                }
            }
        }

        if ($this->counterCache) {
            $sql = "UPDATE {$this->mainTDG->qtableName} SET {$this->counterCache} = (SELECT COUNT(*) FROM {$this->assocTDG->qtableName} WHERE {$this->qforeignKey} = {$qpkv}) WHERE {$this->mainTDG->qpk} = {$qpkv}";
            $this->mainTDG->dbo->execute(sql_statement($sql));
        }

        return true;
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
