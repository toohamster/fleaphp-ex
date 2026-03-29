<?php

namespace FLEA\Db;

/**
 * 表关联定义类
 *
 * 用于定义和管理数据表之间的关联关系。
 * 支持四种关联类型：HasOne、BelongsTo、HasMany、ManyToMany。
 *
 * 主要功能：
 * - 定义表关联配置
 * - 管理关联数据的读取、创建、更新、删除
 * - 支持关联查询条件、排序、限制
 * - 支持 Counter Cache（关联记录数缓存）
 *
 * 关联类型说明：
 * - HAS_ONE: 一对一关联（主表记录对应一条关联表记录）
 * - BELONGS_TO: 属于关联（当前表记录属于另一条记录）
 * - HAS_MANY: 一对多关联（主表记录对应多条关联表记录）
 * - MANY_TO_MANY: 多对多关联（需要中间表）
 *
 * 用法示例：
 * ```php
 * // 在 TableDataGateway 子类中定义关联
 * class Post extends \FLEA\Db\TableDataGateway
 * {
 *     public $hasMany = [
 *         'Comment' => [
 *             'tableClass' => 'Comment',
 *             'foreignKey' => 'post_id',
 *             'mappingName' => 'comments',
 *             'sort' => 'created_at DESC',
 *         ]
 *     ];
 *
 *     public $belongsTo = [
 *         'User' => [
 *             'tableClass' => 'User',
 *             'foreignKey' => 'user_id',
 *             'mappingName' => 'author',
 *         ]
 *     ];
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Db\TableDataGateway
 * @see     \FLEA\Db\TableLink\HasOneLink
 * @see     \FLEA\Db\TableLink\BelongsToLink
 * @see     \FLEA\Db\TableLink\HasManyLink
 * @see     \FLEA\Db\TableLink\ManyToManyLink
 */
class TableLink
{
    /**
     * @var string 该连接的名字，用于检索指定的连接
     *
     * 同一个数据表的多个关联不能使用相同的名字。如果定义关联时没有指定名字，
     * 则以关联对象的 $mappingName 属性作为这个关联的名字。
     */
    public string $name;

    /**
     * @var string 该关联所使用的表数据入口对象名
     */
    public string $tableClass;

    /**
     * @var string|null 外键字段名
     */
    public $foreignKey = null;

    /**
     * @var string 关联数据表结果映射到主表结果中的字段名
     */
    public string $mappingName;

    /**
     * @var bool 指示连接两个数据集的行时，是一对一连接还是一对多连接
     */
    public bool $oneToOne = false;

    /**
     * @var int 关联的类型
     *
     * @see TableDataGateway::HAS_ONE
     * @see TableDataGateway::BELONGS_TO
     * @see TableDataGateway::HAS_MANY
     * @see TableDataGateway::MANY_TO_MANY
     */
    public int $type;

    /**
     * @var string 对关联表进行查询时使用的排序参数
     */
    public string $sort = '';

    /**
     * @var mixed 对关联表进行查询时使用的条件参数
     */
    public $conditions;

    /**
     * @var string|array 对关联表进行查询时要获取的关联表字段
     */
    public string $fields = '*';

    /**
     * @var int|null 对关联表进行查询时限制查出的记录数
     */
    public $limit = null;

    /**
     * @var bool 当 enabled 为 false 时，表数据入口的任何操作都不会处理该关联
     *
     * enabled 的优先级高于 linkRead、linkCreate、linkUpdate 和 linkRemove。
     */
    public bool $enabled = true;

    /**
     * @var bool 指示在查询关联表时是否仅仅统计记录数，而不实际查询数据
     */
    public bool $countOnly = false;

    /**
     * @var string 将关联记录总数缓存到指定的字段
     */
    public string $counterCache = '';

    /**
     * @var bool 指示是否在主表读取记录时也读取该关联对应的关联表的记录
     */
    public bool $linkRead = true;

    /**
     * @var bool 指示是否在主表创建记录时也创建该关联对应的关联表的记录
     */
    public bool $linkCreate = true;

