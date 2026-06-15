<?php

namespace FleaPhpDemo\Controller;

use FLEA\Response;
use FLEA\View;
use FLEA\View\ViewInterface;
use FleaPhpDemo\Model\Post;
use FleaPhpDemo\Model\Comment;
use FLEA\Controller\Action;

/**
 * 文章控制器
 *
 * 演示新版架构：action 方法返回 ViewInterface 或 Response，
 * 由框架统一处理响应发送。
 */
class PostController extends Action
{
    protected Post $postModel;

    protected Comment $commentModel;

    public function __construct(string $controllerName)
    {
        parent::__construct($controllerName);
        $this->postModel = new Post();
        $this->commentModel = new Comment();
    }

    /**
     * 文章列表页
     */
    public function actionIndex(): ViewInterface
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $posts = $this->postModel->getPublishedPosts($pageSize, $offset);
        $total = $this->postModel->getTotalCount();
        $totalPages = ceil($total / $pageSize);

        return View::html('post/index.php', [
            'posts'      => $posts,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ]);
    }

    /**
     * 文章详情页
     */
    public function actionView(): ViewInterface
    {
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('文章ID不能为空');
        }

        $post = $this->postModel->find($id);

        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('文章不存在');
        }

        $comments = $post['comments'] ?? [];
        $commentCount = count($comments);

        return View::html('post/view.php', [
            'post'         => $post,
            'comments'     => $comments,
            'commentCount' => $commentCount,
        ]);
    }

    /**
     * 创建文章
     */
    public function actionCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'   => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author'  => $_POST['author'] ?? '匿名',
                'status'  => 1,
            ];

            if (empty($data['title']) || empty($data['content'])) {
                return Response::error('标题和内容不能为空', 400);
            }

            $id = $this->postModel->createPost($data);
            if ($id) {
                return Response::success(null, '文章创建成功');
            }

            return Response::error('文章创建失败', 500);
        }

        return View::html('post/create.php');
    }

    /**
     * 编辑文章
     */
    public function actionEdit()
    {
        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            throw new \FLEA\Exception\InvalidArguments('文章ID不能为空');
        }

        $post = $this->postModel->getPostById($id);

        if (!$post) {
            throw new \FLEA\Exception\InvalidArguments('文章不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'   => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author'  => $_POST['author'] ?? '匿名',
            ];

            if (empty($data['title']) || empty($data['content'])) {
                return Response::error('标题和内容不能为空', 400);
            }

            $result = $this->postModel->updatePost($id, $data);
            if ($result) {
                return Response::success(null, '文章更新成功');
            }

            return Response::error('文章更新失败', 500);
        }

        return View::html('post/edit.php', ['post' => $post]);
    }

    /**
     * 删除文章
     */
    public function actionDelete(): Response
    {
        $id = \FLEA\Request::current()->param('id');

        if (!$id) {
            return Response::error('文章ID不能为空', 400);
        }

        $result = $this->postModel->deletePost((int) $id);

        return $result
            ? Response::success(null, '文章删除成功')
            : Response::error('文章删除失败', 500);
    }

    /**
     * 添加评论
     */
    public function actionComment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return View::html('post/comment.php');
        }

        $data = [
            'post_id' => intval($_POST['post_id'] ?? 0),
            'author'  => $_POST['author'] ?? '匿名',
            'email'   => $_POST['email'] ?? '',
            'content' => $_POST['content'] ?? '',
        ];

        if (empty($data['post_id']) || empty($data['content'])) {
            return Response::error('评论内容不能为空', 400);
        }

        $id = $this->commentModel->createComment($data);
        if ($id) {
            return Response::success(null, '评论发表成功');
        }

        return Response::error('评论发表失败', 500);
    }
}
