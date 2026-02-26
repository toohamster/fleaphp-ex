<?php

namespace FLEA\Db;


/**
 * 定义 \FLEA\Db\TableLink 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: TableLink.php 1449 2008-10-30 06:16:17Z dualface $
 */

/**
 * \FLEA\Db\TableLink 封装数据表之间的关联关系
 *
 * \FLEA\Db\TableLink 是一个完全供 FleaPHP 内部使用的类，
 * 开发者不应该直接构造 \FLEA\Db\TableLink 对象。
 *
 * @package Core
 * @author toohamster
 * @version 1.2
 */
class TableLink
{
    /**
     * 该连接的名字，用于检索指定的连接
     *
     * 同一个数据表的多个关联不能使用相同的名字。如果定义关联时没有指定名字，
     * 则以关联对象的 $mappingName 属性作为这个关联的名字。
     *
     * @var string
     */
    public $name;

    /**
     * 该关联所使用的表数据入口对象名
     *
     * @var string
     */
    public $tableClass;

    /**
     * 外键字段名
     *
     * @var string
     */
    public $foreignKey;

    /**
     * 关联数据表结果映射到主表结果中的字段名
     *
     * @var string
     */
    public $mappingName;

    /**
     * 指示连接两个数据集的行时，是一对一连接还是一对多连接
     *
     * @var boolean
     */
    public $oneToOne;

    /**
     * 关联的类型
     *
     * @var enum
     */
    public $type;

    /**
     * 对关联表进行查询时使用的排序参数
     *
     * @var string
     */
    public $sort;

    /**
     * 对关联表进行查询时使用的条件参数
     *
     * @var string
     */
    public $conditions;

    /**
     * 对关联表进行查询时要获取的关联表字段
     *
     * @var string|array
     */
    public $fields = '*';

    /**
     * 对关联表进行查询时限制查出的记录数
     *
     * @var int
     */
    public $limit = null;

    /**
     * 当 enabled 为 false 时，表数据入口的任何操作都不会处理该关联
     *
     * enabled 的优先级高于 linkRead、linkCreate、linkUpdate 和 linkRemove。
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * 指示在查询关联表时是否仅仅统计记录数，而不实际查询数据
     *
     * @var boolean
     */
    public $countOnly = false;

    /**
     * 将关联记录总数缓存到指定的字段
     *
     * @var string
     */
    public $counterCache = null;

    /**
     * 指示是否在主表读取记录时也读取该关联对应的关联表的记录
     *
     * @var boolean
     */
    public $linkRead = true;

    /**
     * 指示是否在主表创建记录时也创建该关联对应的关联表的记录
     *
     * @var boolean
     */
    public $linkCreate = true;

    /**
     * 指示是否在主表更新记录时也更新该关联对应的关联表的记录
     *
     * @var boolean
     */
    public $linkUpdate = true;

    /**
     * 指示是否在主表删除记录时也删除该关联对应的关联表的记录
     *
     * @var boolean
     */
    public $linkRemove = true;

    /**
     * 当删除主表记录而不删除关联表记录时，用什么值填充关联表记录的外键字段
     *
     * @var mixed
     */
    public $linkRemoveFillValue = 0;

    /**
     * 指示当保存关联数据时，采用何种方法，默认为 save，可以设置为 create、update 或 replace
     *
     * @var string
     */
    public $saveAssocMethod = 'save';

    /**
     * 主表的表数据入口对象
     *
     * @var \FLEA\Db\TableDataGateway
     */
    public $mainTDG;

    /**
     * 关联表的表数据入口对象
     *
     * @var \FLEA\Db\TableDataGateway
     */
    public $assocTDG = null;

    /**
     * 必须设置的对象属性
     *
     * @var array
     */
    public $_req = array(
        'name',             // 关联的名字
        'tableClass',       // 关联的表数据入口对象名
        'mappingName',      // 字段映射名
    );

    /**
     * 可选的参数
     *
     * @var array
     */
    public $_optional = array(
        'foreignKey',
        'sort',
        'conditions',
        'fields',
        'limit',
        'enabled',
        'countOnly',
        'counterCache',
        'linkRead',
        'linkCreate',
        'linkUpdate',
        'linkRemove',
        'linkRemoveFillValue',
        'saveAssocMethod',
    );

    /**
     * 外键字段的完全限定名
     *
     * @var string
     */
    public $qforeignKey;

    /**
     * 数据访问对象
     *
     * @var \FLEA\Db\Driver\Abstract
     */
    public $dbo;

    /**
     * 关联表数据入口的对象名
     *
     * @var string
     */
    public $assocTDGObjectId;

    /**
     * 指示关联的表数据入口是否已经初始化
     *
     * @var boolean
     */
    public $init = false;

    /**
     * 构造函数
     *
     * 开发者不应该自行构造 \FLEA\Db\TableLink 实例。而是应该通过
     * \FLEA\Db\TableLink::createLink() 静态方法来构造实例。
     *
     * @param array $define
     * @param enum $type
     * @param \FLEA\Db\TableDataGateway $mainTDG
     *
     * @return \FLEA\Db\TableLink
     */
    public function __construct($define, $type, $mainTDG)
    {
        static $defaultDsnId = null;

        // 检查必须的属性是否都已经提供
        foreach ($this->_req as $key) {
            if (!isset($define[$key]) || $define[$key] == '') {
                throw new \FLEA\Db\Exception\MissingLinkOption($key);
            } else {
                $this->{$key} = $define[$key];
            }
        }
        // 设置可选属性
        foreach ($this->_optional as $key) {
            if (isset($define[$key])) {
                $this->{$key} = $define[$key];
            }
        }
        $this->type = $type;
        $this->mainTDG = $mainTDG;
        $this->dbo = $this->mainTDG->getDBO();
        $dsnid = $this->dbo->dsn['id'];

        if (is_null($defaultDsnId)) {
            $defaultDSN = \FLEA::getAppInf('dbDSN');
            if ($defaultDSN) {
                $defaultDSN = \FLEA::parseDSN($defaultDSN);
                $defaultDsnId = $defaultDSN['id'];
            } else {
                $defaultDsnId = -1;
            }
        }
        if ($dsnid == $defaultDsnId) {
            $this->assocTDGObjectId = null;
        } else {
            $this->assocTDGObjectId = "{$this->tableClass}-{$dsnid}";
        }
    }

