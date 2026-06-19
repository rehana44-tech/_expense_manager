// ============================================================
// Smart Expense Manager — Dashboard JS
// ============================================================

let pieChart, lineChart;
let allExpenses  = [];
let currentTab   = 'all';
let editingId    = null;

// -------------------------------------------------------
// Init
// -------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    setDefaultDate();
    loadExpenses();
    loadStats();
    loadBudgets();

    document.getElementById('expenseForm').addEventListener('submit', handleExpenseSubmit);
    document.getElementById('budgetForm').addEventListener('submit', setBudget);
});

function setDefaultDate() {
    const dateInput = document.getElementById('date');
    if (dateInput) dateInput.valueAsDate = new Date();
}

// -------------------------------------------------------
// Modal helpers
// -------------------------------------------------------
function openModal(expenseData = null) {
    editingId = null;
    const modal = document.getElementById('expenseModal');
    const title = document.getElementById('expenseModalTitle');
    const btn   = document.getElementById('expenseSubmitBtn');

    document.getElementById('expenseForm').reset();
    setDefaultDate();

    if (expenseData) {
        editingId = expenseData.id;
        title.textContent = 'Edit Expense';
        btn.textContent   = 'Update Expense';
        document.getElementById('category').value    = expenseData.category;
        document.getElementById('amount').value      = expenseData.amount;
        document.getElementById('description').value = expenseData.description;
        document.getElementById('date').value        = expenseData.date;
    } else {
        title.textContent = 'Add New Expense';
        btn.textContent   = 'Add Expense';
    }

    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('expenseModal').classList.remove('active');
    document.getElementById('expenseForm').reset();
    editingId = null;
}

function openBudgetModal() {
    document.getElementById('budgetModal').classList.add('active');
}

function closeBudgetModal() {
    document.getElementById('budgetModal').classList.remove('active');
    document.getElementById('budgetForm').reset();
}

// Close modals when clicking backdrop
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        closeModal();
        closeBudgetModal();
    }
});

// -------------------------------------------------------
// Add / Edit Expense
// -------------------------------------------------------
async function handleExpenseSubmit(e) {
    e.preventDefault();

    const data = {
        category:    document.getElementById('category').value,
        amount:      document.getElementById('amount').value,
        description: document.getElementById('description').value,
        date:        document.getElementById('date').value
    };

    const isEdit  = editingId !== null;
    const url     = isEdit ? `api/expenses.php?id=${editingId}` : 'api/expenses.php';
    const method  = isEdit ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            closeModal();
            await Promise.all([loadExpenses(), loadStats(), loadBudgets()]);
            showNotification(isEdit ? 'Expense updated!' : 'Expense added!', 'success');
        } else {
            showNotification(result.message || 'Failed to save expense.', 'error');
        }
    } catch (err) {
        console.error(err);
        showNotification('Network error. Please try again.', 'error');
    }
}

// -------------------------------------------------------
// Set Budget (BUG FIX: was not calling the API)
// -------------------------------------------------------
async function setBudget(e) {
    e.preventDefault();

    const data = {
        category: document.getElementById('budgetCategory').value,
        amount:   document.getElementById('budgetAmount').value
    };

    try {
        const response = await fetch('api/budgets.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            closeBudgetModal();
            await loadBudgets();
            showNotification('Budget saved!', 'success');
        } else {
            showNotification(result.message || 'Failed to save budget.', 'error');
        }
    } catch (err) {
        console.error(err);
        showNotification('Network error. Please try again.', 'error');
    }
}

// -------------------------------------------------------
// Load & Display Expenses
// -------------------------------------------------------
async function loadExpenses() {
    try {
        const response = await fetch('api/expenses.php');
        const result   = await response.json();
        if (result.success) {
            allExpenses = result.data;
            applyCurrentTab();
        }
    } catch (err) {
        console.error('loadExpenses:', err);
    }
}

