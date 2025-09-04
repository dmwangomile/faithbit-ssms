<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_NAME'] ?? 'faithbit_ssms'
    ),
    'username' => $_ENV['DB_USER'] ?? 'faithbit_user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'faithbit_pass',
    'charset' => 'utf8mb4',
    
    // Schema cache options (for production)
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
    
    // Enable query cache
    'enableQueryCache' => true,
    'queryCacheDuration' => 300,
    'queryCache' => 'cache',
    
    // Connection options
    'attributes' => [
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'",
    ],
];