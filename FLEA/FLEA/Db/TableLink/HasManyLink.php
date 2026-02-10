<?php


/**
 * 定义 FLEA_Db_HasManyLink 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: HasManyLink.php 1449 2008-10-30 06:16:17Z dualface $
 */

/**
 * FLEA_Db_HasManyLink 封装 has many 关系
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_HasManyLink extends FLEA_Db_HasOneLink
{
    public $oneToOne = false;

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

        foreach ($row as $arow) {
            if (!is_array($arow)) { continue; }
            $arow[$this->foreignKey] = $pkv;
            if (!$this->_saveAssocDataBase($arow)) {
                return false;
            }
        }
        return true;
    }
}
