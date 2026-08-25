<?php

$host = '10.101.8.42';
$port = '5432';
$dbname = 'test_demekhin';
$user = 'adm_demekhin';
$password = 'T421w$gwU3'; // Вставьте правильный пароль!

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to PostgreSQL successfully!\n";

    // Получаем список таблиц
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_type = 'BASE TABLE'
        AND table_name NOT IN ('migrations', 'failed_jobs', 'password_reset_tokens')
    ");

    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables:\n";
}