    /**
     * 创建 \FLEA\Db\TableLink 对象实例
     *
     * @param array $define
     * @param enum $type
     * @param \FLEA\Db\TableDataGateway $mainTDG
     *
     * @return \FLEA\Db\TableLink
     */
    public static function createLink(array $define, int $type, \FLEA\Db\TableDataGateway &$mainTDG): \FLEA\Db\TableLink
    {
        static $typeMap = array(
            HAS_ONE         => '\FLEA\Db\TableLink\HasOneLink',
            BELONGS_TO      => '\FLEA\Db\TableLink\BelongsToLink',
            HAS_MANY        => '\FLEA\Db\TableLink\HasManyLink',
            MANY_TO_MANY    => '\FLEA\Db\TableLink\ManyToManyLink',
        );
        static $instances = [];

        // 检查 $type 参数
        if (!isset($typeMap[$type])) {
            throw new \FLEA\Db\Exception\InvalidLinkType($type);
        }

        // tableClass 属性是必须提供的
        if (!isset($define['tableClass'])) {
            throw new \FLEA\Db\Exception\MissingLinkOption('tableClass');
        }
        // 如果没有提供 mappingName 属性，则使用 tableClass 作为 mappingName
        if (!isset($define['mappingName'])) {
            $define['mappingName'] = $define['tableClass'];
        }
        // 如果没有提供 name 属性，则使用 mappingName 属性作为 name
        if (!isset($define['name'])) {
            $define['name'] = $define['mappingName'];
        }

        // 如果是 MANY_TO_MANY 连接，则检查是否提供了 joinTable 属性或者 joinTableClass 属性，
        // 以及assocForeignKey 属性
        if ($type == MANY_TO_MANY) {
            if (!isset($define['joinTable']) && !isset($define['joinTableClass'])) {
                throw new \FLEA\Db\Exception\MissingLinkOption('joinTable');
            }
        }

        $instances[$define['name']] = new $typeMap[$type]($define, $type, $mainTDG);
        return $instances[$define['name']];
    }

    /**
     * 生成一个 MANY_TO_MANY 关联需要的中间表名称
     *
     * @param string $table1
     * @param string $table2
     *
     * @return string
     */
    public function getMiddleTableName(string $table1, string $table2): string
    {
        if (strcmp($table1, $table2) < 0) {
            return $this->dbo->dsn['prefix'] . "{$table1}_{$table2}";
        } else {
            return $this->dbo->dsn['prefix'] . "{$table2}_{$table1}";
        }
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
        throw new \FLEA\Exception\NotImplemented('saveAssocData()', '\FLEA\Db\TableLink');
    }

    /**
     * 初始化关联对象
     */
    public function init(): void
    {
        if ($this->init) { return; }
        if ($this->assocTDGObjectId && \FLEA::isRegistered($this->assocTDGObjectId)) {
            $this->assocTDG = \FLEA::registry($this->assocTDGObjectId);
        } else {
            if ($this->assocTDGObjectId) {
                // 使用 Composer PSR-4 自动加载
                if (!class_exists($this->tableClass, true)) {
                    throw new \FLEA\Exception\ExpectedClass($this->tableClass);
                }
                $this->assocTDG = new $this->tableClass(array('dbo' => $this->dbo));
                \FLEA::register($this->assocTDG, $this->assocTDGObjectId);
            } else {
                $this->assocTDG = \FLEA::getSingleton($this->tableClass);
            }
        }
        $this->init = true;
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
    function calcCount(array &$assocRowset, string $mappingName, string $in): void
    {
        throw new \FLEA\Exception\NotImplemented('calcCount()', '\FLEA\Db\TableLink');
    }

    /**
     * 返回用于查询关联表数据的 SQL 语句
     *
     * @param string $sql
     * @param string $in
     *
     * @return string
     */
    protected function _getFindSQLBase(string $sql, string $in): string
    {
        if ($in) {
            $sql .= " WHERE {$this->qforeignKey} {$in}";
        }
        if ($this->conditions) {
            if (is_array($this->conditions)) {
                $conditions = \FLEA\Db\SqlHelper::parseConditions($this->conditions, $this->assocTDG);
                if (is_array($conditions)) {
                    $conditions = $conditions[0];
                }
            } else {
                $conditions =& $this->conditions;
            }
            if ($conditions) {
                $sql .= " AND {$conditions}";
            }
        }
        if ($this->sort && $this->countOnly == false) {
            $sql .= " ORDER BY {$this->sort}";
        }

        return $sql;
    }

    /**
     * 创建或更新主表记录时，保存关联的数据
     *
     * @param array $row 要保存的关联数据
     *
     * @return boolean
     */
    protected function _saveAssocDataBase(array &$row): bool
    {
        switch (strtolower($this->saveAssocMethod)) {
        case 'create':
            return $this->assocTDG->create($row);
        case 'update':
            return $this->assocTDG->update($row);
        case 'replace':
            return $this->assocTDG->replace($row);
        default:
            return $this->assocTDG->save($row);
        }
    }
}
