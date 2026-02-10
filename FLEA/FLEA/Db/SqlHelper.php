<?php


/**
 * 定义 FLEA_Db_SqlHelper 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: SqlHelper.php 1449 2008-10-30 06:16:17Z dualface $
 */

/**
 * FLEA_Db_SqlHelper 类提供了各种生成 SQL 语句的辅助方法
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_SqlHelper
{
    /**
     * 分析查询条件
     *
     * @param mixed $conditions
     * @param FLEA_Db_TableDataGateway $table
     *
     * @return array
     */
    public static function parseConditions($conditions, $table)
    {
        // 对于 NULL，直接返回 NULL
        if (is_null($conditions)) { return null; }

        // 如果是数字，则假定为主键字段值
        if (is_numeric($conditions)) {
            return "{$table->qpk} = {$conditions}";
        }

        // 如果是字符串，则假定为自定义条件
        if (is_string($conditions)) {
            return $conditions;
        }

        // 如果不是数组，说明提供的查询条件有误
        if (!is_array($conditions)) {
            return null;
        }

        $where = '';
        $linksWhere = [];
        $expr = '';

        foreach ($conditions as $offset => $cond) {
            $expr = 'AND';
            /**
             * 不过何种条件形式，一律转换为 (字段名, 值, 操作, 连接运算符, 值是否是SQL命令) 的形式
             */
            if (is_string($offset)) {
                if (!is_array($cond)) {
                    // 字段名 => 值
                    $cond = array($offset, $cond);
                } else {
                    if (strtolower($offset) == 'in()') {
                        if (count($cond) == 1 && is_array(reset($cond)) && is_string(key($cond))) {
                            $tmp = $table->qfield(key($cond)) . ' IN (' . implode(',', array_map(array($table->dbo, 'qstr'), reset($cond))). ')';
                        } else {
                            $tmp = $table->qpk . ' IN (' . implode(',', array_map(array($table->dbo, 'qstr'), $cond)). ')';
                        }
                        $cond = array('', $tmp, '', $expr, true);
                    } else {
                        // 字段名 => 数组
                        array_unshift($cond, $offset);
                    }
                }
            } elseif (is_int($offset)) {
                if (!is_array($cond)) {
                    // 值
                    $cond = array('', $cond, '', $expr, true);
                }
            } else {
                continue;
            }

            if (!isset($cond[0])) { continue; }
            if (!isset($cond[2])) { $cond[2] = '='; }
            if (!isset($cond[3])) { $cond[3] = $expr; }
            if (!isset($cond[4])) { $cond[4] = false; }

            list($field, $value, $op, $expr, $isCommand) = $cond;

            $str = '';
            do {
                if (strpos($field, '.') !== false) {
                    list($scheme, $field) = explode('.', $field);
                    $linkname = strtoupper($scheme);
                    if (isset($table->links[$linkname])) {
                        $linksWhere[$linkname][] = array($field, $value, $op, $expr, $isCommand);
                        break;
                    } else {
                        $field = "{$scheme}.{$field}";
                    }
                }

                if (!$isCommand) {
                    $field = $table->qfield($field);
                    $value = $table->dbo->qstr($value);
                    $str = "{$field} {$op} {$value} {$expr} ";
                } else {
                    $str = "{$value} {$expr} ";
                }
            } while (false);

            $where .= $str;
        }

        $where = substr($where, 0, - (strlen($expr) + 2));
        if (empty($linksWhere)) {
            return $where;
        } else {
            return array($where, $linksWhere);
        }
    }

    /**
     * 格式化输出 SQL 日志
     *
     * @param array $log
     */
    public static function dumpLog($log)
    {
        foreach ($log as $ix => $sql) {
            dump($sql, 'SQL ' . ($ix + 1));
        }
    }
}