    /**
     * @var bool 指示是否在主表更新记录时也更新该关联对应的关联表的记录
     */
    public bool $linkUpdate = true;

    /**
     * @var bool 指示是否在主表删除记录时也删除该关联对应的关联表的记录
     */
    public bool $linkRemove = true;

    /**
     * @var mixed 当删除主表记录而不删除关联表记录时，用什么值填充关联表记录的外键字段
     */
    public $linkRemoveFillValue = 0;

    /**
     * @var string 指示当保存关联数据时，采用何种方法，默认为 save，可以设置为 create、update 或 replace
     */
    public string $saveAssocMethod = 'save';

    /**
     * @var TableDataGateway 主表的表数据入口对象
     */
    public TableDataGateway $mainTDG;

    /**
     * @var TableDataGateway|null 关联表的表数据入口对象
     */
    public ?TableDataGateway $assocTDG = null;

    /**
     * @var array 必须设置的对象属性
     */
    protected array $req = [
        'name',
        'tableClass',
        'mappingName',
    ];

    /**
     * @var array 可选设置的对象属性
     */
    protected array $optional = [
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
    ];

    /**
     * @var string 外键字段的完全限定名
     */
    public string $qforeignKey = '';

    /**
     * @var Driver\AbstractDriver 数据访问对象
     */
    public Driver\AbstractDriver $dbo;

    /**
     * @var string 关联表数据入口的对象名
     */
    public string $assocTDGObjectId = '';

    /**
     * @var bool 指示关联的表数据入口是否已经初始化
     */
    public bool $initialized = false;

    /**
     * 构造函数
     *
     * 开发者不应该自行构造 \FLEA\Db\TableLink 实例。
     * 应该通过 \FLEA\Db\TableLink::createLink() 静态方法来构造实例。
     *
     * @param array             $define   关联定义配置数组
     * @param int               $type     关联类型
     * @param TableDataGateway  $mainTDG  主表的表数据入口对象
     *
     * @throws \FLEA\Db\Exception\MissingLinkOption 缺少必需的配置选项时抛出
     */
    public function __construct(array $define, int $type, TableDataGateway $mainTDG)
    {
        static $defaultDsnId = null;

        // 检查必须的属性是否都已经提供
        foreach ($this->req as $key) {
            if (!isset($define[$key]) || $define[$key] == '') {
                throw new \FLEA\Db\Exception\MissingLinkOption($key);
            } else {
                $this->{$key} = $define[$key];
            }
        }
        // 设置可选属性
        foreach ($this->optional as $key) {
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
            $this->assocTDGObjectId = '';
        } else {
            $this->assocTDGObjectId = "{$this->tableClass}-{$dsnid}";
        }
    }

    /**
     * 创建 \FLEA\Db\TableLink 对象实例
     *
     * 根据关联类型自动创建对应的 TableLink 子类实例。
     * 支持 HasOneLink、BelongsToLink、HasManyLink、ManyToManyLink 四种类型。
     *
     * 用法示例：
     * ```php
     * // 创建 HasMany 关联
     * $link = TableLink::createLink([
     *     'tableClass' => 'Comment',
     *     'foreignKey' => 'post_id',
     *     'mappingName' => 'comments',
     * ], TableDataGateway::HAS_MANY, $postTable);
     * ```
     *
     * @param array            $define   关联定义配置数组
     * @param int              $type     关联类型常量
     * @param TableDataGateway $mainTDG  主表的表数据入口对象
     *
     * @return TableLink 返回创建的 TableLink 实例
     *
     * @throws \FLEA\Db\Exception\InvalidLinkType 关联类型无效时抛出
     * @throws \FLEA\Db\Exception\MissingLinkOption 缺少必需的配置选项时抛出
     */
    public static function createLink(array $define, int $type, \FLEA\Db\TableDataGateway $mainTDG): \FLEA\Db\TableLink
    {
        static $typeMap = [
            TableDataGateway::HAS_ONE         => \FLEA\Db\TableLink\HasOneLink::class,
            TableDataGateway::BELONGS_TO      => \FLEA\Db\TableLink\BelongsToLink::class,
            TableDataGateway::HAS_MANY        => \FLEA\Db\TableLink\HasManyLink::class,
            TableDataGateway::MANY_TO_MANY    => \FLEA\Db\TableLink\ManyToManyLink::class,
        ];
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

        // 如果是 TableDataGateway::MANY_TO_MANY 连接，则检查是否提供了 joinTable 属性或者 joinTableClass 属性，
        // 以及 assocForeignKey 属性
        if ($type == TableDataGateway::MANY_TO_MANY) {
            if (!isset($define['joinTable']) && !isset($define['joinTableClass'])) {
                throw new \FLEA\Db\Exception\MissingLinkOption('joinTable');
            }
        }

        $instances[$define['name']] = new $typeMap[$type]($define, $type, $mainTDG);
        return $instances[$define['name']];
    }

