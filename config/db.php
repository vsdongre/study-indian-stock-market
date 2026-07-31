<?php
// ── Database Configuration — Microsoft SQL Server ─────────────────────────────
//
//  Driver  : PDO_SQLSRV  (Microsoft Drivers for PHP for SQL Server)
//            https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
//
//  DB_HOST : IP address or hostname of the SQL Server on the LAN, e.g.
//              '192.168.1.10'           — static IP on local network
//              '192.168.1.10\SQLEXPRESS'— named instance on static IP
//              'localhost'              — server and PHP on the same machine
//            For a non-default port append a comma: '192.168.1.10,1433'
//
//  Windows Authentication (trusted connection):
//    Set DB_WIN_AUTH to true and leave DB_USER / DB_PASS blank.
//    The PHP process must run under a Windows account that has SQL Server access.
//
//  SQL Server Authentication:
//    Set DB_WIN_AUTH to false and fill in DB_USER / DB_PASS.
// ─────────────────────────────────────────────────────────────────────────────

define('DB_HOST',     'localhost');          // <-- SQL Server IP or hostname
define('DB_PORT',     '1433');               // <-- SQL Server port (default 1433)
define('DB_NAME',     'StationeryDB');       // <-- your database name
define('DB_USER',     'sa');                 // <-- SQL Server login (SQL Auth)
define('DB_PASS',     '');                   // <-- SQL Server password (SQL Auth)
define('DB_WIN_AUTH', false);                // <-- true = Windows Auth, false = SQL Auth

// ── PDO_SQLSRV Connection ─────────────────────────────────────────────────────
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // Build DSN
        // Format:  sqlsrv:Server=<host>,<port>;Database=<db>
        $dsn = sprintf(
            'sqlsrv:Server=%s,%s;Database=%s;TrustServerCertificate=1',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // NOTE: PDO_SQLSRV does not support ATTR_EMULATE_PREPARES=false;
            //       native prepared statements are always used.
        ];

        // Windows Authentication passes no credentials to PDO constructor.
        $user = DB_WIN_AUTH ? null : DB_USER;
        $pass = DB_WIN_AUTH ? null : DB_PASS;

        if (DB_WIN_AUTH) {
            // Append Trusted_Connection to DSN for Windows Auth
            $dsn .= ';Trusted_Connection=yes';
        }

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
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