function applyCurrentTab() {
    // Honour active filters first
    const filtered = applyFilters(allExpenses);

    let display = [...filtered];
    if (currentTab === 'recent') {
        display = display.slice(0, 10);
    } else if (currentTab === 'high') {
        display = display.sort((a, b) => parseFloat(b.amount) - parseFloat(a.amount)).slice(0, 10);
    }
    displayExpenses(display);
}

function displayExpenses(expenses) {
    const list = document.getElementById('expenseList');

    if (!expenses || expenses.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No expenses found</h3>
                <p>Try adjusting your filters or add a new expense.</p>
            </div>`;
        document.getElementById('displayedTotal').textContent = '0';
        return;
    }

    list.innerHTML = expenses.map(exp => {
        const icon      = getCategoryIcon(exp.category);
        const badgeCls  = `badge-${exp.category.toLowerCase()}`;
        const amountFmt = '$' + parseFloat(exp.amount).toFixed(2);
        const dateFmt   = formatDate(exp.date);
        const descHtml  = exp.description
            ? `<div class="expense-description">${escHtml(exp.description)}</div>`
            : '';

        return `
        <li class="expense-item">
            <div class="expense-info">
                <div class="expense-category">
                    ${icon} ${escHtml(exp.category)}
                    <span class="badge ${badgeCls}">${escHtml(exp.category)}</span>
                </div>
                ${descHtml}
                <div class="expense-date">${dateFmt}</div>
            </div>
            <div class="expense-amount">${amountFmt}</div>
            <div class="expense-actions">
                <button class="btn btn-secondary btn-sm" onclick='editExpense(${JSON.stringify(exp)})'>Edit</button>
                <button class="btn btn-danger btn-sm"    onclick="deleteExpense(${exp.id})">Delete</button>
            </div>
        </li>`;
    }).join('');

    document.getElementById('displayedTotal').textContent = expenses.length;
}

function editExpense(expenseData) {
    openModal(expenseData);
}

// -------------------------------------------------------
// Delete Expense
// -------------------------------------------------------
async function deleteExpense(id) {
    if (!confirm('Delete this expense?')) return;

    try {
        const response = await fetch(`api/expenses.php?id=${id}`, { method: 'DELETE' });
        const result   = await response.json();

        if (result.success) {
            await Promise.all([loadExpenses(), loadStats(), loadBudgets()]);
            showNotification('Expense deleted.', 'success');
        } else {
            showNotification(result.message || 'Delete failed.', 'error');
        }
    } catch (err) {
        console.error(err);
        showNotification('Network error.', 'error');
    }
}

// -------------------------------------------------------
// Load & Display Statistics (BUG FIX: real week/last-month data)
// -------------------------------------------------------
async function loadStats() {
    try {
        const response = await fetch('api/expenses.php?stats=1');
        const result   = await response.json();
        if (result.success) {
            updateStats(result.data);
            updateCharts(result.data);
        }
    } catch (err) {
        console.error('loadStats:', err);
    }
}

function updateStats(data) {
    const totalMonth = parseFloat(data.totalMonth     || 0);
    const lastMonth  = parseFloat(data.totalLastMonth || 0);
    const thisWeek   = parseFloat(data.totalWeek      || 0);
    const count      = parseInt(data.totalCount       || 0);

    document.getElementById('totalMonth').textContent    = '$' + totalMonth.toFixed(2);
    document.getElementById('totalExpenses').textContent = count;
    document.getElementById('thisWeek').textContent      = '$' + thisWeek.toFixed(2);
    document.getElementById('lastMonth').textContent     = '$' + lastMonth.toFixed(2);

    // Average daily based on days elapsed this month
    const dayOfMonth = new Date().getDate();
    document.getElementById('avgDaily').textContent = '$' + (totalMonth / dayOfMonth).toFixed(2);

    // Top category
    if (data.byCategory && data.byCategory.length > 0) {
        document.getElementById('topCategory').textContent = data.byCategory[0].category;
    }
}

// -------------------------------------------------------
// Load Budgets & Render Progress Bars
// -------------------------------------------------------
async function loadBudgets() {
    try {
        const response = await fetch('api/budgets.php');
        const result   = await response.json();
        if (result.success && result.data.length > 0) {
            renderBudgetBars(result.data);
            updateBudgetStatus(result.data);
        } else {
            document.getElementById('budgetOverview').style.display = 'none';
            document.getElementById('budgetStatus').textContent = 'No budgets set';
        }
    } catch (err) {
        console.error('loadBudgets:', err);
    }
}

function renderBudgetBars(budgets) {
    const container = document.getElementById('budgetBars');
    const overview  = document.getElementById('budgetOverview');
    overview.style.display = 'block';

    container.innerHTML = budgets.map(b => {
        const spent  = parseFloat(b.spent  || 0);
        const budget = parseFloat(b.amount || 1);
        const pct    = Math.min((spent / budget) * 100, 100).toFixed(1);
        const over   = spent > budget;
        const barCls = over ? 'budget-bar-over' : (pct > 80 ? 'budget-bar-warn' : 'budget-bar-ok');

        return `
        <div class="budget-row">
            <div class="budget-label">
                <span>${getCategoryIcon(b.category)} ${escHtml(b.category)}</span>
                <span class="${over ? 'text-danger' : ''}">
                    $${spent.toFixed(2)} / $${budget.toFixed(2)}
                </span>
            </div>
            <div class="budget-bar-track">
                <div class="budget-bar ${barCls}" style="width:${pct}%"></div>
            </div>
            <div class="budget-meta">
                <span>${pct}% used</span>
                <button class="btn btn-danger btn-xs" onclick="deleteBudget(${b.id})">Remove</button>
            </div>
        </div>`;
    }).join('');
}

function updateBudgetStatus(budgets) {
    const overBudget = budgets.filter(b => parseFloat(b.spent || 0) > parseFloat(b.amount));
    if (overBudget.length === 0) {
        document.getElementById('budgetStatus').textContent = '✅ On Track';
    } else {
        document.getElementById('budgetStatus').innerHTML =
            `⚠️ ${overBudget.length} over budget`;
    }
}

async function deleteBudget(id) {
    if (!confirm('Remove this budget?')) return;
    try {
        const response = await fetch(`api/budgets.php?id=${id}`, { method: 'DELETE' });
        const result   = await response.json();
        if (result.success) {
            loadBudgets();
            showNotification('Budget removed.', 'success');
        }
    } catch (err) {
        console.error(err);
    }
}

// -------------------------------------------------------
// Charts
// -------------------------------------------------------
function updateCharts(data) {
    const colors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a','#fee140','#a18cd1','#fbc2eb'];

    // Pie / Doughnut
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    if (pieChart) pieChart.destroy();

    if (data.byCategory && data.byCategory.length > 0) {
        pieChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels:   data.byCategory.map(c => c.category),
                datasets: [{
                    data:            data.byCategory.map(c => parseFloat(c.total)),
                    backgroundColor: colors,
                    borderWidth:     2,
                    borderColor:     '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    } else {
        pieCtx.canvas.parentElement.innerHTML += '<p class="chart-empty">No data for this month.</p>';
    }

    // Line / Trend
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    if (lineChart) lineChart.destroy();

    lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels:   (data.trend || []).map(t => t.month),
            datasets: [{
                label:           'Monthly Spending',
                data:            (data.trend || []).map(t => parseFloat(t.total)),
                borderColor:     '#667eea',
                backgroundColor: 'rgba(102,126,234,0.1)',
                tension:         0.4,
                fill:            true,
                pointRadius:     4,
                pointHoverRadius:6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '$' + v }
                }
            }
        }
    });
}

// -------------------------------------------------------
// Filtering & Tabs
// -------------------------------------------------------
function applyFilters(expenses) {
    const search    = (document.getElementById('searchInput')?.value    || '').toLowerCase();
    const category  =  document.getElementById('filterCategory')?.value  || '';
    const startDate =  document.getElementById('filterStartDate')?.value || '';
    const endDate   =  document.getElementById('filterEndDate')?.value   || '';

    return expenses.filter(exp => {
        if (category  && exp.category !== category)       return false;
        if (startDate && exp.date < startDate)            return false;
        if (endDate   && exp.date > endDate)              return false;
        if (search    && !exp.description.toLowerCase().includes(search)
                      && !exp.category.toLowerCase().includes(search)) return false;
        return true;
    });
}

function filterExpenses() {
    applyCurrentTab();
}

function clearFilters() {
    ['searchInput','filterCategory','filterStartDate','filterEndDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    applyCurrentTab();
}

// BUG FIX: was using implicit global `event`; now receives the button element explicitly
function switchTab(btn, tab) {
    currentTab = tab;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    applyCurrentTab();
}

// -------------------------------------------------------
// Export CSV
// -------------------------------------------------------
function exportData() {
    if (allExpenses.length === 0) {
        showNotification('No data to export.', 'error');
        return;
    }

    const visible = applyFilters(allExpenses);
    let csv = 'Date,Category,Description,Amount\n';
    visible.forEach(exp => {
        const desc = (exp.description || '').replace(/"/g, '""');
        csv += `${exp.date},"${exp.category}","${desc}",${exp.amount}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `expenses_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    showNotification('CSV exported!', 'success');
}

// -------------------------------------------------------
// Utilities
// -------------------------------------------------------
function getCategoryIcon(cat) {
    const icons = {
        Food: '🍔', Transport: '🚗', Shopping: '🛍️',
        Entertainment: '🎮', Bills: '💡', Health: '⚕️',
        Education: '📚', Travel: '✈️', Other: '📦'
    };
    return icons[cat] || '📦';
}

function formatDate(dateStr) {
    // Dates from MySQL arrive as 'YYYY-MM-DD'; add T00:00 to avoid timezone shifts
    const d = new Date(dateStr + 'T00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#39;');
}

function showNotification(message, type) {
    const el = document.createElement('div');
    el.className  = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    el.textContent = message;
    Object.assign(el.style, {
        position:  'fixed',
        top:       '20px',
        right:     '20px',
        zIndex:    '10000',
        animation: 'fadeIn 0.3s ease'
    });
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.3s';
        setTimeout(() => el.remove(), 300);
    }, 3000);
}

// -------------------------------------------------------
// Chatbot
// -------------------------------------------------------
function toggleChat() {
    document.getElementById('chatWindow').classList.toggle('active');
}

async function sendMessage() {
    const input   = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;

    addChatMessage(message, 'user');
    input.value = '';
    showTyping();

    try {
        const response = await fetch('api/chatbot.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ message })
        });
        const result = await response.json();
        removeTyping();
        addChatMessage(result.success ? result.response : 'Sorry, something went wrong.', 'bot');
    } catch (err) {
        removeTyping();
        addChatMessage('Sorry, I encountered an error. Please try again.', 'bot');
    }
}

function addChatMessage(html, sender) {
    const container = document.getElementById('chatMessages');
    const div       = document.createElement('div');
    div.className   = `message${sender === 'user' ? ' user' : ''}`;
    div.innerHTML   = `
        <div class="message-avatar">${sender === 'user' ? '👤' : 'AI'}</div>
        <div class="message-content">${sender === 'user' ? escHtml(html) : html}</div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    const container = document.getElementById('chatMessages');
    const div       = document.createElement('div');
    div.className   = 'message';
    div.id          = 'typingIndicator';
    div.innerHTML   = `
        <div class="message-avatar">AI</div>
        <div class="message-content">
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removeTyping() {
    document.getElementById('typingIndicator')?.remove();
}
