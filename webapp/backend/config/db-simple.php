<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . __DIR__ . '/../database/faithbit_ssms.db',
    'charset' => 'utf8',
    
    // Enable query cache
    'enableQueryCache' => true,
    'queryCacheDuration' => 300,
    
    // Connection options
    'attributes' => [
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];