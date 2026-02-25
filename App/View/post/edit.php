<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑文章 - 我的博客</title>
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

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 1em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            font-family: inherit;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
        }

        .form-group textarea {
            min-height: 300px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4a90e2;
            color: white;
        }

        .btn-primary:hover {
            background: #357abd;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
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
                <a href="?controller=Post&action=index">返回首页</a>
                <a href="?controller=Post&action=view&id=<?php echo $post['id']; ?>">查看文章</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2>编辑文章</h2>
            <form action="?controller=Post&action=edit&id=<?php echo $post['id']; ?>" method="POST">

                <div class="form-group">
                    <label for="title">文章标题：</label>
                    <input type="text" id="title" name="title" required
                           value="<?php echo htmlspecialchars($post['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="author">作者：</label>
                    <input type="text" id="author" name="author" required
                           value="<?php echo htmlspecialchars($post['author']); ?>">
                </div>

                <div class="form-group">
                    <label for="content">文章内容：</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">更新文章</button>
                    <a href="?controller=Post&action=view&id=<?php echo $post['id']; ?>" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> 我的博客. 基于 FLEA 框架开发.</p>
    </footer>
</body>
</html>
