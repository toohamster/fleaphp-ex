<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的博客</title>
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
            max-width: 1000px;
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

        .posts {
            display: grid;
            gap: 20px;
        }

        .post {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .post h2 {
            color: #4a90e2;
            margin-bottom: 10px;
            font-size: 1.8em;
        }

        .post-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .post-excerpt {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .read-more {
            display: inline-block;
            color: #4a90e2;
            text-decoration: none;
            padding: 8px 20px;
            border: 2px solid #4a90e2;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .read-more:hover {
            background: #4a90e2;
            color: white;
        }

        .pagination {
            margin-top: 30px;
            text-align: center;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 15px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 5px;
            background: white;
            color: #4a90e2;
            border: 1px solid #ddd;
        }

        .pagination a:hover {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }

        .pagination .current {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
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
                <a href="?controller=Post&action=index">首页</a>
                <a href="?controller=Post&action=create">写文章</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="posts">
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p style="text-align: center; color: #999;">暂无文章</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span>作者：<?php echo htmlspecialchars($post['author']); ?></span>
                            <span> | </span>
                            <span>发布时间：<?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
                        </div>
                        <div class="post-excerpt">
                            <?php echo htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 200)); ?>...
                        </div>
                        <a href="?controller=Post&action=view&id=<?php echo $post['id']; ?>" class="read-more">阅读全文</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?controller=Post&action=index&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> 我的博客. 基于 FLEA 框架开发.</p>
    </footer>
</body>
</html>
