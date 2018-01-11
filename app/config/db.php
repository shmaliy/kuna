<?php
require_once '_local.config.php';
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='. MYSQL_HOST .':' . MYSQL_PORT . ';dbname=' . MYSQL_DB_NAME,
    'username' => MYSQL_USER,
    'password' => MYSQL_PASSWORD,
    'charset' => 'utf8mb4',
    'tablePrefix' => 'cr_'

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
