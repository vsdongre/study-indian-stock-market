# Stationery Management System

A **PHP + MySQL** web application for managing stationery inventory, built with a classic **VB6 / Windows 98** look-and-feel.

---

## Project Structure

```
StationeryManagement/
├── index.php               ← Dashboard (homepage)
├── config/
│   └── db.php              ← PDO database connection
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
| PHP | 7.4 or higher |
| MySQL / MariaDB | 5.7 / 10.3 or higher |
| Web server | Apache (XAMPP / WAMP) or PHP built-in server |

---

## Setup (Windows — XAMPP / WAMP)

1. Copy this folder to `E:\StationeryManagement` (or your `htdocs` / `www` folder).
2. Open `config/db.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'stationery_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Create the database in phpMyAdmin (or MySQL CLI):
   ```sql
   CREATE DATABASE stationery_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Table SQL scripts will be added here as each module is built.
5. Open your browser: `http://localhost/StationeryManagement/`

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