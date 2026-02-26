<?php

namespace App\Model;

use \FLEA\Db\TableDataGateway;

/**
 * 文章模型
 */
class Post extends TableDataGateway
{
    /**
     * 数据表名
     */
    public string $tableName = 'posts';

    /**
     * 主键字段
     */
    public string $primaryKey = 'id';

    /**
     * 定义一对多关联：一个文章有多个评论
     */
    public array $hasMany = array(
        array(
            'tableClass' => Comment::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'comments',
        ),
    );

    /**
     * 获取所有已发布的文章
     *
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return array
     */
    public function getPublishedPosts($limit = 10, $offset = 0)
    {
        return $this->findAll(
            array('status' => 1),
            'created_at DESC',
            [$limit, $offset],
            '*',
            false  // 不查询关联数据
        );
    }

    /**
     * 根据ID获取文章
     *
     * @param int $id 文章ID
     * @return array|null
     */
    public function getPostById($id)
    {
        return $this->find($id);
    }

    /**
     * 创建文章
     *
     * @param array $data 文章数据
     * @return int|false
     */
    public function createPost($data)
    {
        return $this->create($data);
    }

    /**
     * 更新文章
     *
     * @param int $id 文章ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updatePost($id, $data)
    {
        return $this->updateByConditions([$this->primaryKey => $id], $data);
    }

    /**
     * 删除文章
     *
     * @param int $id 文章ID
     * @return bool
     */
    public function deletePost($id)
    {
        return $this->removeByPkv($id);
    }

    /**
     * 获取文章总数
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->findCount(array('status' => 1));
    }
}
