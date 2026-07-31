# Stationery Management System

A **PHP + Microsoft SQL Server** web application for managing stationery inventory, built with a classic **VB6 / Windows 98** look-and-feel. Designed to run on a LAN or via static IP.

---

## Project Structure

```
StationeryManagement/
├── index.php               ← Dashboard (homepage)
├── config/
│   └── db.php              ← PDO_SQLSRV database connection
├── includes/
│   ├── header.php          ← Title bar + VB menu + toolbar
│   └── footer.php          ← Status bar + scripts
├── pages/
│   ├── items.php           ← Items / Products master
│   ├── categories.php      ← Categories master
│   ├── suppliers.php       ← Suppliers master
│   ├── departments.php     ← Departments master
│   ├── employees.php       ← Employees master
│   ├── purchase.php        ← Purchase Orders
│   ├── receive.php         ← Goods Receipt
│   ├── issue.php           ← Issue to Department
│   ├── return.php          ← Returns / Rejections
│   ├── stock.php           ← Current Stock
│   ├── reports.php         ← Reports
│   ├── users.php           ← User Management
│   ├── settings.php        ← System Settings
│   ├── help.php
│   └── about.php
├── assets/
│   ├── css/style.css       ← VB classic theme
│   └── js/script.js        ← Menu behaviour, clock, drag
└── README.md
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 7.4 or higher (8.x recommended) |
| Microsoft SQL Server | 2016 or higher (Express edition is fine) |
| PHP Driver | [Microsoft Drivers for PHP for SQL Server](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server) (`php_pdo_sqlsrv_xx_ts.dll`) |
| Web server | IIS with PHP, or Apache/XAMPP on Windows |
| ODBC Driver | [ODBC Driver 17 or 18 for SQL Server](https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server) |

---

## Setup (Windows)

### 1 — Install the PHP SQL Server driver

1. Download the correct `php_pdo_sqlsrv_xx_ts.dll` for your PHP version from the link above.
2. Copy the `.dll` file to your PHP `ext/` folder (e.g. `C:\xampp\php\ext\`).
3. Add to `php.ini`:
   ```ini
   extension=php_pdo_sqlsrv_xx_ts.dll
   ```
4. Also install **ODBC Driver 17 or 18 for SQL Server** on the web-server machine.
5. Restart Apache / IIS.
6. Verify: `php -m | findstr sqlsrv` should show `pdo_sqlsrv`.

### 2 — Create the database

In SQL Server Management Studio (SSMS) or `sqlcmd`:
```sql
CREATE DATABASE StationeryDB;
GO
```

### 3 — Configure the connection

Open `config/db.php` and set:
```php
define('DB_HOST',     'localhost');       // or '192.168.1.10' for LAN/static IP
define('DB_PORT',     '1433');            // default SQL Server port
define('DB_NAME',     'StationeryDB');
define('DB_WIN_AUTH', false);             // true = Windows Auth (no user/pass needed)
define('DB_USER',     'sa');              // SQL Server login
define('DB_PASS',     'YourPassword');
```

**LAN / Static IP access** — set `DB_HOST` to the server's IP address, e.g.:
```php
define('DB_HOST', '192.168.1.10');
// Named instance: '192.168.1.10\\SQLEXPRESS'
```
Make sure TCP/IP is enabled in **SQL Server Configuration Manager** and port **1433** is open in Windows Firewall.

### 4 — Deploy the app

Copy this folder to `E:\StationeryManagement` (or your web server's document root) and open:
```
http://localhost/StationeryManagement/
http://192.168.1.10/StationeryManagement/   ← from other LAN machines
```

Table SQL scripts will be added here as each module is built.

---

## Modules

| Module | Description |
|---|---|
| **Items** | Add / edit stationery items with unit, category, reorder level |
| **Categories** | Group items by category |
| **Suppliers** | Supplier master with contact details |
| **Departments** | Departments that consume stationery |
| **Purchase Orders** | Raise and track purchase orders |
| **Goods Receipt** | Record items received against a purchase order |
| **Issue to Dept** | Issue stationery to a department |
| **Returns** | Record returns and rejections |
| **Stock** | Live stock position per item |
| **Reports** | Stock, purchase, issue, and supplier reports |