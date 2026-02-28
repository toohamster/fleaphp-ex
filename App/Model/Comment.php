<?php

namespace App\Model;

use \FLEA\Db\TableDataGateway;

/**
 * 评论模型
 */
class Comment extends TableDataGateway
{
    /**
     * 数据表名
     */
    public string $tableName = 'comments';

    /**
     * 主键字段
     */
    public $primaryKey = 'id';

    /**
     * 定义从属关联：一个评论属于一个文章
     */
    public array $belongsTo = [
        [
            'tableClass' => Post::class,
            'foreignKey' => 'post_id',
            'mappingName' => 'post',
        ],
    ];

    /**
     * 获取文章的评论
     *
     * @param int $postId 文章ID
     * @return array
     */
    public function getCommentsByPostId($postId)
    {
        return $this->findAll(
            [
                'post_id' => $postId,
                'status' => 1
            ],
            'created_at ASC'
        );
    }

    /**
     * 创建评论
     *
     * @param array $data 评论数据
     * @return int
     */
    public function createComment($data)
    {
        $data['status'] = 1; // 自动审核通过
        return $this->create($data);
    }

    /**
     * 删除评论
     *
     * @param int $id 评论ID
     * @return bool
     */
    public function deleteComment($id)
    {
        return $this->removeByPkv($id);
    }

    /**
     * 获取文章的评论数
     *
     * @param int $postId 文章ID
     * @return int
     */
    public function getCommentCount($postId)
    {
        return $this->findCount([
            'post_id' => $postId,
            'status' => 1
        ]);
    }
}
