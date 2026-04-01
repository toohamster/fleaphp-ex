<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - 我的博客</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: #4a90e2;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        header h1 {
            text-align: center;
        }

        nav {
            text-align: center;
            margin-top: 10px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .post {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .post h1 {
            color: #333;
            margin-bottom: 15px;
        }

        .post-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }

        .post-content {
            color: #444;
            line-height: 1.8;
        }

        .post-actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .btn-primary, .btn-danger {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: #4a90e2;
            color: white;
        }

        .btn-primary:hover {
            background: #3a7bc8;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .comments-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .comments-section h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .no-comments {
            color: #666;
            text-align: center;
            padding: 30px;
        }

        .comment {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-author {
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 5px;
        }

        .comment-date {
            font-size: 0.85em;
            color: #999;
            margin-bottom: 10px;
        }

        .comment-content {
            color: #444;
            line-height: 1.6;
        }

        .comment-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #4a90e2;
        }

        .comment-form h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4a90e2;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>我的博客</h1>
            <nav>
                <a href="/">首页</a>
                <a href="/post">文章列表</a>
                <a href="/post/create">写文章</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <a href="/post" class="back-link">← 返回文章列表</a>

        <article class="post">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span>作者：<?php echo htmlspecialchars($post['author'] ?? '匿名'); ?></span>
                <span>发布时间：<?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
                <span> | </span>
                <span>更新时间：<?php echo date('Y-m-d H:i', strtotime($post['updated_at'])); ?></span>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            <div class="post-actions">
                <a href="<?php echo url('post.edit', ['id' => $post['id']]); ?>" class="btn-primary">编辑文章</a>
                <button type="button" class="btn-danger" onclick="deletePost(<?php echo $post['id']; ?>)">删除文章</button>
            </div>
        </article>

        <section class="comments-section">
            <h2>评论 (<?php echo $commentCount; ?>)</h2>

            <?php if (empty($comments)): ?>
                <div class="no-comments">暂无评论，快来发表第一条评论吧！</div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-author">
                            <?php echo htmlspecialchars($comment['author']); ?>
                            <?php if ($comment['email']): ?>
                                <span style="color: #999; font-weight: normal;">(<?php echo htmlspecialchars($comment['email']); ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-date">
                            <?php echo date('Y-m-d H:i:s', strtotime($comment['created_at'])); ?>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="comment-form">
                <h3>发表评论</h3>
                <form method="POST" action="<?php echo url('post.comment'); ?>">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                    <div class="form-group">
                        <label>昵称：</label>
                        <input type="text" name="author" required>
                    </div>

                    <div class="form-group">
                        <label>邮箱（可选）：</label>
                        <input type="email" name="email">
                    </div>

                    <div class="form-group">
                        <label>评论内容：</label>
                        <textarea name="content" required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">发表评论</button>
                </form>
            </div>
        </section>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> 我的博客。基于 FLEA 框架开发.</p>
    </footer>

    <script>
    function deletePost(id) {
        if (!confirm('确定要删除这篇文章吗？')) {
            return;
        }

        fetch('/post/' + id + '/delete', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.code === 0) {
                alert('文章删除成功');
                location.href = '/post';
            } else {
                alert('删除失败：' + data.message);
            }
        })
        .catch(err => {
            alert('网络错误：' + err);
        });
    }
    </script>
</body>
</html>
