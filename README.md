# Smart Expense Manager — Setup Guide

## What's Included

```
expense_manager/
├── index.php              ← Landing page
├── login.php              ← Login page
├── register.php           ← Registration page
├── auth.php               ← Handles login / register / logout logic
├── dashboard.php          ← Main dashboard (protected)
├── config.php             ← DB connection + helper functions
│
├── api/
│   ├── expenses.php       ← CRUD + stats + search API
│   ├── budgets.php        ← Budget CRUD API (with spending totals)
│   └── chatbot.php        ← Rule-based expense assistant
│
├── css/
│   ├── global.css
│   ├── auth.css
│   ├── dashboard.css
│   ├── chatbot.css
│   └── landing.css
│
├── js/
│   └── dashboard.js       ← All dashboard interactivity
│
└── db/
    └── expense_manager.sql ← Complete schema + sample data
```

---

## Step 1 — Install a Local Server

You need **PHP 8.0+** and **MySQL 5.7+ / MariaDB 10.4+**.

### Option A — XAMPP (recommended, Windows/macOS/Linux)

1. Download from <https://www.apachefriends.org/>
2. Install and launch the **XAMPP Control Panel**
3. Start **Apache** and **MySQL**

### Option B — WAMP (Windows)

1. Download from <https://www.wampserver.com/>
2. Install, launch, and ensure the tray icon turns **green**
3. Both Apache and MySQL should start automatically

### Option C — MAMP (macOS)

1. Download from <https://www.mamp.info/>
2. Start servers; default ports are Apache 8888, MySQL 3306

---

## Step 2 — Place Project Files

| Server | Web root folder |
|--------|-----------------|
| XAMPP  | `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux/macOS) |
| WAMP   | `C:\wamp64\www\` |
| MAMP   | `/Applications/MAMP/htdocs/` |

Copy the entire `expense_manager/` folder into the web root.

**Example result:** `C:\xampp\htdocs\expense_manager\`

---

## Step 3 — Create the Database

### Via phpMyAdmin (easiest)

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Log in (default: user `root`, no password)
3. Click **"New"** in the left panel
4. Enter database name: `expense_manager` → click **Create**
5. Click the new `expense_manager` database in the left panel
6. Click the **SQL** tab at the top
7. Paste the entire contents of `db/expense_manager.sql`
8. Click **Go**

> ✅ You should see green success messages for all table creations.

### Via MySQL Command Line

```bash
# Log in
mysql -u root -p

# Inside MySQL shell:
source /path/to/expense_manager/db/expense_manager.sql
# OR run each block manually

exit
```

---

## Step 4 — Configure the Database Connection

Open `config.php` and verify these constants match your setup:

```php
define('DB_HOST', 'localhost');   // usually 'localhost'
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password (blank for XAMPP default)
define('DB_NAME', 'expense_manager');
```

**If you changed MySQL credentials** during installation, update `DB_USER` and `DB_PASS` accordingly.

### Common port differences

| Server | MySQL port |
|--------|-----------|
| XAMPP  | 3306 (default) |
| MAMP   | 8889 — change `DB_HOST` to `'localhost:8889'` |
| WAMP   | 3306 (default) |

---

## Step 5 — Run the Project

Open your browser and go to:

```
http://localhost/expense_manager/
```

You will see the landing page. Click **Sign Up** to create an account, or use the built-in demo account:

| Field    | Value            |
|----------|-----------------|
| Username | `demo`           |
| Password | `demo1234`       |

---

## Features Overview

### Dashboard
- **3 stat cards** — this month's total, total expense count, top category
- **4 summary cards** — average daily spend, this week, last month (real DB values), budget status
- **Budget progress bars** — shows each category's spend vs budget with colour coding (green/orange/red)
- **Doughnut chart** — spending by category (current month)
- **Line chart** — monthly trend (last 6 months)

### Expense Management
- **Add** expense (category, amount, description, date)
- **Edit** expense — click the Edit button on any row; form pre-fills
- **Delete** expense with confirmation
- **Search** by description keyword (real-time)
- **Filter** by category, start date, end date
- **Tabs** — All / Recent 10 / Top Amounts
- **Export to CSV** — exports currently visible filtered rows

### Budget Management
- Set a monthly budget per category
- Budgets are saved to DB (previously this was broken — it only closed the modal)
- Live progress bars with over-budget warnings
- Delete individual budgets

### Chatbot Assistant
- Ask in plain English:
  - "How much did I spend this month?"
  - "Show my category breakdown"
  - "What are my recent expenses?"
  - "Give me budget tips"
- Responses render formatted HTML in the chat window

---

## Bug Fixes Summary

| # | File | Bug | Fix Applied |
|---|------|-----|-------------|
| 1 | `js/dashboard.js` | `setBudget()` never called the API — only closed the modal | Added full `fetch('api/budgets.php', {method:'POST',...})` |
| 2 | `js/dashboard.js` | "Total Expenses" showed sum of amounts, not count | Changed to use `totalCount` from new API field |
| 3 | `js/dashboard.js` | "This Week" and "Last Month" used fake multiplications (×0.25, ×0.9) | API now returns real DB values; JS uses them directly |
| 4 | `js/dashboard.js` | `switchTab()` used implicit global `event` object (fails in strict mode) | Function now receives the button element as a parameter |
| 5 | `js/dashboard.js` | No Edit functionality existed | Added `openModal(expenseData)` + PUT fetch + edit modal |
| 6 | `api/expenses.php` | No `UPDATE` (PUT) endpoint | Added full PUT handler with validation |
| 7 | `api/expenses.php` | No server-side search/filter params | Added `category`, `start_date`, `end_date`, `search` query params |
| 8 | `api/expenses.php` | Stats returned fake approximations for week/last month | Added real SQL queries for both |
| 9 | `api/budgets.php` | Used two queries (SELECT then INSERT/UPDATE) — race condition possible | Replaced with single `INSERT ... ON DUPLICATE KEY UPDATE` |
| 10 | `api/chatbot.php` | Responses used `\n` newlines — displayed literally in browser | Changed to `<br>` HTML; also removed `white-space:pre-wrap` from CSS |
| 11 | `db/expense_manager.sql` | `CREATE DATABASE` was commented out with invalid `--` inside comment | Fixed to a proper runnable SQL file |
| 12 | `auth.php` | No `session_regenerate_id()` after login (session fixation risk) | Added `session_regenerate_id(true)` on successful login |
| 13 | `config.php` | `die()` on DB error returned plain text inside API endpoints | Replaced with JSON error response + correct HTTP 500 status |
| 14 | `dashboard.php` | `<ul>` contained `<div>` as direct child (invalid HTML) | Restructured to use proper empty-state rendering in JS |
| 15 | `js/dashboard.js` | Date formatted with `new Date(dateStr)` — off-by-one due to timezone | Changed to `new Date(dateStr + 'T00:00')` to fix timezone issue |

---

## Troubleshooting

**Blank white page / PHP errors**
- Enable error display: add `ini_set('display_errors', 1);` at top of `config.php` temporarily
- Check Apache error log: `C:\xampp\apache\logs\error.log`

**"Connection failed" error**
- Make sure MySQL is running in XAMPP/WAMP control panel
- Double-check `DB_USER` and `DB_PASS` in `config.php`

**"Table doesn't exist" error**
- You may not have run the SQL file. Go back to Step 3.

**Charts not showing**
- Check browser console (F12) for JS errors
- Ensure you have an internet connection (Chart.js loads from CDN)

**Expenses not saving**
- Open browser DevTools → Network tab → check the API response from `api/expenses.php`
- Ensure the `expenses` table was created (Step 3)
