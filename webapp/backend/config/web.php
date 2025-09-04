<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'faithbit-ssms',
    'name' => 'FaithBit SSMS',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'language' => 'en-US',
    'timeZone' => 'Africa/Dar_es_Salaam',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'faithbit-ssms-validation-key-replace-with-random',
            'enableCookieValidation' => true,
            'enableCsrfValidation' => false, // Disabled for API
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $response->headers->add('Access-Control-Allow-Origin', '*');
                $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->add('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            },
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => $_ENV['REDIS_HOST'] ?? 'localhost',
                'port' => 6379,
                'database' => 0,
            ]
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'faithbit_queue',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // Authentication
                'POST auth/login' => 'auth/login',
                'POST auth/refresh' => 'auth/refresh',
                'POST auth/logout' => 'auth/logout',
                
                // Products
                'GET products' => 'product/index',
                'GET products/<id:\d+>' => 'product/view',
                'POST products' => 'product/create',
                'PUT products/<id:\d+>' => 'product/update',
                'DELETE products/<id:\d+>' => 'product/delete',
                
                // Customers
                'GET customers' => 'customer/index',
                'GET customers/<id:\d+>' => 'customer/view',
                'POST customers' => 'customer/create',
                'PUT customers/<id:\d+>' => 'customer/update',
                
                // Sales
                'GET sales/quotes' => 'sales/quote-index',
                'POST sales/quotes' => 'sales/quote-create',
                'PUT sales/quotes/<id:\d+>' => 'sales/quote-update',
                'GET sales/orders' => 'sales/order-index',
                'POST sales/orders' => 'sales/order-create',
                'PUT sales/orders/<id:\d+>' => 'sales/order-update',
                
                // POS
                'POST pos/sale' => 'pos/create-sale',
                'GET pos/products' => 'pos/search-products',
                'POST pos/payment' => 'pos/process-payment',
                
                // Service Management
                'GET service/work-orders' => 'service/work-order-index',
                'POST service/work-orders' => 'service/work-order-create',
                'PUT service/work-orders/<id:\d+>' => 'service/work-order-update',
                'GET service/technicians/<id:\d+>/schedule' => 'service/technician-schedule',
                
                // Inventory
                'GET inventory/stock' => 'inventory/stock-levels',
                'POST inventory/transfer' => 'inventory/create-transfer',
                'POST inventory/adjustment' => 'inventory/stock-adjustment',
                
                // Procurement
                'GET procurement/purchase-orders' => 'procurement/po-index',
                'POST procurement/purchase-orders' => 'procurement/po-create',
                'PUT procurement/purchase-orders/<id:\d+>' => 'procurement/po-update',
                
                // Reports
                'GET reports/sales' => 'report/sales',
                'GET reports/inventory' => 'report/inventory',
                'GET reports/service' => 'report/service',
                'GET dashboard/stats' => 'dashboard/stats',
                
                // Payments
                'POST payments/mobile-money' => 'payment/mobile-money',
                'POST payments/webhook/<provider>' => 'payment/webhook',
                
                // Default patterns
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '172.*.*.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '172.*.*.*'],
    ];
}

return $config;