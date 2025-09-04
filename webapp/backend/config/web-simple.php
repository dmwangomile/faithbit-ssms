<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db-simple.php';

$config = [
    'id' => 'faithbit-ssms',
    'name' => 'FaithBit SSMS',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
            'class' => 'yii\caching\FileCache',
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                // API routes
                'POST api/auth/login' => 'auth/login',
                'POST api/auth/refresh' => 'auth/refresh',
                'POST api/auth/logout' => 'auth/logout',
                
                'GET api/products' => 'product/index',
                'GET api/products/<id:\d+>' => 'product/view',
                'POST api/products' => 'product/create',
                'PUT api/products/<id:\d+>' => 'product/update',
                'DELETE api/products/<id:\d+>' => 'product/delete',
                'GET api/products/search' => 'product/search',
                'GET api/products/by-barcode' => 'product/by-barcode',
                'GET api/products/low-stock' => 'product/low-stock',
                
                'GET api/customers' => 'customer/index',
                'GET api/customers/<id:\d+>' => 'customer/view',
                'POST api/customers' => 'customer/create',
                'PUT api/customers/<id:\d+>' => 'customer/update',
                
                // Health check
                'GET api/health' => 'site/health',
                
                // Default patterns
                'api/<controller:\w+>/<id:\d+>' => '<controller>/view',
                'api/<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                'api/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
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