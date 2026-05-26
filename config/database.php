<?php
/**
 * Database Configuration
 * Trong production, load từ .env file (sử dụng vlucas/phpdotenv hoặc tương đương).
 */
return [
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'port'     => (int)($_ENV['DB_PORT'] ?? 3306),
    'dbname'   => $_ENV['DB_NAME']     ?? 'obe_portfolio',
    'username' => $_ENV['DB_USER']     ?? 'root',
    'password' => $_ENV['DB_PASS']     ?? '',
    'charset'  => 'utf8mb4',
];
