<?php

namespace FLEA\Db;


/**
 * 定义 \FLEA\Db\ActiveRecord 类
 *
 * @author 起源科技(www.qeeyuan.com)
 * @package Core
 * @version $Id: ActiveRecord.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\ActiveRecord 类实现了 ActiveRecord 模式
 *
 * @author 起源科技(www.qeeyuan.com)
 * @package Core
 * @version $Id: ActiveRecord.php 972 2007-10-09 20:56:54Z qeeyuan $
 */
class ActiveRecord
{
    /**
     * 定义该对象要聚合的其他对象
     *
     * @var array
     */
    public $_aggregation = [];

    /**
     * 用于完成数据库操作的 TableDataGateway 继承类
     *
     * @var \FLEA\Db\TableDataGateway
     */
    public $_table;

    /**
     * 该对象的主键属性名
     *
     * @var string
     */
    public $_idname;

    /**
     * 指示该对象是否已经初始化
     *
     * @var boolean
     */
    public $init = false;

    /**
     * 字段和对象属性之间的映射关系
     *
     * @var array
     */
    public $_mapping = false;

    /**
     * 继承类必须覆盖此静态函数
     *
     * @static
     *
     * @return array
     */
    static function define(): array
    {
    }

    /**
     * 构造函数
     *
     * 根据 $conditions 参数查询符合条件的记录作为对象属性。
     *
     * @param mixed $conditions
     *
     * @return \FLEA\Db\ActiveRecord
     */
    public function __construct($conditions = null)
    {
        $this->init();
        $this->load($conditions);
    }

    /**
     * 初始化
     *
     * @param array $options
     */
    public function init(): void
    {
        if ($this->init) { return; }
        $this->init = true;

        $myclass = get_class($this);
        $options = call_user_func(array($myclass, 'define'));
        $tableClass = $options['tableClass'];

        $objid = "{$myclass}_tdg";
        if (FLEA::isRegistered($objid)) {
            $this->_table = FLEA::registry($objid);
        } else {
            FLEA::loadClass($tableClass);
            $this->_table = new $tableClass(array('skipCreateLinks' => true));
            FLEA::register($this->_table, $objid);
        }

        if (!empty($options['propertiesMapping'])) {
            $this->_mapping = array(
                'p2f' => $options['propertiesMapping'],
                'f2p' => array_flip($options['propertiesMapping']),
            );
            $this->_idname = $this->_mapping['f2p'][$this->_table->primaryKey];
        } else {
            $this->_mapping = array('p2f' => array(), 'f2p' => array());
            foreach ($this->_table->meta as $field) {
                $this->_mapping['p2f'][$field['name']] = $field['name'];
                $this->_mapping['f2p'][$field['name']] = $field['name'];
            }
            $this->_idname = $this->_table->primaryKey;
        }

        if (!isset($options['aggregation']) || !is_array($options['aggregation'])) {
            $options['aggregation'] = [];
        }
        foreach ($options['aggregation'] as $offset => $define) {
            if (!isset($define['mappingName'])) {
                $define['mappingName'] = substr(strtolower($define['tableClass']), 0, 1) . substr($define['tableClass'], 1);
            }
            if ($define['mappingType'] == HAS_MANY || $define['mappingType'] == MANY_TO_MANY) {
                $this->{$define['mappingName']} = [];
            } else {
                $this->{$define['mappingName']} = null;
            }

            /**
             * 获得聚合对象的定义信息
             */
            FLEA::loadClass($define['class']);
            $options = call_user_func(array($define['class'], 'define'));

            $link = array(
                'tableClass' => $options['tableClass'],
                'mappingName' => $define['mappingName'],
                'foreignKey' => isset($define['foreignKey']) ? $define['foreignKey'] : null,
            );

            if ($define['mappingType'] == MANY_TO_MANY) {
                $link['joinTable'] = isset($define['joinTable']) ? $define['joinTable'] : null;
                $link['assocForeignKey'] = isset($define['assocForeignKey']) ? $define['assocForeignKey'] : null;
            }

            $this->_table->createLink($link, $define['mappingType']);
            $define['link'] =& $this->_table->getLink($link['mappingName']);
            $this->_aggregation[$offset] = $define;
        }
    }

    /**
     * 从数据库载入符合条件的一个对象
     *
     * @param mixed $conditions
     */
    public function load($conditions): void
    {
        $row = $this->_table->find($conditions);
        if (is_array($row)) { $this->attach($row); }
    }

    /**
     * 保存对象到数据库
     */
    public function save(): void
    {
        $row =& $this->toArray();
        $this->_table->save($row);
    }

    /**
     * 从数据库删除对象
     */
    public function delete(): void
    {
        $this->_table->removeByPkv($this->getId());
    }

    /**
     * 设置对象主键值
     *
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->{$this->_idname} = $id;
    }

    /**
     * 返回对象主键值
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->{$this->_idname};
    }

    /**
     * 将对象属性转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];
        foreach ($this->_mapping['p2f'] as $prop => $field) {
            $arr[$field] = $this->{$prop};
        }
        return $arr;
    }

    /**
     * 将记录的值绑定到对象
     *
     * @param array $row
     */
    public function attach(array &$row): void
    {
        foreach ($this->_mapping['f2p'] as $field => $prop) {
            if (isset($row[$field])) {
                $this->{$prop} = $row[$field];
            }
        }

        foreach ($this->_aggregation as $define) {
            $mn = $define['link']->mappingName;
            if (!isset($row[$mn])) { continue; }
            if ($define['link']->oneToOne) {
                $this->{$mn} = new $define['class']($row[$mn]);
            } else {
                $this->{$mn}[] = new $define['class']($row[$mn]);
            }
        }
    }

}
