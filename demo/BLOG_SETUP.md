# 博客应用安装指南 v2.0

基于 FleaPHP v2.0 框架开发的博客示例应用安装指南。

---

## 系统要求

- **PHP**: 7.4+
- **MySQL**: 5.0+ 或 PDO 支持的其他数据库
- **Composer**: 依赖管理工具
- **Web 服务器**: Apache/Nginx（可选，开发环境可用 PHP 内置服务器）

---

## 安装步骤

### 方式一：通过 Composer 安装（推荐）

```bash
composer require toohamster/fleaphp-ex
```

### 方式二：克隆项目

```bash
git clone https://github.com/toohamster/fleaphp-ex.git
cd fleaphp-ex
```

### 安装依赖

在项目根目录执行：

```bash
php74 ~/bin/composer.phar install
```

### 3. 配置环境变量

复制环境配置示例文件：

```bash
cp demo/.env.example demo/.env
```

编辑 `demo/.env` 文件，配置数据库连接：

```env
# 应用环境
APP_ENV=local
APP_DEBUG=true

# 数据库配置
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=your_password
DB_DATABASE=blog

# 日志配置
LOG_ENABLED=true
LOG_LEVEL=debug
LOG_FILENAME=app.log

# 缓存配置
CACHE_DRIVER=file
CACHE_DIR=cache
```

**配置说明**：

| 配置项 | 说明 | 默认值 |
|--------|------|--------|
| `APP_ENV` | 应用环境 | `local` |
| `APP_DEBUG` | 调试模式 | `true` |
| `DB_DRIVER` | 数据库驱动 | `mysql` |
| `DB_HOST` | 数据库主机 | `127.0.0.1` |
| `DB_PORT` | 数据库端口 | `3306` |
| `DB_USERNAME` | 数据库用户名 | `root` |
| `DB_PASSWORD` | 数据库密码 | 需修改 |
| `DB_DATABASE` | 数据库名 | `blog` |
| `LOG_ENABLED` | 是否启用日志 | `true` |
| `LOG_LEVEL` | 日志级别（debug/info/warning/error） | `debug` |
| `LOG_FILENAME` | 日志文件名 | `app.log` |
| `CACHE_DRIVER` | 缓存驱动（file/redis） | `file` |
| `CACHE_DIR` | 缓存目录 | `cache` |

### 4. 初始化数据库

```bash
mysql -u root -p < demo/blog.sql
```

`blog.sql` 会执行以下操作：
- 创建 `blog` 数据库
- 创建 `posts` 和 `comments` 数据表
- 插入示例数据（3 篇文章，3 条评论）

### 5. 设置缓存目录权限

```bash
chmod -R 777 demo/cache/
```

### 6. 启动开发服务器

在项目根目录执行：

```bash
php bin/flea-cli --project-dir=demo
```

或者在 `demo` 目录下执行：

```bash
cd demo && php ../bin/flea-cli
```

访问：`http://127.0.0.1:8081/`

---

## 项目结构

```
fleaphp-ex/
├── demo/                       # 博客应用目录
│   ├── .env                    # 环境配置
│   ├── .env.example            # 配置示例
│   ├── blog.sql                # 数据库初始化脚本
│   ├── public/
│   │   └── index.php           # Web 入口
│   ├── cache/                  # 缓存目录
│   └── App/
│       ├── Config.php          # 应用配置
│       ├── routes.php          # 路由定义
│       ├── Controller/
│       │   └── PostController.php   # 文章控制器
│       ├── Model/
│       │   ├── Post.php             # 文章模型
│       │   └── Comment.php          # 评论模型
│       └── View/
│           └── post/
│               ├── index.php        # 文章列表页
│               ├── view.php         # 文章详情页
│               ├── create.php       # 创建文章页
│               └── edit.php         # 编辑文章页
├── src/FLEA/                   # 框架核心代码
├── vendor/                     # Composer 依赖
└── bin/
    └── flea-cli                # CLI 启动脚本
```

---

## 数据库结构