    /**
     * 生成一个 TableDataGateway::MANY_TO_MANY 关联需要的中间表名称
     *
     * 根据两个表名自动生成中间表名称（按字母顺序排列）。
     *
     * 用法示例：
     * ```php
     * // 生成 posts 和 tags 的中间表名
     * $middleTable = $link->getMiddleTableName('posts', 'tags');
     * // 返回："posts_tags"（带前缀）
     * ```
     *
     * @param string $table1 第一个表名
     * @param string $table2 第二个表名
     *
     * @return string 中间表名称（带数据库前缀）
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
     * 此方法在基类中抛出异常，由子类实现具体逻辑。
     *
     * @param array $row 要保存的关联数据
     * @param mixed $pkv 主表的主键字段值
     *
     * @return bool
     *
     * @throws \FLEA\Exception\NotImplemented 子类未实现此方法时抛出
     */
    public function saveAssocData(array &$row, $pkv): bool
    {
        throw new \FLEA\Exception\NotImplemented('saveAssocData()', '\FLEA\Db\TableLink');
    }

    /**
     * 初始化关联对象
     *
     * 加载并初始化关联表的表数据入口对象。
     * 使用缓存避免重复创建对象。
     *
     * @return void
     *
     * @throws \FLEA\Exception\ExpectedClass 关联表类不存在时抛出
     */
    public function init(): void
    {
        if ($this->initialized) { return; }
        if ($this->assocTDGObjectId && \FLEA::isRegistered($this->assocTDGObjectId)) {
            $this->assocTDG = \FLEA::registry($this->assocTDGObjectId);
        } else {
            if ($this->assocTDGObjectId) {
                // 使用 Composer PSR-4 自动加载
                if (!class_exists($this->tableClass, true)) {
                    throw new \FLEA\Exception\ExpectedClass($this->tableClass);
                }
                $this->assocTDG = new $this->tableClass(['dbo' => $this->dbo]);
                \FLEA::register($this->assocTDG, $this->assocTDGObjectId);
            } else {
                $this->assocTDG = \FLEA::getSingleton($this->tableClass);
            }
        }
        $this->initialized = true;
    }

    /**
     * 统计关联记录数
     *
     * @param array  $assocRowset 关联记录集
     * @param string $mappingName 映射字段名
     * @param string $in          IN 条件字符串
     *
     * @return void
     *
     * @throws \FLEA\Exception\NotImplemented 子类未实现此方法时抛出
     */
    public function calcCount(array &$assocRowset, string $mappingName, string $in): void
    {
        throw new \FLEA\Exception\NotImplemented('calcCount()', '\FLEA\Db\TableLink');
    }

    /**
     * 返回用于查询关联表数据的 SQL 语句基础部分
     *
     * 添加 WHERE 条件、排序等子句。
     *
     * @param string $sql SQL 语句基础
     * @param string $in  IN 条件字符串
     *
     * @return string 完整的 SQL 语句
     */
    protected function getFindSQLBase(string $sql, string $in): string
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
     * 根据 $saveAssocMethod 属性决定使用何种方法保存关联数据。
     *
     * @param array $row 要保存的关联数据
     *
     * @return bool 保存成功返回 true，否则返回 false
     */
    protected function saveAssocDataBase(array &$row): bool
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
