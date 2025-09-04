<?php

return [
    'adminEmail' => 'admin@faithbit.com',
    'senderEmail' => 'noreply@faithbit.com',
    'senderName' => 'FaithBit SSMS',
    
    // Application settings
    'app' => [
        'name' => 'FaithBit SSMS',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => $_ENV['APP_DEBUG'] ?? true,
    ],
    
    // JWT settings
    'jwt' => [
        'key' => $_ENV['JWT_SECRET'] ?? 'faithbit-jwt-secret-key-replace-with-random',
        'algorithm' => 'HS256',
        'expiration' => 3600 * 24, // 24 hours
        'refresh_expiration' => 3600 * 24 * 30, // 30 days
    ],
    
    // File upload settings
    'upload' => [
        'maxFileSize' => 10 * 1024 * 1024, // 10MB
        'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'path' => '@webroot/uploads',
        'url' => '@web/uploads',
    ],
    
    // MinIO/S3 settings
    'storage' => [
        'endpoint' => $_ENV['MINIO_ENDPOINT'] ?? 'localhost:9000',
        'accessKey' => $_ENV['MINIO_ACCESS_KEY'] ?? 'faithbit_access',
        'secretKey' => $_ENV['MINIO_SECRET_KEY'] ?? 'faithbit_secret',
        'bucket' => 'faithbit-ssms',
        'region' => 'us-east-1',
        'useSSL' => false,
    ],
    
    // Mobile money settings
    'mobileMoney' => [
        'selcom' => [
            'vendor' => $_ENV['SELCOM_VENDOR'] ?? '',
            'apiKey' => $_ENV['SELCOM_API_KEY'] ?? '',
            'secretKey' => $_ENV['SELCOM_SECRET_KEY'] ?? '',
            'baseUrl' => $_ENV['SELCOM_BASE_URL'] ?? 'https://apigw.selcommobile.com',
        ],
        'maximalipo' => [
            'username' => $_ENV['MAXIMALIPO_USERNAME'] ?? '',
            'password' => $_ENV['MAXIMALIPO_PASSWORD'] ?? '',
            'baseUrl' => $_ENV['MAXIMALIPO_BASE_URL'] ?? 'https://api.maximalipo.co.tz',
        ],
    ],
    
    // SMS/WhatsApp settings
    'messaging' => [
        'sms' => [
            'provider' => 'beem',
            'apiKey' => $_ENV['BEEM_API_KEY'] ?? '',
            'secretKey' => $_ENV['BEEM_SECRET_KEY'] ?? '',
            'senderId' => 'FAITHBIT',
        ],
        'whatsapp' => [
            'token' => $_ENV['WHATSAPP_TOKEN'] ?? '',
            'phoneNumberId' => $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '',
            'webhookVerifyToken' => $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? '',
        ],
    ],
    
    // Accounting integration
    'accounting' => [
        'quickbooks' => [
            'clientId' => $_ENV['QUICKBOOKS_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['QUICKBOOKS_CLIENT_SECRET'] ?? '',
            'redirectUri' => $_ENV['QUICKBOOKS_REDIRECT_URI'] ?? '',
            'sandbox' => $_ENV['QUICKBOOKS_SANDBOX'] ?? true,
        ],
    ],
    
    // Business settings
    'business' => [
        'defaultCurrency' => 'TZS',
        'defaultTimezone' => 'Africa/Dar_es_Salaam',
        'defaultLanguage' => 'en-US',
        'supportedLanguages' => ['en-US', 'sw-TZ'],
        'taxRate' => 18.0, // 18% VAT
        'fiscalYearStart' => '07-01', // July 1st
    ],
    
    // POS settings
    'pos' => [
        'receiptFooter' => 'Thank you for shopping with FaithBit!\nBuy Tech. Build Hope.',
        'allowOfflineMode' => true,
        'offlineSyncInterval' => 300, // 5 minutes
        'maxOfflineTransactions' => 100,
    ],
    
    // Service management settings
    'service' => [
        'defaultSla' => [
            'warranty_repair' => 48, // hours
            'paid_repair' => 72, // hours
            'consultation' => 24, // hours
        ],
        'workingHours' => [
            'start' => '08:00',
            'end' => '18:00',
            'workingDays' => [1, 2, 3, 4, 5, 6], // Monday to Saturday
        ],
    ],
    
    // Inventory settings
    'inventory' => [
        'defaultReorderLevel' => 10,
        'defaultReorderQuantity' => 50,
        'lowStockThreshold' => 5,
        'expiryWarningDays' => 30,
    ],
];