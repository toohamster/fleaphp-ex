-- 创建博客数据库
CREATE DATABASE IF NOT EXISTS blog DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE blog;

-- 文章表
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT '文章标题',
    content TEXT NOT NULL COMMENT '文章内容',
    author VARCHAR(100) NOT NULL COMMENT '作者',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    status TINYINT DEFAULT 1 COMMENT '状态: 0-草稿, 1-发布',
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章表';

-- 评论表
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL COMMENT '文章ID',
    author VARCHAR(100) NOT NULL COMMENT '评论者',
    email VARCHAR(255) COMMENT '邮箱',
    content TEXT NOT NULL COMMENT '评论内容',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    status TINYINT DEFAULT 1 COMMENT '状态: 0-待审核, 1-已审核',
    INDEX idx_post_id (post_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论表';

-- 插入示例数据
INSERT INTO posts (title, content, author, status) VALUES
('欢迎来到我的博客', '这是我的第一篇博客文章，使用 FLEA 框架开发的。', '管理员', 1),
('FLEA 框架介绍', 'FLEA 是一个轻量级的 PHP MVC 框架，支持 PSR-4 自动加载，使用 Composer 管理依赖。', '管理员', 1),
('PHP 最佳实践', '1. 使用命名空间\n2. 遵循 PSR 标准\n3. 使用 Composer 管理依赖\n4. 编写单元测试', '技术专家', 1);

INSERT INTO comments (post_id, author, email, content, status) VALUES
(1, '访客1', 'visitor1@example.com', '恭喜你的博客上线了！', 1),
(1, '访客2', 'visitor2@example.com', '期待更多精彩内容', 1),
(2, '开发者', 'dev@example.com', 'FLEA 框架确实不错', 1);
