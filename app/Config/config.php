<?php
/**
 * Configuration principale Klaimy
 */

return [
    'app' => [
        'name' => 'Klaimy',
        'version' => '1.0.0',
        'url' => getenv('APP_URL') ?: 'http://localhost:8080',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => getenv('APP_DEBUG') === 'true',
        'timezone' => 'Africa/Douala',
        'locale' => 'fr',
        'key' => getenv('APP_KEY') ?: 'klaimy-secret-key-change-in-production',
    ],

    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'name' => getenv('DB_NAME') ?: 'klaimy',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    'session' => [
        'lifetime' => 7200, // 2 heures
        'name' => 'klaimy_session',
        'secure' => false,
        'httponly' => true,
    ],

    'security' => [
        'csrf_token_lifetime' => 3600,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'two_factor_expiry' => 600, // 10 minutes
        'two_factor_inactivity_days' => 7,
        'password_min_length' => 8,
    ],

    'mail' => [
        'from_email' => getenv('MAIL_FROM') ?: 'noreply@klaimy.com',
        'from_name' => 'Klaimy',
        'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
        'smtp_port' => getenv('SMTP_PORT') ?: 587,
        'smtp_user' => getenv('SMTP_USER') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'smtp_encryption' => 'tls',
    ],

    'cinetpay' => [
        'api_key' => getenv('CINETPAY_API_KEY') ?: '',
        'site_id' => getenv('CINETPAY_SITE_ID') ?: '',
        'secret_key' => getenv('CINETPAY_SECRET_KEY') ?: '',
        'base_url' => 'https://api-checkout.cinetpay.com/v2/payment',
        'notify_url' => '/webhooks/cinetpay',
        'return_url' => '/payment/return',
    ],

    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'path' => '/public/uploads/',
    ],

    'pagination' => [
        'per_page' => 20,
    ],
];
