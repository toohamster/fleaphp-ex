<?php

namespace App\Controller;

use App\Model\Post;
use App\Model\Comment;
use \FLEA\Controller\Action;

/**
 * 文章控制器
 */
class PostController extends Action
{
    /**
     * @var Post
     */
    protected $postModel;

    /**
     * @var Comment
     */
    protected $commentModel;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('Post');
        $this->postModel = new Post();
        $this->commentModel = new Comment();
    }

    /**
     * 文章列表页
     */
    public function actionIndex()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $posts = $this->postModel->getPublishedPosts($pageSize, $offset);
        $total = $this->postModel->getTotalCount();
        $totalPages = ceil($total / $pageSize);

        $this->view->assign('posts', $posts);
        $this->view->assign('page', $page);
        $this->view->assign('totalPages', $totalPages);
        $this->view->assign('total', $total);

        $this->view->display('post/index.php');
    }

    /**
     * 文章详情页
     */
    public function actionView()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$id) {
            die('文章ID不能为空');
        }

        $post = $this->postModel->getPostById($id);

        if (!$post) {
            die('文章不存在');
        }

        $comments = $this->commentModel->getCommentsByPostId($id);
        $commentCount = $this->commentModel->getCommentCount($id);

        $this->view->assign('post', $post);
        $this->view->assign('comments', $comments);
        $this->view->assign('commentCount', $commentCount);

        $this->view->display('post/view.php');
    }

    /**
     * 创建文章
     */
    public function actionCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author' => $_POST['author'] ?? '匿名',
                'status' => 1
            ];

            if (empty($data['title']) || empty($data['content'])) {
                echo '<script>alert("标题和内容不能为空"); history.back();</script>';
                return;
            }

            $id = $this->postModel->createPost($data);
            if ($id) {
                echo '<script>alert("文章创建成功"); location.href="?controller=Post&action=index";</script>';
            } else {
                echo '<script>alert("文章创建失败"); history.back();</script>';
            }
        } else {
            $this->view->display('post/create.php');
        }
    }

    /**
     * 编辑文章
     */
    public function actionEdit()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$id) {
            die('文章ID不能为空');
        }

        $post = $this->postModel->getPostById($id);

        if (!$post) {
            die('文章不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'author' => $_POST['author'] ?? '匿名'
            ];

            if (empty($data['title']) || empty($data['content'])) {
                echo '<script>alert("标题和内容不能为空"); history.back();</script>';
                return;
            }

            $result = $this->postModel->updatePost($id, $data);
            if ($result) {
                echo '<script>alert("文章更新成功"); location.href="?controller=Post&action=view&id=' . $id . '";</script>';
            } else {
                echo '<script>alert("文章更新失败"); history.back();</script>';
            }
        } else {
            $this->view->assign('post', $post);
            $this->view->display('post/edit.php');
        }
    }

    /**
     * 删除文章
     */
    public function actionDelete()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$id) {
            die('文章ID不能为空');
        }

        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            $result = $this->postModel->deletePost($id);
            if ($result) {
                echo '<script>alert("文章删除成功"); location.href="?controller=Post&action=index";</script>';
            } else {
                echo '<script>alert("文章删除失败"); history.back();</script>';
            }
        } else {
            echo '<script>if(confirm("确定要删除这篇文章吗？")) { location.href="?controller=Post&action=delete&id=' . $id . '&confirm=yes"; } else { history.back(); }</script>';
        }
    }

    /**
     * 添加评论
     */
    public function actionComment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'post_id' => $_POST['post_id'] ?? 0,
                'author' => $_POST['author'] ?? '匿名',
                'email' => $_POST['email'] ?? '',
                'content' => $_POST['content'] ?? ''
            ];

            if (empty($data['post_id']) || empty($data['content'])) {
                echo '<script>alert("评论内容不能为空"); history.back();</script>';
                return;
            }

            $id = $this->commentModel->createComment($data);
            if ($id) {
                echo '<script>alert("评论发表成功"); location.href="?controller=Post&action=view&id=' . $data['post_id'] . '";</script>';
            } else {
                echo '<script>alert("评论发表失败"); history.back();</script>';
            }
        }
    }
}
