<?php
// Consolidation/db.php
// Database connection helper

function getPDOConnection(): PDO {
    $host   = getenv('DB_HOST')   ?: '127.0.0.1';
    $port   = getenv('DB_PORT')   ?: '1433';
    $dbname = getenv('DB_NAME')   ?: 'FinanceDB';
    $user   = getenv('DB_USER')   ?: '';
    $pass   = getenv('DB_PASS')   ?: '';

    $dsn = "sqlsrv:Server={$host},{$port};Database={$dbname};TrustServerCertificate=1";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
