# FLEA Blog Application

A simple blog system developed using the FLEA framework.

## Features

- ✅ Article list display
- ✅ Article detail view
- ✅ Create new articles
- ✅ Edit articles
- ✅ Delete articles
- ✅ Comment functionality
- ✅ Pagination
- ✅ Responsive design

## Installation

### 1. Create Database

```bash
mysql -u root -p < blog.sql
```

Or manually execute the SQL statements in `blog.sql`.

### 2. Configure Database Connection

Pre-configured database information:
- Host: 127.0.0.1:3306
- Username: root
- Password: 11111111
- Database: blog

To modify, edit the `App/Config.php` file.

### 3. Access the Application

Open your browser and visit:
```
http://localhost/fleaphp-ex/index.php
```

## Project Structure

```
fleaphp-ex/
├── App/
│   ├── Config.php          # Application configuration
│   ├── Controller/
│   │   └── Post.php         # Article controller
│   ├── Model/
│   │   ├── Post.php         # Article model
│   │   └── Comment.php      # Comment model
│   └── View/
│       └── post/
│           ├── index.php    # Article list page
│           ├── view.php     # Article detail page
│           ├── create.php   # Create article page
│           └── edit.php     # Edit article page
├── blog.sql                 # Database initialization script
├── index.php                # Application entry point
└── FLEA/                    # FLEA framework core
```

## Usage

### Access Homepage
```
http://localhost/fleaphp-ex/index.php
or
http://localhost/fleaphp-ex/index.php?controller=Post&action=index
```

### View Article
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=view&id=1
```

### Create Article
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=create
```

### Edit Article
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=edit&id=1
```

### Delete Article
```
http://localhost/fleaphp-ex/index.php?controller=Post&action=delete&id=1
```

## Tech Stack

- **Framework**: FLEA (PSR-4 compliant)
- **Database**: MySQL
- **PHP**: 7.1+
- **Template Engine**: Native PHP
- **CSS**: Custom responsive styles

## Database Schema

### posts (Articles Table)
- id: Primary key
- title: Article title
- content: Article content
- author: Author
- created_at: Created time
- updated_at: Updated time
- status: Status (0-Draft, 1-Published)

### comments (Comments Table)
- id: Primary key
- post_id: Article ID (Foreign Key)
- author: Commenter
- email: Email
- content: Comment content
- created_at: Created time
- status: Status (0-Pending, 1-Approved)

## Development Guide

### Adding New Controllers

1. Create a new controller class in the `App/Controller/` directory
2. Extend `\FLEA\Controller\Action`
3. Implement methods prefixed with `action`

Example:
```php
<?php

namespace App\Controller;

use \FLEA\Controller\Action;

class MyController extends Action
{
    public function actionIndex()
    {
        // Your logic here
    }
}
```

### Adding New Models

1. Create a new model class in the `App/Model/` directory
2. Extend `\FLEA\Db\TableDataGateway`

Example:
```php
<?php

namespace App\Model;

use \FLEA\Db\TableDataGateway;

class MyModel extends TableDataGateway
{
    protected $tableName = 'my_table';
    protected $primaryKey = 'id';
}
```

## License

MIT License

## Authors

FLEA Framework Team