### posts 表（文章表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| title | VARCHAR(255) | 文章标题 |
| content | TEXT | 文章内容 |
| author | VARCHAR(100) | 作者 |
| created_at | DATETIME | 创建时间（自动填充） |
| updated_at | DATETIME | 更新时间（自动更新） |
| status | TINYINT | 0=草稿，1=发布 |

### comments 表（评论表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| post_id | INT | 文章 ID（外键，级联删除） |
| author | VARCHAR(100) | 评论者 |
| email | VARCHAR(255) | 邮箱（可选） |
| content | TEXT | 评论内容 |
| created_at | DATETIME | 创建时间（自动填充） |
| status | TINYINT | 0=待审核，1=已审核 |

---

## 示例数据

数据库中已包含以下示例数据：

### 文章

1. **欢迎来到我的博客** - 管理员
2. **FleaPHP 框架介绍** - 管理员
3. **PHP 最佳实践** - 技术专家

### 评论

1. 访客 1 对文章 1 的评论
2. 访客 2 对文章 1 的评论
3. 开发者 对文章 2 的评论

---

## 功能特性

### 文章管理
- 查看文章列表（分页显示）
- 查看文章详情
- 创建新文章
- 编辑文章
- 删除文章

### 评论功能
- 查看文章评论
- 发表评论

### 用户界面
- 响应式设计
- 简洁美观的界面
- 友好的交互体验

---

## 访问方式

### 开发服务器

启动后访问：`http://127.0.0.1:8081/`

### URL 路由

| 功能 | URL |
|------|-----|
| 文章列表 | `/post` |
| 文章详情 | `/post/1` |
| 创建文章 | `/post/create` |
| 编辑文章 | `/post/1/edit` |
| 删除文章 | `/post/1/delete` (POST) |

### 命名路由

在视图文件中使用：

```php
// 生成文章详情页 URL
<a href="<?php echo \FLEA\Router::urlFor('post.view', ['id' => $post['id']]); ?>">
    查看详情
</a>

// 生成编辑页 URL
<a href="<?php echo \FLEA\Router::urlFor('post.edit', ['id' => $post['id']]); ?>">
    编辑
</a>
```

---

## 生产环境部署

### 1. 修改环境配置

编辑 `demo/.env`：

```env
APP_ENV=production
APP_DEBUG=false
```

### 2. 配置 Web 服务器

**Apache 配置示例**：

```apache
<VirtualHost *:80>
    ServerName blog.example.com
    DocumentRoot /path/to/fleaphp-ex/demo/public

    <Directory /path/to/fleaphp-ex/demo/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx 配置示例**：

```nginx
server {
    listen 80;
    server_name blog.example.com;
    root /path/to/fleaphp-ex/demo/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. 优化设置

- 开启视图缓存（`App/Config.php` 中设置 `enableCache => true`）
- 关闭错误显示
- 使用 OPcache 加速

---

## 故障排除

### 数据库连接失败

检查 `.env` 中的数据库配置是否正确：

```bash
# 测试数据库连接
mysql -u root -p -e "USE blog; SELECT 1;"
```

### 缓存目录权限错误

```bash
chmod -R 777 demo/cache/
```

### 自动加载问题

重新生成自动加载文件：

```bash
php74 ~/bin/composer.phar dump-autoload
```

### PHP 版本检查

确保使用 PHP 7.4+：

```bash
php74 -v
```

### 重置数据库

```bash
mysql -u root -p < demo/blog.sql
```

---

## 技术栈

| 组件 | 版本/类型 |
|------|----------|
| 框架 | FleaPHP v2.0 |
| PHP | 7.4+ |
| 数据库 | MySQL 5.0+ |
| 依赖管理 | Composer |
| 模板引擎 | Simple View |

---

## 下一步

你可以根据需要扩展此博客系统：

- 添加用户登录/注册功能
- 添加文章标签和分类
- 添加文章搜索功能
- 添加图片上传功能
- 添加后台管理界面
- 添加 Markdown 编辑器

---

## 参考文档

- [APP_USAGE_GUIDE.md](APP_USAGE_GUIDE.md) - 博客应用使用手册
- [USER_GUIDE.md](../USER_GUIDE.md) - 框架用户手册
- [SPEC.md](../SPEC.md) - 框架规格说明书
