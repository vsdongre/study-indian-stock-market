<?php
// ── Database Configuration ──────────────────────────────────────────────────
// Adjust the constants below to match your MySQL / MariaDB setup.
// ─────────────────────────────────────────────────────────────────────────────

define('DB_HOST',   'localhost');
define('DB_PORT',   '3306');
define('DB_NAME',   'stationery_db');   // <-- your database name
define('DB_USER',   'root');            // <-- your DB username
define('DB_PASS',   '');               // <-- your DB password
define('DB_CHARSET','utf8mb4');

// ── PDO Connection ────────────────────────────────────────────────────────────
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log the error instead of displaying it.
            die('<p style="color:red;font-family:Tahoma,sans-serif;">'
                . 'Database connection failed: '
                . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                . '</p>');
        }
    }

    return $pdo;
}
