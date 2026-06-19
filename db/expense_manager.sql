-- ============================================================
-- Smart Expense Manager - Database Setup
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

-- Step 1: Create and select the database
CREATE DATABASE IF NOT EXISTS expense_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE expense_manager;

-- ============================================================
-- TABLES
-- ============================================================

-- Users
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  UNIQUE NOT NULL,
    email       VARCHAR(100) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Expense categories (normalised lookup)
CREATE TABLE IF NOT EXISTS categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(50) UNIQUE NOT NULL,
    icon  VARCHAR(10) NOT NULL DEFAULT '📦'
) ENGINE=InnoDB;

-- Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    category    VARCHAR(50)    NOT NULL,
    amount      DECIMAL(12,2)  NOT NULL CHECK (amount > 0),
    description TEXT,
    date        DATE           NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date),
    INDEX idx_user_category (user_id, category)
) ENGINE=InnoDB;

-- Budgets (one per user/category/month/year)
CREATE TABLE IF NOT EXISTS budgets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    category    VARCHAR(50)    NOT NULL,
    amount      DECIMAL(12,2)  NOT NULL CHECK (amount > 0),
    month       TINYINT        NOT NULL CHECK (month BETWEEN 1 AND 12),
    year        SMALLINT       NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_budget (user_id, category, month, year)
) ENGINE=InnoDB;

-- Chat history
CREATE TABLE IF NOT EXISTS chat_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT  NOT NULL,
    message     TEXT NOT NULL,
    response    TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT IGNORE INTO categories (name, icon) VALUES
  ('Food',          '🍔'),
  ('Transport',     '🚗'),
  ('Shopping',      '🛍️'),
  ('Entertainment', '🎮'),
  ('Bills',         '💡'),
  ('Health',        '⚕️'),
  ('Education',     '📚'),
  ('Travel',        '✈️'),
  ('Other',         '📦');

-- Demo user  (password: demo1234)
INSERT IGNORE INTO users (username, email, password) VALUES
  ('demo', 'demo@example.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample expenses for the demo user (user_id = 1)
-- Adjust dates relative to current month as needed
INSERT IGNORE INTO expenses (user_id, category, amount, description, date) VALUES
  (1, 'Food',          12.50, 'Lunch at restaurant',       DATE_FORMAT(NOW(), '%Y-%m-05')),
  (1, 'Food',          45.00, 'Weekly grocery shopping',   DATE_FORMAT(NOW(), '%Y-%m-08')),
  (1, 'Transport',     30.00, 'Monthly bus pass',          DATE_FORMAT(NOW(), '%Y-%m-01')),
  (1, 'Transport',      8.50, 'Taxi to office',            DATE_FORMAT(NOW(), '%Y-%m-10')),
  (1, 'Bills',        120.00, 'Electricity bill',          DATE_FORMAT(NOW(), '%Y-%m-03')),
  (1, 'Bills',         60.00, 'Internet subscription',     DATE_FORMAT(NOW(), '%Y-%m-03')),
  (1, 'Entertainment', 15.00, 'Netflix subscription',      DATE_FORMAT(NOW(), '%Y-%m-06')),
  (1, 'Entertainment', 25.00, 'Cinema tickets',            DATE_FORMAT(NOW(), '%Y-%m-14')),
  (1, 'Health',        55.00, 'Pharmacy',                  DATE_FORMAT(NOW(), '%Y-%m-12')),
  (1, 'Shopping',      89.99, 'New shoes',                 DATE_FORMAT(NOW(), '%Y-%m-09')),
  (1, 'Education',     20.00, 'Online course subscription',DATE_FORMAT(NOW(), '%Y-%m-02'));

-- Sample budgets for the demo user
INSERT IGNORE INTO budgets (user_id, category, amount, month, year) VALUES
  (1, 'Food',          200.00, MONTH(NOW()), YEAR(NOW())),
  (1, 'Transport',      80.00, MONTH(NOW()), YEAR(NOW())),
  (1, 'Bills',         200.00, MONTH(NOW()), YEAR(NOW())),
  (1, 'Entertainment',  60.00, MONTH(NOW()), YEAR(NOW())),
  (1, 'Health',        100.00, MONTH(NOW()), YEAR(NOW())),
  (1, 'Shopping',      150.00, MONTH(NOW()), YEAR(NOW()));
