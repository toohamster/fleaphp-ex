<?php

namespace FLEA\Db;

/**
 * SQL 辅助类
 *
 * 提供 SQL 查询条件解析、日志格式化等辅助功能。
 *
 * 主要功能：
 * - 解析多种格式的查询条件
 * - 支持关联查询条件
 * - SQL 日志格式化输出
 *
 * 用法示例：
 * ```php
 * // 解析简单查询条件
 * $where = \FLEA\Db\SqlHelper::parseConditions(['status' => 1], $table);
 * // 返回："status = 1"
 *
 * // 解析数值主键查询
 * $where = \FLEA\Db\SqlHelper::parseConditions(123, $table);
 * // 返回："id = 123"
 *
 * // 解析字符串条件
 * $where = \FLEA\Db\SqlHelper::parseConditions('status = 1 AND age > 18', $table);
 * // 返回："status = 1 AND age > 18"
 *
 * // 解析数组条件
 * $where = \FLEA\Db\SqlHelper::parseConditions([
 *     'status' => 1,
 *     'age' => ['age', 18, '>'],
 * ], $table);
 *
 * // 格式化输出 SQL 日志
 * \FLEA\Db\SqlHelper::dumpLog($sqlLogs);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class SqlHelper
{
    /**
     * 分析查询条件
     *
     * 支持多种查询条件格式：
     * - 数字：自动转换为主键字段查询
     * - 字符串：直接作为 SQL 条件
     * - 数组：支持多种格式的条件定义
     *
     * 数组条件格式：
     * ```php
     * // 简单格式：字段名 => 值
     * ['status' => 1, 'user_id' => 123]
     *
     * // 高级格式：字段名 => [字段名，值，操作符，连接符，是否为 SQL 命令]
     * ['age' => ['age', 18, '>', 'AND', false]]
     *
     * // IN 查询
     * ['in()' => [1, 2, 3]]
     *
     * // 关联表条件
     * ['User.status' => 1]  // User 为关联名
     * ```
     *
     * @param mixed              $conditions 查询条件（支持数字、字符串、数组）
     * @param TableDataGateway   $table      表数据入口对象
     *
     * @return array|string 解析后的 WHERE 子句（字符串）或包含关联条件的数组
     */
    public static function parseConditions($conditions, TableDataGateway $table)
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
             * 不过何种条件形式，一律转换为 (字段名，值，操作，连接运算符，值是否是 SQL 命令) 的形式
             */
            if (is_string($offset)) {
                if (!is_array($cond)) {
                    // 字段名 => 值
                    $cond = [$offset, $cond];
                } else {
                    if (strtolower($offset) == 'in()') {
                        if (count($cond) == 1 && is_array(reset($cond)) && is_string(key($cond))) {
                            $tmp = $table->qfield(key($cond)) . ' IN (' . implode(',', array_map([$table->dbo, 'qstr'], reset($cond))). ')';
                        } else {
                            $tmp = $table->qpk . ' IN (' . implode(',', array_map([$table->dbo, 'qstr'], $cond)). ')';
                        }
                        $cond = ['', $tmp, '', $expr, true];
                    } else {
                        // 字段名 => 数组
                        array_unshift($cond, $offset);
                    }
                }
            } elseif (is_int($offset)) {
                if (!is_array($cond)) {
                    // 值
                    $cond = ['', $cond, '', $expr, true];
                }
            } else {
                continue;
            }

            if (!isset($cond[0])) { continue; }
            if (!isset($cond[2])) { $cond[2] = '='; }
            if (!isset($cond[3])) { $cond[3] = $expr; }
            if (!isset($cond[4])) { $cond[4] = false; }

            [$field, $value, $op, $expr, $isCommand] = $cond;

            $str = '';
            do {
                if (strpos($field, '.') !== false) {
                    [$scheme, $field] = explode('.', $field);
                    $linkname = strtoupper($scheme);
                    if (isset($table->links[$linkname])) {
                        $linksWhere[$linkname][] = [$field, $value, $op, $expr, $isCommand];
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
            return [$where, $linksWhere];
        }
    }

    /**
     * 格式化输出 SQL 日志
     *
     * 用于调试时查看执行的 SQL 语句。
     *
     * 用法示例：
     * ```php
     * // 输出 SQL 执行日志
     * \FLEA\Db\SqlHelper::dumpLog($db->getQueryLog());
     * ```
     *
     * @param array $log SQL 日志数组
     *
     * @return void
     */
    public static function dumpLog(array $log): void
    {
        foreach ($log as $ix => $sql) {
            dump($sql, 'SQL ' . ($ix + 1));
        }
    }
}
