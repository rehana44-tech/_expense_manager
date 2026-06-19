<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Smart Expense Manager</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/chatbot.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard">

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="navbar-content">
        <a href="index.php" class="navbar-brand">💰 Expense Manager</a>
        <div class="navbar-user">
            <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="auth.php?logout=1" class="btn btn-secondary">Logout</a>
        </div>
    </div>
</nav>

<div class="container">

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="action-btn" onclick="openModal()">
            <div class="action-icon">➕</div>
            <div class="action-text"><h4>Add Expense</h4><p>Record a new expense</p></div>
        </button>
        <button class="action-btn" onclick="openBudgetModal()">
            <div class="action-icon">🎯</div>
            <div class="action-text"><h4>Set Budget</h4><p>Manage spending limits</p></div>
        </button>
        <button class="action-btn" onclick="exportData()">
            <div class="action-icon">📥</div>
            <div class="action-text"><h4>Export CSV</h4><p>Download your data</p></div>
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>This Month</h3>
            <div class="amount" id="totalMonth">$0.00</div>
        </div>
        <div class="stat-card">
            <h3>Total Expenses</h3>
            <div class="amount" id="totalExpenses">0</div>
        </div>
        <div class="stat-card">
            <h3>Top Category</h3>
            <div class="amount" id="topCategory">—</div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h4>Avg Daily</h4>
            <div class="value" id="avgDaily">$0.00</div>
        </div>
        <div class="summary-card">
            <h4>This Week</h4>
            <div class="value" id="thisWeek">$0.00</div>
        </div>
        <div class="summary-card">
            <h4>Last Month</h4>
            <div class="value" id="lastMonth">$0.00</div>
        </div>
        <div class="summary-card">
            <h4>Budget Status</h4>
            <div class="value" id="budgetStatus">—</div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="card" id="budgetOverview" style="display:none;">
        <h2>Budget Overview</h2>
        <div id="budgetBars"></div>
    </div>

    <!-- Charts -->
    <div class="grid grid-2">
        <div class="chart-container">
            <h2>Spending by Category</h2>
            <div class="chart-wrapper"><canvas id="pieChart"></canvas></div>
        </div>
        <div class="chart-container">
            <h2>Monthly Trend</h2>
            <div class="chart-wrapper"><canvas id="lineChart"></canvas></div>
        </div>
    </div>

    <!-- Filter + Search -->
    <div class="filter-section">
        <h3>Filter &amp; Search</h3>
        <div class="filter-controls">
            <input type="text"   id="searchInput"    placeholder="🔍 Search description…" oninput="filterExpenses()">
            <select             id="filterCategory"  onchange="filterExpenses()">
                <option value="">All Categories</option>
                <option value="Food">🍔 Food</option>
                <option value="Transport">🚗 Transport</option>
                <option value="Shopping">🛍️ Shopping</option>
                <option value="Entertainment">🎮 Entertainment</option>
                <option value="Bills">💡 Bills</option>
                <option value="Health">⚕️ Health</option>
                <option value="Education">📚 Education</option>
                <option value="Travel">✈️ Travel</option>
                <option value="Other">📦 Other</option>
            </select>
            <input type="date"   id="filterStartDate" onchange="filterExpenses()" title="From">
            <input type="date"   id="filterEndDate"   onchange="filterExpenses()" title="To">
            <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
        </div>
    </div>

    <!-- Expense List -->
    <div class="card">
        <div class="tabs">
            <button class="tab active" data-tab="all"    onclick="switchTab(this,'all')">All</button>
            <button class="tab"        data-tab="recent" onclick="switchTab(this,'recent')">Recent 10</button>
            <button class="tab"        data-tab="high"   onclick="switchTab(this,'high')">Top Amounts</button>
        </div>

        <ul class="expense-list" id="expenseList">
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No expenses yet</h3>
                <p>Click "Add Expense" to start tracking.</p>
            </div>
        </ul>

        <div class="export-section">
            <span>Showing <strong id="displayedTotal">0</strong> expense(s)</span>
            <button class="btn btn-primary" onclick="exportData()">📥 Export to CSV</button>
        </div>
    </div>
</div>

<!-- ===== ADD / EDIT EXPENSE MODAL ===== -->
<div id="expenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="expenseModalTitle">Add New Expense</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form id="expenseForm">
            <input type="hidden" id="editExpenseId">
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Food">🍔 Food</option>
                    <option value="Transport">🚗 Transport</option>
                    <option value="Shopping">🛍️ Shopping</option>
                    <option value="Entertainment">🎮 Entertainment</option>
                    <option value="Bills">💡 Bills</option>
                    <option value="Health">⚕️ Health</option>
                    <option value="Education">📚 Education</option>
                    <option value="Travel">✈️ Travel</option>
                    <option value="Other">📦 Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="number" id="amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" id="description" class="form-control" maxlength="255">
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block" id="expenseSubmitBtn">Add Expense</button>
        </form>
    </div>
</div>

<!-- ===== SET BUDGET MODAL ===== -->
<div id="budgetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Set Monthly Budget</h2>
            <button class="close-modal" onclick="closeBudgetModal()">&times;</button>
        </div>
        <form id="budgetForm">
            <div class="form-group">
                <label for="budgetCategory">Category</label>
                <select id="budgetCategory" class="form-control" required>
                    <option value="Food">🍔 Food</option>
                    <option value="Transport">🚗 Transport</option>
                    <option value="Shopping">🛍️ Shopping</option>
                    <option value="Entertainment">🎮 Entertainment</option>
                    <option value="Bills">💡 Bills</option>
                    <option value="Health">⚕️ Health</option>
                    <option value="Education">📚 Education</option>
                    <option value="Travel">✈️ Travel</option>
                    <option value="Other">📦 Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="budgetAmount">Budget Amount ($)</label>
                <input type="number" id="budgetAmount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Budget</button>
        </form>
    </div>
</div>

<!-- ===== CHATBOT ===== -->
<div class="chatbot-container">
    <button class="chatbot-toggle" onclick="toggleChat()" title="Expense Assistant">💬</button>
    <div class="chatbot-window" id="chatWindow">
        <div class="chatbot-header">
            <h3>💰 Expense Assistant</h3>
            <button class="chatbot-close" onclick="toggleChat()">×</button>
        </div>
        <div class="chatbot-messages" id="chatMessages">
            <div class="message">
                <div class="message-avatar">AI</div>
                <div class="message-content">
                    Hi! I'm your expense assistant. Try asking:<br>
                    • <em>How much did I spend this month?</em><br>
                    • <em>Show my category breakdown</em><br>
                    • <em>Show recent expenses</em>
                </div>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatInput" placeholder="Ask me anything…"
                   onkeydown="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
