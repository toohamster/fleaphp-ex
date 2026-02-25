# FleaPHP Developer User Guide (English Version)

## Table of Contents

1. [Introduction](#introduction)
2. [Quick Start](#quick-start)
3. [Core Concepts](#core-concepts)
4. [Configuration Management](#configuration-management)
5. [Class Loading and Autoloading](#class-loading-and-autoloading)
6. [Object Registration and Singleton Pattern](#object-registration-and-singleton-pattern)
7. [Database Operations](#database-operations)
8. [TableDataGateway - Table Data Gateway](#tabledatagateway---table-data-gateway)
9. [MVC Pattern](#mvc-pattern)
10. [Cache Management](#cache-management)
11. [RBAC Permission Control](#rbac-permission-control)
12. [Exception Handling](#exception-handling)
13. [Helper Functions](#helper-functions)
14. [URL Generation](#url-generation)
15. [Best Practices](#best-practices)

---

## Introduction

FleaPHP is a lightweight PHP framework that provides complete MVC development support, database abstraction layer, cache management, and other features. This manual will help developers quickly get started and make full use of FleaPHP's functionality.

**Important Note**: FleaPHP has been fully migrated to PSR-4 namespace standards and Composer autoloading. All framework classes use namespaces (e.g., `\FLEA\Controller\Action`), and are loaded through Composer's PSR-4 autoloader. All examples in this manual have been updated to reflect these changes.

### Features

- **Lightweight**: Core code is streamlined with high performance
- **MVC Architecture**: Supports Model-View-Controller pattern
- **Database Abstraction**: Supports multiple databases with unified operation interface
- **PSR-4 Autoloading**: Composer-based PSR-4 standard autoloading
- **Object Container**: Manages singleton instances
- **Cache System**: Built-in file cache support
- **Flexible Configuration**: Supports both debug and production modes

### System Requirements

- PHP 7.0 or higher
- Composer (for dependency management and autoloading)
- Supported databases: MySQL, PostgreSQL, SQLite, etc.

---

## Quick Start

### Installation

Copy the FleaPHP framework files to your project directory:

```
your-project/
├── FLEA/
│   ├── FLEA.php
│   └── FLEA/
│       ├── Config.php
│       └── ...
├── composer.json
├── index.php
└── ...
```

Ensure your project includes a `composer.json` file and run `composer install` to generate `vendor/autoload.php`:

```bash
composer install
```

### Basic Configuration

Create a configuration file `config.php`:

```php
<?php
return [
    // Database configuration
    'dbDSN' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'login'    => 'username',
        'password' => 'password',
        'database' => 'your_database',
        'charset'  => 'utf8',
    ],
    'dbTablePrefix' => 'tbl_',

    // URL configuration
    'urlMode' => URL_PATHINFO, // or URL_STANDARD, URL_REWRITE
    'urlLowerChar' => false,
    'defaultController' => 'Index',
    'defaultAction' => 'index',

    // Charset configuration
    'defaultLanguage' => 'chinese-utf8',
    'responseCharset' => 'UTF-8',
    'databaseCharset' => 'UTF-8',

    // Cache configuration
    'internalCacheDir' => dirname(__FILE__) . '/Cache',
];
```

### Initialize Framework

Initialize the framework in your entry file `index.php`:

```php
<?php
require('vendor/autoload.php');

// Load application configuration
FLEA::loadAppInf('config.php');

// Run MVC application
FLEA::runMVC();
```

**Note**: With Composer, the framework is loaded through `vendor/autoload.php` automatically. No need to manually `require('FLEA/FLEA.php')`.

---

## Core Concepts

### Configuration Management

FleaPHP uses the `\FLEA\Config` singleton class to manage all configuration. The framework automatically initializes the configuration manager when loaded.

### Object Container

The framework maintains an object container to store and manage singleton instances. Objects can be registered and retrieved using `FLEA::register()` and `FLEA::registry()` methods.

### Class File Search Paths

The framework uses Composer's PSR-4 autoloader exclusively for loading class files. No manual class search path configuration is needed. All classes are automatically located by their namespace.

**PSR-4 Namespace Mapping:**

```json
{
    "autoload": {
        "psr-4": {
            "FLEA\\": "FLEA/FLEA/"
        }
    }
}
```

Class name to file path mapping:
- `\FLEA\Config` → `FLEA/FLEA/Config.php`
- `\FLEA\Db\TableDataGateway` → `FLEA/FLEA/Db/TableDataGateway.php`
- `\FLEA\Controller\Action` → `FLEA/FLEA/Controller/Action.php`

### Database Connection Pool

The framework maintains a database connection pool. Identical DSNs will return the same database connection object.

---

## Configuration Management

### Getting Configuration Items

Use `FLEA::getAppInf()` to retrieve configuration items:

```php
$charset = FLEA::getAppInf('responseCharset'); // Get response charset
$controller = FLEA::getAppInf('defaultController'); // Get default controller
```

Specify default values when configuration item doesn't exist:

```php
$timeout = FLEA::getAppInf('requestTimeout', 30);
```

### Setting Configuration Items

Use `FLEA::setAppInf()` to set configuration items:

```php
FLEA::setAppInf('siteTitle', 'My Website');

// Batch set multiple items
FLEA::setAppInf([
    'siteTitle' => 'My Website',
    'siteUrl' => 'https://example.com',
]);
```

### Loading Configuration Files

Use `FLEA::loadAppInf()` to load configuration files:

```php
FLEA::loadAppInf('./config/database.php');
```

Configuration file should return an array:

```php
<?php
return [
    'dbDSN' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        // ...
    ],
];
```

### Array Configuration Item Operations

Retrieve specific values from array configuration items:

```php
// Get array configuration item value
$maxSize = FLEA::getAppInfValue('upload', 'maxSize', 1048576);

// Set array configuration item value
FLEA::setAppInfValue('upload', 'allowedTypes', ['jpg', 'png', 'gif']);
```

### Configuration Constants

Framework predefined constants:

| Constant | Description |
|----------|-------------|
| `FLEA_VERSION` | FleaPHP version number |
| `PHP5` | PHP version identifier (true) |
| `PHP4` | PHP version identifier (false) |
| `DS` | Directory separator shortcut |
| `FLEA_DIR` | FLEA framework directory |
| `DEBUG_MODE` | Debug mode indicator |

URL Mode Constants:

| Constant | Value | Description |
|----------|-------|-------------|
| `URL_STANDARD` | `URL_STANDARD` | Standard URL mode (?controller=...) |
| `URL_PATHINFO` | `URL_PATHINFO` | PATHINFO mode (/controller/action/) |
| `URL_REWRITE` | `URL_REWRITE` | URL rewrite mode (/controller/action/)

---

## Class Loading and Autoloading

### Composer PSR-4 Autoloading

FleaPHP now uses Composer's PSR-4 autoloader to load all class files. This is the modern standard for PHP development, providing better performance and maintainability.

#### Namespace Usage

All framework classes use PSR-4 namespace standards:

```php
// Use fully qualified names
$config = new \FLEA\Config();
$userTable = new \FLEA\Db\TableDataGateway();
$dispatcher = new \FLEA\Dispatcher\Auth();
```

#### Autoloading Mechanism

Composer automatically loads class files based on namespace without manual include/require:

```php
// Use classes, Composer automatically loads corresponding file
$userTable = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);

// Equivalent to
use FLEA\Db\TableDataGateway;
$userTable = FLEA::getSingleton(TableDataGateway::class);
```

#### Class Name to File Path Mapping

| Class Name | File Path |
|------------|-----------|
| `\FLEA\Config` | `FLEA/FLEA/Config.php` |
| `\FLEA\Db\TableDataGateway` | `FLEA/FLEA/Db/TableDataGateway.php` |
| `\FLEA\Db\ActiveRecord` | `FLEA/FLEA/Db/ActiveRecord.php` |
| `\FLEA\Controller\Action` | `FLEA/FLEA/Controller/Action.php` |
| `\FLEA\Dispatcher\Auth` | `FLEA/FLEA/Dispatcher/Auth.php` |
| `\FLEA\Exception\MissingController` | `FLEA/FLEA/Exception/MissingController.php` |

### Loading Non-Class Files

For files that don't contain class definitions (e.g., function libraries, configuration files), use `require_once()` to load manually:

```php
// Load function library
require_once dirname(__FILE__) . '/lib/functions.php';

// Load configuration file
require_once dirname(__FILE__) . '/config/routes.php';
```

### Auto-Loading Configuration Files

Auto-load configuration files through `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "FLEA\\": "FLEA/FLEA/"
        },
        "files": [
            "FLEA/FLEA.php",
            "FLEA/Functions.php"
        ]
    }
}
```

### Regenerate Autoloading Files

After modifying `composer.json`, regenerate autoloading files:

```bash
composer dump-autoload
```

---

## Object Registration and Singleton Pattern

### Registering Objects

Use `FLEA::register()` to register objects to the object container:

```php
$cache = new Cache();
FLEA::register($cache, 'Cache');

// When not specifying name, uses class name
$cache = new Cache();
FLEA::register($cache);
// Equivalent to: FLEA::register($cache, 'Cache');
```

### Retrieving Objects

Use `FLEA::registry()` to retrieve registered objects:

```php
// Get object by name
$cache = FLEA::registry('Cache');

// Get all objects when no name specified
$objects = FLEA::registry();
```

### Checking Object Registration

Use `FLEA::isRegistered()` to check if object is registered:

```php
if (FLEA::isRegistered('Cache')) {
    $cache = FLEA::registry('Cache');
}
```

### Getting Singleton Objects

Use `FLEA::getSingleton()` to get singleton instances:

```php
// First call creates and registers the instance
$userModel = FLEA::getSingleton('Table_Users');

// Subsequent calls return the same instance
$userModel2 = FLEA::getSingleton('Table_Users');

// $userModel and $userModel2 are the same object
var_dump($userModel === $userModel2); // bool(true)
```

---

## Database Operations

### Getting Database Connection

Use `FLEA::getDBO()` to get database connection object:

```php
// Use default DSN from configuration
$dbo = FLEA::getDBO();

// Use specified DSN
$dsn = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'login' => 'username',
    'password' => 'password',
    'database' => 'test',
];
$dbo = FLEA::getDBO($dsn);

// Use DSN string
$dbo = FLEA::getDBO('mysql://username:password@localhost:3306/database?options');
```

### DSN Format

DSN (Data Source Name) describes database connection information.

**Array Format:**

```php
$dsn = [
    'driver'   => 'mysql',        // Database driver
    'host'     => 'localhost',    // Host address
    'port'     => 3306,          // Port number
    'login'    => 'username',    // Username
    'password' => 'password',     // Password
    'database' => 'test_db',      // Database name
    'charset'  => 'utf8',         // Charset
    'prefix'   => 'tbl_',        // Table prefix
    'schema'   => '',            // Schema (PostgreSQL)
    'options'  => '',            // Extra options
];
```

**String Format:**

```
mysql://username:password@host:port/database?options
```

Examples:

```php
$dsn = 'mysql://root:123456@localhost:3306/mydb';
$dsn = 'mysql://root:123456@localhost/mydb?charset=utf8';
```

### Connection Pool

Identical DSN will return the same database connection object:

```php
// First call creates connection
$dbo1 = FLEA::getDBO();

// Second call returns same connection
$dbo2 = FLEA::getDBO();

var_dump($dbo1 === $dbo2); // bool(true)
```

---

## TableDataGateway - Table Data Gateway

FleaPHP provides `FLEA_Db_TableDataGateway` class (Table Data Gateway) for encapsulating CRUD operations on data tables. Developers should derive their data access classes from this class.

### Defining Table Data Gateway Classes

Create a table data gateway class by extending `\FLEA\Db\TableDataGateway`:

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    /**
     * Table name (without prefix)
     */
    public $tableName = 'users';

    /**
     * Primary key field name
     */
    public $primaryKey = 'user_id';
}
```

Using the class:

```php
$userTable = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);

// Or
use FLEA\Db\TableDataGateway;
$userTable = FLEA::getSingleton(TableDataGateway::class);
```

### Table Relationship Definitions

FleaPHP supports four types of table relationships:

| Relationship Type | Constant | Description |
|-------------------|----------|-------------|
| One-to-one | `HAS_ONE` | One record owns another associated record |
| One-to-many | `HAS_MANY` | One record has multiple associated records |
| Belongs-to | `BELONGS_TO` | One record belongs to another record |
| Many-to-many | `MANY_TO_MANY` | Two tables reference each other |

#### One-to-One Relationship (HAS_ONE)

One user corresponds to one profile:

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * Define one-to-one relationship
     */
    public $hasOne = [
        'Profile' => [
            'tableClass' => 'Table_UserProfiles',
            'foreignKey' => 'user_id',
            'mappingName' => 'profile',
        ],
    ];
}
```

Usage:

```php
$userTable = FLEA::getSingleton('Table_Users');
$user = $userTable->find(1);

// Access associated data
$profile = $user['profile'];
```

#### One-to-Many Relationship (HAS_MANY)

One department has multiple employees:

```php
class Table_Departments extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'departments';
    public $primaryKey = 'dept_id';

    /**
     * Define one-to-many relationship
     */
    public $hasMany = [
        'Employees' => [
            'tableClass' => 'Table_Employees',
            'foreignKey' => 'dept_id',
            'mappingName' => 'employees',
            'sort' => 'employee_id DESC',
        ],
    ];
}
```

Usage:

```php
$deptTable = FLEA::getSingleton('Table_Departments');
$dept = $deptTable->find(1);

// Access associated employees list
$employees = $dept['employees'];
```

#### Belongs-to Relationship (BELONGS_TO)

One user belongs to one role:

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * Define belongs-to relationship
     */
    public $belongsTo = [
        'Role' => [
            'tableClass' => 'Table_Roles',
            'foreignKey' => 'role_id',
            'mappingName' => 'role',
        ],
    ];
}
```

Usage:

```php
$userTable = FLEA::getSingleton('Table_Users');
$user = $userTable->find(1);

// Access owned role
$role = $user['role'];
```

#### Many-to-Many Relationship (MANY_TO_MANY)

Students and courses have many-to-many relationship, linked through a junction table:

```php
class Table_Students extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'students';
    public $primaryKey = 'student_id';

    /**
     * Define many-to-many relationship
     */
    public $manyToMany = [
        'Courses' => [
            'tableClass' => 'Table_Courses',
            'joinTable' => 'student_courses', // Junction table
            'foreignKey' => 'student_id',    // Field in junction table pointing to this table
            'assocForeignKey' => 'course_id', // Field in junction table pointing to associated table
            'mappingName' => 'courses',
        ],
    ];
}
```

Usage:

```php
$studentTable = FLEA::getSingleton('Table_Students');
$student = $studentTable->find(1);

// Access enrolled courses list
$courses = $student['courses'];
```

### Querying Data

#### Finding Single Record (find)

```php
// Find by primary key
$user = $userTable->find(1);

// Find by conditions
$user = $userTable->find(['username' => 'john']);

// Specify sort order
$user = $userTable->find(['status' => 'active'], 'user_id DESC']);

// Specify query fields
$user = $userTable->find(1, null, 'user_id, username, email');

// Don't query associated data
$user = $userTable->find(1, null, '*', false);
```

#### Finding Multiple Records (findAll)

```php
// Query all records
$users = $userTable->findAll();

// Query by conditions
$users = $userTable->findAll(['status' => 'active']);

// Specify sort order and pagination
$users = $userTable->findAll(
    ['status' => 'active'],
    'user_id DESC',
    10,    // Limit 10 records
    0       // Start from 0
);

// Use array format for pagination
$users = $userTable->findAll(
    null,
    null,
    [10, 0], // array(length, offset)
);

// Specify query fields
$users = $userTable->findAll(null, null, null, 'user_id, username');
```

#### Finding by Field (findByField / findAllByField)

```php
// Find single record
$user = $userTable->findByField('username', 'john');

// Find multiple records
$users = $userTable->findAllByField('status', 'active', 'user_id DESC');

// With pagination
$users = $userTable->findAllByField('status', 'active', null, [10, 0]);
```

#### Finding by Primary Keys (findAllByPkvs)

```php
// Find by multiple primary key values
$users = $userTable->findAllByPkvs([1, 2, 3, 4]);

// With conditions
$users = $userTable->findAllByPkvs([1, 2, 3], ['status' => 'active']);
```

#### Using SQL Query (findBySql)

```php
// Use custom SQL query
$sql = "SELECT * FROM users WHERE status = 'active'";
$users = $userTable->findBySql($sql);

// With pagination
$users = $userTable->findBySql($sql, 10); // First 10 records
$users = $userTable->findBySql($sql, [10, 0]); // Records 0-10
```

### Condition Expressions

#### Simple Conditions

```php
// Field = value
$users = $userTable->findAll(['username' => 'john']);

// Multiple conditions (AND relationship)
$users = $userTable->findAll([
    'status' => 'active',
    'age' => 25,
]);
```

#### OR Conditions

```php
$users = $userTable->findAll([
    'or',
    'status' => 'active',
    'status' => 'pending',
]);
```

#### IN Conditions

```php
$users = $userTable->findAll([
    'user_id' => ['in()' => [1, 2, 3, 4]],
]);

// Equivalent to SQL: WHERE user_id IN (1, 2, 3, 4)
```

#### LIKE Conditions

```php
$users = $userTable->findAll([
    'username' => ['like' => 'john%'],
]);

// Equivalent to SQL: WHERE username LIKE 'john%'
```

#### Comparison Conditions

```php
$users = $userTable->findAll([
    'age' => ['>' => 18],
    'created_at' => ['<=' => '2024-01-01'],
]);

// Equivalent to SQL: WHERE age > 18 AND created_at <= '2024-01-01'
```

#### Complex Conditions

```php
$users = $userTable->findAll([
    'or',
    [
        'and',
        'status' => 'active',
        'age' => ['>' => 18],
    ],
    [
        'and',
        'status' => 'vip',
        'age' => ['>' => 25],
    ],
]);

// Equivalent to SQL: WHERE (status = 'active' AND age > 18) OR (status = 'vip' AND age > 25)
```

### Creating Records (create)

```php
// Create single record
$row = [
    'username' => 'john',
    'email' => 'john@example.com',
    'status' => 'active',
];

$newUserId = $userTable->create($row);

// $newUserId contains primary key value of new inserted record
echo "New user ID: " . $newUserId;

// Auto-fill time fields on create
// If data table has CREATED, CREATED_ON, CREATED_AT fields
// Will automatically fill current time
```

#### Creating Multiple Records (createRowset)

```php
$rows = [
    [
        'username' => 'user1',
        'email' => 'user1@example.com',
    ],
    [
        'username' => 'user2',
        'email' => 'user2@example.com',
    ],
];

$userTable->createRowset($rows);
```

#### Don't Process Associated Records on Create

```php
// Process associated data on create (default)
$userTable->create($row, true);

// Don't process associated data
$userTable->create($row, false);
```

### Updating Records (update)

```php
// Update by primary key
$row = [
    'user_id' => 1,
    'email' => 'newemail@example.com',
    'status' => 'active',
];

$userTable->update($row);
```

#### Update by Conditions (updateByConditions)

```php
$conditions = ['status' => 'pending'];
$row = ['status' => 'active'];

$userTable->updateByConditions($conditions, $row);

// Equivalent to SQL: UPDATE users SET status = 'active' WHERE status = 'pending'
```

#### Update Single Field (updateField)

```php
$conditions = ['user_id' => 1];
$userTable->updateField($conditions, 'email', 'newemail@example.com');

// Equivalent to SQL: UPDATE users SET email = 'newemail@example.com' WHERE user_id = 1
```

#### Update Multiple Records (updateRowset)

```php
$rows = [
    ['user_id' => 1, 'status' => 'active'],
    ['user_id' => 2, 'status' => 'active'],
];

$userTable->updateRowset($rows);
```

### Deleting Records (remove)

```php
// Delete by primary key
$row = $userTable->find(1);
$userTable->remove($row);

// Or delete directly by primary key value
$userTable->removeByPkv(1);
```

#### Delete by Conditions (removeByConditions)

```php
$conditions = ['status' => 'deleted'];
$userTable->removeByConditions($conditions);

// Equivalent to SQL: DELETE FROM users WHERE status = 'deleted'
```

#### Delete by Multiple Primary Keys (removeByPkvs)

```php
$userTable->removeByPkvs([1, 2, 3, 4]);

// Equivalent to SQL: DELETE FROM users WHERE user_id IN (1, 2, 3, 4)
```

#### Delete All Records (removeAll / removeAllWithLinks)

```php
// Delete all records (don't process associations)
$userTable->removeAll();

// Delete all records (process associations)
$userTable->removeAllWithLinks();
```

#### Delete with Association Handling

```php
// Process associations when deleting (default)
$userTable->remove($row, true);  // Process associations

// Don't process associations
$userTable->remove($row, false);
```

### Saving Records (save)

`save()` method automatically determines whether to create a new record or update an existing record:

```php
$row = [
    'username' => 'john',
    'email' => 'john@example.com',
];

// First call creates record
$userTable->save($row);
// $row now contains primary key value
echo "User ID: " . $row['user_id'];

// Modify and save again will update record
$row['email'] = 'newemail@example.com';
$userTable->save($row);
```

#### Save Multiple Records (saveRowset)

```php
$rows = [
    [
        'username' => 'user1',
        'email' => 'user1@example.com',
    ],
    [
        'username' => 'user2',
        'email' => 'user2@example.com',
    ],
];

$userTable->saveRowset($rows);
```

### Table Relationships

#### Enable/Disable Links

```php
// Disable all associations
$userTable->disableLinks();

// Enable all associations
$userTable->enableLinks();

// Enable specific associations
$userTable->enableLinks(['profile', 'role']);

// Disable specific associations
$userTable->disableLinks(['profile', 'role']);
```

#### Dynamically Create Links

```php
// Create relationship
$defines = [
    'tableClass' => 'Table_UserProfiles',
    'foreignKey' => 'user_id',
    'mappingName' => 'profile',
];

$userTable->createLink($defines, HAS_ONE);

// Delete relationship
$userTable->removeLink('profile');
```

### Data Validation

#### Enable Auto Validation

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * Enable auto-validation
     */
    public $autoValidating = true;

    /**
     * Validation rules
     */
    public $validateRules = [
        'username' => [
            'required' => true,
            'minLength' => 3,
            'maxLength' => 20,
        ],
        'email' => [
            'required' => true,
            'email' => true,
        ],
    ];
}
```

#### Validate Data

```php
$row = [
    'username' => 'john',
    'email' => 'invalid-email',
];

$result = $userTable->create($row);

if (!$result) {
    // Get validation errors
    $errors = $userTable->lastValidationResult;
    print_r($errors);
}
```

### Auto-Fill Time Fields

If data table contains the following fields, current time will be automatically filled:

```php
class Table_Users extends \FLEA\Db\TableDataGateway
{
    public $tableName = 'users';
    public $primaryKey = 'user_id';

    /**
     * Fields to auto-fill on record creation
     */
    public $createdTimeFields = ['CREATED', 'CREATED_ON', 'CREATED_AT'];

    /**
     * Fields to auto-fill on record creation and update
     */
    public $updatedTimeFields = ['UPDATED', 'UPDATED_ON', 'UPDATED_AT'];
}
```

Usage example:

```php
// On create, CREATED field is auto-filled
$row = ['username' => 'john'];
$userTable->create($row);

// On update, UPDATED field is auto-filled
$row['email'] = 'newemail@example.com';
$userTable->update($row);
```

---

## MVC Pattern

### Running MVC Application

Use `FLEA::runMVC()` to start MVC application:

```php
require('vendor/autoload.php');
FLEA::loadAppInf('config.php');

// Run MVC application
FLEA::runMVC();
```

### Controllers

Controller classes should extend `\FLEA\Controller\Action`:

```php
class Controller_Index extends \FLEA\Controller\Action
{
    public function actionIndex()
    {
        echo 'Hello, World!';
    }

    public function actionLogin()
    {
        // Handle login logic
    }
}
```

### URL Routing

Framework supports three URL modes:

#### 1. Standard URL Mode (URL_STANDARD)

```
http://example.com/index.php?controller=Index&action=login
```

#### 2. PATHINFO Mode (URL_PATHINFO)

```
http://example.com/index.php/Index/login
```

#### 3. URL Rewrite Mode (URL_REWRITE)

Requires web server URL rewrite rules:

```
http://example.com/Index/login
```

### Initializing Environment

Use `FLEA::init()` to initialize runtime environment:

```php
FLEA::init();

// Or
FLEA::init(true); // Also load MVC-related files
```

Initialization process includes:
- Set timezone
- Install exception handler
- Load logging service
- Set cache directory
- Load URL analysis filters
- Load requestFilters
- Load autoLoad files
- Load session service providers
- Start session
- Set response character set
- Load multi-language support

---

## Cache Management

### Write Cache

Use `FLEA::writeCache()` to write cache:

```php
$data = ['name' => 'John', 'age' => 30];
$cacheId = 'user_info_' . $userId;

FLEA::writeCache($cacheId, $data);
```

### Read Cache

Use `FLEA::getCache()` to read cache:

```php
$cacheId = 'user_info_' . $userId;

// Default cache time 900 seconds (15 minutes)
$data = FLEA::getCache($cacheId);

if ($data === false) {
    // Cache not found or expired
    $data = fetchDataFromDatabase();
    FLEA::writeCache($cacheId, $data);
}
```

Specify cache lifetime:

```php
// Cache time 3600 seconds (1 hour)
$data = FLEA::getCache($cacheId, 3600);

// Cache never expires
$data = FLEA::getCache($cacheId, -1);
```

### Delete Cache

Use `FLEA::purgeCache()` to delete cache:

```php
$cacheId = 'user_info_' . $userId;
FLEA::purgeCache($cacheId);
```

### Cache Configuration

Set cache directory in configuration file:

```php
return [
    'internalCacheDir' => dirname(__FILE__) . '/Cache',
];
```

If cache directory is not set, caching features will be unavailable.

---

## RBAC Permission Control

FleaPHP provides complete RBAC (Role-Based Access Control) support through the `\FLEA\Rbac\Rbac` class for permission checking functionality.

### RBAC Constants

Framework predefined several RBAC-related constants:

| Constant | Description |
|-----------|-------------|
| `RBAC_EVERYONE` | Any user (regardless of whether the user has role information) |
| `RBAC_HAS_ROLE` | Users with any role |
| `RBAC_NO_ROLE` | Users without any role |
| `RBAC_NULL` | This setting has no value |
| `ACTION_ALL` | All actions in a controller |

### Initializing RBAC

Create RBAC instance:

```php
$rbac = new \FLEA\Rbac\Rbac();
```

Or use in controller:

```php
$rbac = FLEA::getSingleton(\FLEA\Rbac\Rbac::class);
```

### Configuring RBAC

Set RBAC-related options in configuration file:

```php
return [
    // RBAC session key
    'RBACSessionKey' => 'MY_APP_RBAC_USER',
];
```

### User Management

#### Set User Information

Use `setUser()` method to save user information to session:

```php
$rbac = new \FLEA\Rbac\Rbac();

// Set user information
$userData = [
    'user_id' => 1,
    'username' => 'john',
    'email' => 'john@example.com',
];

// Set role data
$rolesData = ['ADMIN', 'EDITOR'];

$rbac->setUser($userData, $rolesData);
```

Set user information without roles:

```php
$rbac->setUser($userData);
```

#### Get User Information

Use `getUser()` method to get user information from session:

```php
$user = $rbac->getUser();

if ($user) {
    echo "Current user: " . $user['username'];
}
```

#### Get User Roles

Use `getRoles()` method to get user roles:

```php
$roles = $rbac->getRoles();

// Returns possibly a string like "ADMIN,EDITOR"
// Or an array
```

Use `getRolesArray()` method to ensure array return:

```php
$roles = $rbac->getRolesArray();

print_r($roles);
// Output: Array ( [0] => ADMIN [1] => EDITOR )
```

#### Clear User Information

Use `clearUser()` method to clear user information from session (usually for logout):

```php
$rbac->clearUser();

// User logged out
```

### Permission Checking

#### Access Control Table (ACT)

ACT (Access Control Table) is an array containing the following keys:

- `allow`: List of allowed roles or special constants
- `deny`: List of denied roles or special constants

ACT Examples:

```php
// Allow all users
$ACT = [
    'allow' => RBAC_EVERYONE,
    'deny' => RBAC_NULL,
];

// Only allow administrators
$ACT = [
    'allow' => ['ADMIN'],
    'deny' => RBAC_NULL,
];

// Allow administrators and editors, but deny regular users
$ACT = [
    'allow' => ['ADMIN', 'EDITOR'],
    'deny' => ['USER'],
];

// Require users to have a role
$ACT = [
    'allow' => RBAC_HAS_ROLE,
    'deny' => RBAC_NULL,
];

// Require users to not have any role
$ACT = [
    'allow' => RBAC_NO_ROLE,
    'deny' => RBAC_NULL,
];
```

#### Check Permission

Use `check()` method to check access permissions:

```php
$rbac = new \FLEA\Rbac\Rbac();

// Set user and roles
$userData = ['user_id' => 1, 'username' => 'john'];
$rolesData = ['ADMIN', 'EDITOR'];

$rbac->setUser($userData, $rolesData);

// Define access control table
$ACT = [
    'allow' => ['ADMIN', 'EDITOR'],
    'deny' => RBAC_NULL,
];

// Check permission
$roles = $rbac->getRolesArray();
if ($rbac->check($roles, $ACT)) {
    echo "Access granted";
} else {
    echo "Access denied";
}
```

#### Prepare ACT

Use `prepareACT()` method to analyze and organize raw ACT:

```php
// Raw ACT (may contain string format role lists)
$rawACT = [
    'allow' => 'ADMIN,EDITOR',
    'deny' => 'USER,BLOCKED',
];

// Prepare ACT
$ACT = $rbac->prepareACT($rawACT);

// Output prepared ACT
print_r($ACT);
// Output:
// Array (
//     [allow] => Array ( [0] => ADMIN [1] => EDITOR )
//     [deny] => Array ( [0] => USER [1] => BLOCKED )
// )
```

### Permission Checking Examples

#### Example 1: Simple Role Check

```php
$ACT = [
    'allow' => ['ADMIN'],
    'deny' => RBAC_NULL,
];

// Check if user is administrator
if ($rbac->check($roles, $ACT)) {
    // User is administrator
    // Execute admin operations
}
```

#### Example 2: Multi-Role Support

```php
$ACT = [
    'allow' => ['ADMIN', 'EDITOR', 'MODERATOR'],
    'deny' => RBAC_NULL,
];

// User has ADMIN, EDITOR, MODERATOR role and any one is sufficient for access
if ($rbac->check($roles, $ACT)) {
    // User has permission
}
```

#### Example 3: Deny Specific Roles

```php
$ACT = [
    'allow' => RBAC_EVERYONE,
    'deny' => ['BLOCKED', 'BANNED'],
];

// All users can access except blocked and banned users
if ($rbac->check($roles, $ACT)) {
    // User not blocked or banned
}
```

#### Example 4: Require Role

```php
$ACT = [
    'allow' => RBAC_HAS_ROLE,
    'deny' => RBAC_NULL,
];

// User must have at least one role to access
if ($rbac->check($roles, $ACT)) {
    // User has role
}
```

#### Example 5: Require No Role

```php
$ACT = [
    'allow' => RBAC_NO_ROLE,
    'deny' => RBAC_NULL,
];

// User must not have any role to access
if ($rbac->check($roles, $ACT)) {
    // User has no role
}
```

### Using RBAC in Controllers

#### Login with Setting User

```php
class Controller_Login extends \FLEA\Controller\Action
{
    public function actionLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Verify user
        $userTable = FLEA::getSingleton('Table_Users');
        $user = $userTable->findByField('username', $username);

        if ($user && $user['password'] === md5($password)) {
            // Get user roles
            $roleTable = FLEA::getSingleton('Table_UserRoles');
            $roles = $roleTable->getUserRoles($user['user_id']);

            // Set user and roles
            $rbac = new \FLEA\Rbac\Rbac();
            $rbac->setUser($user, $roles);

            // Redirect to home page
            redirect(url('Index', 'index'));
        } else {
            echo "Username or password incorrect";
        }
    }

    public function actionLogout()
    {
        $rbac = new \FLEA\Rbac\Rbac();
        $rbac->clearUser();

        redirect(url('Login', 'index'));
    }
}
```

#### Check Permissions in Controller

```php
class Controller_Admin extends \FLEA\Controller\Action
{
    public function actionIndex()
    {
        $rbac = new \FLEA\Rbac\Rbac();
        $roles = $rbac->getRolesArray();

        // Define access control table
        $ACT = [
            'allow' => ['ADMIN'],
            'deny' => RBAC_NULL,
        ];

        // Check permissions
        if (!$rbac->check($roles, $ACT)) {
            echo "Access denied";
            return;
        }

        // Execute admin operations
        echo "Welcome, administrator";
    }
}
```

#### Using RBAC Middleware

Create RBAC check middleware:

```php
function checkPermission($requiredRoles)
{
    $rbac = new \FLEA\Rbac\Rbac();
    $roles = $rbac->getRolesArray();

    $ACT = [
        'allow' => $requiredRoles,
        'deny' => RBAC_NULL,
    ];

    if (!$rbac->check($roles, $ACT)) {
        js_alert('Access denied', '', url('Index', 'index'));
        exit;
    }
}
```

Use middleware in controllers:

```php
class Controller_Admin extends \FLEA\Controller\Action
{
    public function actionIndex()
    {
        // Check admin permissions
        checkPermission(['ADMIN']);

        // Execute operations
    }

    public function actionEdit()
    {
        // Check editor permissions
        checkPermission(['ADMIN', 'EDITOR']);

        // Execute operations
    }
}
```

### RBAC Best Practices

1. **Centralize ACT Management**: Define access control tables in configuration files or constants for easier maintenance
2. **Role Naming Convention**: Use uppercase letters for easy identification
3. **Permission Inheritance**: Combine multiple roles for more complex permission control
4. **Logging**: Record permission check failures for auditing purposes

```php
// config/permissions.php
return [
    'ACT_ADMIN' => [
        'allow' => ['ADMIN'],
        'deny' => RBAC_NULL,
    ],
    'ACT_EDITOR' => [
        'allow' => ['ADMIN', 'EDITOR'],
        'deny' => RBAC_NULL,
    ],
];
```

---

## Exception Handling

### Framework Exceptions

FleaPHP provides multiple exception classes, all extending from `\FLEA\Exception`:

- `\FLEA\Exception\ExpectedFile` - File not found
- `\FLEA\Exception\ExpectedClass` - Class not found
- `\FLEA\Exception\TypeMismatch` - Type mismatch
- `\FLEA\Exception\ExistsKeyName` - Object name already exists
- `\FLEA\Exception\NotExistsKeyName` - Object name not found
- `\FLEA\Exception\CacheDisabled` - Cache functionality not enabled
- `\FLEA\Exception\MissingController` - Controller not found
- `\FLEA\Exception\MissingAction` - Action not found
- `\FLEA\Db\Exception\InvalidDSN` - Invalid DSN

#### Using Exceptions

```php
try {
    $userTable = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);
    $user = $userTable->find(1);
} catch (\FLEA\Exception\ExpectedClass $e) {
    echo 'Class not found: ' . $e->getMessage();
} catch (\FLEA\Db\Exception\InvalidDSN $e) {
    echo 'Database connection failed: ' . $e->getMessage();
}
```

#### Setting Exception Handler

```php
set_exception_handler(FLEA::getAppInf('exceptionHandler'));
```

---

## Helper Functions

### Loading Helpers

Use `FLEA::loadHelper()` to load helpers:

```php
// Load helpers
FLEA::loadHelper('array');
FLEA::loadHelper('image');

// Use helper classes (through Composer autoloading)
use FLEA\Helper\Array;
$arrayHelper = new Array();
```

Helper configuration in application configuration, prefixed with `helper.`:

```php
return [
    'helper.array' => 'FLEA\Helper\Array',
    'helper.image' => 'FLEA\Helper\Image',
    // ...
];
```

**Note**: FleaPHP's global helper functions (like `dump()`, `redirect()`, `url()`, etc.) have been consolidated in `FLEA/Functions.php` file and are loaded through Composer autoloading, without requiring manual loading.

### Initialize WebControls

Use `FLEA::initWebControls()` to initialize WebControls:

```php
$webControls = FLEA::initWebControls();
```

Customize WebControls class:

```php
return [
    'webControlsClassName' => 'MyApp\Controls\WebControls',
];
```

### Initialize Ajax

Use `FLEA::initAjax()` to initialize Ajax:

```php
$ajax = FLEA::initAjax();
```

Customize Ajax class:

```php
return [
    'ajaxClassName' => 'MyApp\Ajax\Ajax',
];
```

---

## URL Generation

### Generate URLs

Use `url()` function to generate URLs:

```php
// Generate standard URL
$url = url('Index', 'login');
// Output: ?controller=Index&action=login

// With parameters
$url = url('Article', 'view', ['id' => 1]);
// Output: ?controller=Article&action=view&id=1

// With anchor
$url = url('Article', 'view', ['id' => 1], '#comments');
// Output: ?controller=Article&action=view&id=1#comments

// Use default controller and action
$url = url();
// Output: ?controller=Index&action=index (uses default values)
```

### URL Modes

Different URL formats are generated based on `urlMode` configuration:

**Standard Mode:**

```php
$url = url('User', 'profile', ['id' => 1]);
// Output: /index.php?controller=User&action=profile&id=1
```

**PATHINFO Mode:**

```php
$url = url('User', 'profile', ['id' => 1]);
// Output: /index.php/User/profile/id/1
```

**URL Rewrite Mode:**

```php
$url = url('User', 'profile', ['id' => 1]);
// Output: /User/profile/id/1
```

### URL Options

```php
$url = url('User', 'profile', ['id' => 1], null, [
    'mode' => URL_REWRITE,        // Specify URL mode
    'lowerChar' => true,          // Convert to lowercase
    'bootstrap' => 'admin.php',   // Specify entry file
    'parameterPairStyle' => '-',  // Parameter separator
]);
```

### URL Callback

Configure URL generation callback in configuration:

```php
return [
    'urlCallback' => function(&$controller, &$action, &$params, &$anchor, &$options) {
        // Modify URL generation parameters
        $controller = strtolower($controller);
        $action = strtolower($action);
    },
];
```

---

## Best Practices

### 1. Configuration Management

- Store sensitive information (like database passwords) in separate configuration files
- Use environment variables to override configuration items for different environment deployments
- Enable debug mode in development environment, disable in production environment

### 2. Class File Organization

- Use PSR-4 namespace convention to organize class files
- Namespace corresponds to directory structure
- Configure PSR-4 autoloading rules in `composer.json`
- All classes are autoloaded through Composer, no manual search path management needed

**Example Directory Structure:**

```
your-project/
├── vendor/
│   └── composer/
│       └── autoload_*.php
├── src/
│   ├── Controller/
│   │   └── Index.php
│   ├── Model/
│   │   └── User.php
│   └── View/
├── composer.json
└── index.php
```

**composer.json Configuration:**

```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/"
        }
    }
}
```

### 3. Object Management

- Use `FLEA::getSingleton()` for frequently used objects
- Register service classes to object container at application startup
- Avoid creating unnecessary objects in loops
- Use fully qualified namespaces or `use` statements to reference classes

```php
// Use fully qualified namespace
$userTable = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);

// Or use use statement
use FLEA\Db\TableDataGateway;
$userTable = FLEA::getSingleton(TableDataGateway::class);
```

### 4. Database Operations

- Reasonably use database connection pool, avoid duplicate connections
- Use table prefixes to avoid table name conflicts
- Use DSN string or array format to specify database connection information

### 5. Cache Usage

- Use caching for frequently accessed but infrequently changing data
- Set reasonable cache expiration times
- Clean up unused caches promptly

### 6. Exception Handling

- Use framework-provided exception classes
- Set custom exception handler
- Use try-catch blocks for special exception handling

### 7. URL Generation

- Always use `url()` function to generate URLs, never hardcode
- Configure appropriate URL mode for your project
- Use URL options to customize URL generation behavior

### 8. Performance Optimization

- Reasonably configure class search paths, avoid unnecessary file system searches
- Use caching to reduce database queries
- Disable debug mode in production environment for better performance

---

## Common Questions

### Q: How to switch between debug and production modes?

**A:** Define `DEPLOY_MODE` constant as true to enable production mode:

```php
define('DEPLOY_MODE', true);
require('vendor/autoload.php');
```

### Q: How to customize class file search paths?

**A:** Use Composer's PSR-4 autoloading configuration, no manual search path configuration needed:

```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/",
            "MyApp\\Lib\\": "lib/"
        }
    }
}
```

Then run:

```bash
composer dump-autoload
```

### Q: How to handle database connection failures?

**A:** Use try-catch to catch exceptions:

```php
try {
    $dbo = FLEA::getDBO();
} catch (\FLEA\Db\Exception\InvalidDSN $e) {
    echo 'Database connection failed: ' . $e->getMessage();
}
```

### Q: How to clear all caches?

**A:** Delete all files in the cache directory:

```php
$cacheDir = FLEA::getAppInf('internalCacheDir');
$files = glob($cacheDir . '/*.php');
foreach ($files as $file) {
    unlink($file);
}
```

### Q: How to reset the object container?

**A:** There's no direct way to reset the object container. You need to reload the framework.

---

## Appendix

### Configuration Item Reference

| Configuration Item | Description | Default Value |
|-------------------|-------------|----------------|
| `dbDSN` | Database connection information | null |
| `dbTablePrefix` | Database table prefix | '' |
| `urlMode` | URL mode | URL_STANDARD |
| `urlLowerChar` | Convert URL to lowercase | false |
| `defaultController` | Default controller | 'Index' |
| `defaultAction` | Default action | 'index' |
| `urlBootstrap` | Bootstrap script name | 'index.php' |
| `urlLowerChar` | Lowercase URL character mode | false |
| `parameterPairStyle` | URL parameter separator | '&' |
| `internalCacheDir` | Internal cache directory | './cache' |
| `databaseCharset` | Database charset | 'UTF-8' |
| `responseCharset` | Response charset | 'UTF-8' |
| `defaultLanguage` | Default language | 'chinese-utf8' |
| `dispatcher` | Dispatcher class name | `\FLEA\Dispatcher\Simple::class` |
| `view` | View engine class | `PHP` |
| `viewConfig.templateDir` | Template directory | `__DIR__ . '/View'` |
| `viewConfig.cacheDir` | Cache directory | `__DIR__ . '/../cache'` |
| `viewConfig.cacheLifeTime` | Cache lifetime in seconds | 900 |
| `viewConfig.enableCache` | Enable cache | false |

### Table Relationship Types

| Relationship Type | Constant | Description |
|----------------|----------|-------------|
| `HAS_ONE` | One record has another associated record |
| `HAS_MANY` | One record has multiple associated records |
| `BELONGS_TO` | One record belongs to another record |
| `MANY_TO_MANY` | Two tables reference each other |

### Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `FLEA_VERSION` | FleaPHP version number |
| `PHP5` | PHP version identifier (true) |
| `PHP4` | PHP version identifier (false) |
| `DS` | Directory separator shortcut |
| `FLEA_DIR` | FLEA framework directory |
| `DEBUG_MODE` | Debug mode indicator |
| `URL_STANDARD` | Standard URL mode |
| `URL_PATHINFO` | PATHINFO mode |
| `URL_REWRITE` | URL rewrite mode |
| `RBAC_EVERYONE` | Access for all users |
| `RBAC_HAS_ROLE` | Users with roles |
| `RBAC_NO_ROLE` | Users without roles |
| `RBAC_NULL` | No specific requirements |
| `ACTION_ALL` | All controller actions |

---

## Version History

### 2026-02-25 - Framework improvements

#### SqlStatement Class Introduction

Introduced `\FLEA\Db\SqlStatement` class to unify SQL handling across string and PDOStatement types.

#### Sqlitepdo.php Bug Fixes

Fixed errors in SQLitePDO driver including:
- `failTrans()` logic error (changed `_transCommit` from true to false)
- Array destructuring (changed `list()` to `[$length, $offset]`)
- `connect()` return value (changed from object to boolean)
- `affectedRows()` method and comment corrections

#### Simple View Engine Optimization

Renamed `$path` to `$templateDir` in `FLEA\View\Simple`:
- Made variable names consistent with configuration keys
- Simplified constructor by removing special case for `templateDir`
- Simplified template loading logic

---

## License

MIT License

## Authors

FLEA Framework Team
