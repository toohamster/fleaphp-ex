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
            font-size: 2.5em;
        }

        nav {
            text-align: center;
            margin-top: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.1em;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.3s;
        }

        nav a:hover {
            background: rgba(255,255,255,0.2);
        }

        .post {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .post h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2.2em;
            line-height: 1.3;
        }

        .post-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .post-content {
            font-size: 1.1em;
            line-height: 1.8;
            color: #444;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .post-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .post-actions a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4a90e2;
            color: white;
        }

        .btn-primary:hover {
            background: #357abd;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .comments-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .comments-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .comment {
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #4a90e2;
        }

        .comment-author {
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 5px;
        }

        .comment-date {
            color: #999;
            font-size: 0.85em;
            margin-bottom: 10px;
        }

        .comment-content {
            color: #555;
            line-height: 1.6;
        }

        .comment-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .comment-form h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
        }

        .btn-submit {
            background: #4a90e2;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #357abd;
        }

        .no-comments {
            text-align: center;
            color: #999;
            padding: 30px;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📝 我的博客</h1>
            <nav>
                <a href="<?php echo url('post.index'); ?>">返回首页</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <article class="post">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span>作者：<?php echo htmlspecialchars($post['author']); ?></span>
                <span> | </span>
                <span>发布时间：<?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
                <span> | </span>
                <span>更新时间：<?php echo date('Y-m-d H:i', strtotime($post['updated_at'])); ?></span>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            <div class="post-actions">
                <a href="<?php echo url('post.edit', ['id' => $post['id']]); ?>" class="btn-primary">编辑文章</a>
                <a href="<?php echo url('post.delete', ['id' => $post['id']]); ?>" class="btn-danger">删除文章</a>
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
                <form action="<?php echo url('post.comment'); ?>" method="POST">
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
        <p>&copy; <?php echo date('Y'); ?> 我的博客. 基于 FLEA 框架开发.</p>
    </footer>
</body>
</html>
