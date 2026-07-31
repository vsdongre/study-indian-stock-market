<?php
// ── Database Configuration — Microsoft SQL Server ─────────────────────────────
//
//  Driver  : PDO_SQLSRV  (Microsoft Drivers for PHP for SQL Server)
//            https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
//
//  DB_HOST : IP address or hostname of the SQL Server on the LAN, e.g.
//              'localhost'               — server and PHP on the same machine
//              '192.168.1.10'            — static IP on local network
//              'localhost\SQLEXPRESS'    — named instance (no port needed)
//              '192.168.1.10\SQLEXPRESS' — named instance on static IP
//
//  DB_PORT : Set this to the custom TCP port configured in SQL Server
//            Configuration Manager (e.g. '2433', '1435', etc.).
//            Leave it EMPTY ('') to let SQL Server use the named instance
//            dynamic port — the SQL Server Browser service will resolve it.
//
//  Windows Authentication (trusted connection):
//    Set DB_WIN_AUTH to true and leave DB_USER / DB_PASS blank.
//
//  SQL Server Authentication:
//    Set DB_WIN_AUTH to false and fill in DB_USER / DB_PASS.
// ─────────────────────────────────────────────────────────────────────────────

define('DB_HOST',     'localhost');          // <-- SQL Server IP or hostname
define('DB_PORT',     '');                   // <-- your custom port, e.g. '2433' (leave '' for named instance)
define('DB_NAME',     'StationeryDB');       // <-- your database name
define('DB_USER',     'sa');                 // <-- SQL Server login (SQL Auth)
define('DB_PASS',     '');                   // <-- SQL Server password (SQL Auth)
define('DB_WIN_AUTH', false);                // <-- true = Windows Auth, false = SQL Auth

// ── PDO_SQLSRV Connection ─────────────────────────────────────────────────────
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // If a custom port is set, append it with a comma as SQL Server requires.
        // e.g.  sqlsrv:Server=localhost,2433;Database=StationeryDB;...
        // If no port is set, omit it — named instances resolve via SQL Server Browser.
        $serverPart = (DB_PORT !== '')
            ? DB_HOST . ',' . DB_PORT
            : DB_HOST;

        $dsn = sprintf(
            'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=1',
            $serverPart,
            DB_NAME
        );

        if (DB_WIN_AUTH) {
            $dsn .= ';Trusted_Connection=yes';
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // NOTE: PDO_SQLSRV does not support ATTR_EMULATE_PREPARES=false;
            //       native prepared statements are always used.
        ];

        // Windows Authentication passes no credentials to PDO constructor.
        $user = DB_WIN_AUTH ? null : DB_USER;
        $pass = DB_WIN_AUTH ? null : DB_PASS;

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
