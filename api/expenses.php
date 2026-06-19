<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$conn   = getDBConnection();
$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------------
// GET — list expenses (with optional filters) OR stats
// -------------------------------------------------------
if ($method === 'GET') {

    // ---- Statistics endpoint ----
    if (isset($_GET['stats'])) {
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
        $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

        // By category (current month)
        $stmt = $conn->prepare(
            "SELECT category, SUM(amount) AS total
             FROM expenses
             WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
             GROUP BY category
             ORDER BY total DESC"
        );
        $stmt->bind_param("iii", $userId, $month, $year);
        $stmt->execute();
        $byCategory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Monthly trend (last 6 months)
        $stmt = $conn->prepare(
            "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total
             FROM expenses
             WHERE user_id = ?
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month DESC
             LIMIT 6"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $trend = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();

        // Total this month
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM expenses
             WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?"
        );
        $stmt->bind_param("iii", $userId, $month, $year);
        $stmt->execute();
        $totalMonth = (float)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        // Total last month
        $lastMonth     = $month === 1 ? 12 : $month - 1;
        $lastMonthYear = $month === 1 ? $year - 1 : $year;
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM expenses
             WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?"
        );
        $stmt->bind_param("iii", $userId, $lastMonth, $lastMonthYear);
        $stmt->execute();
        $totalLastMonth = (float)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        // This week (Mon–today)
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM expenses
             WHERE user_id = ?
               AND date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
               AND date <= CURDATE()"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $totalWeek = (float)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        // Total expense count (all time)
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM expenses WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $totalCount = (int)$stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        echo json_encode([
            'success' => true,
            'data'    => [
                'byCategory'     => $byCategory,
                'trend'          => $trend,
                'totalMonth'     => $totalMonth,
                'totalLastMonth' => $totalLastMonth,
                'totalWeek'      => $totalWeek,
                'totalCount'     => $totalCount,
            ]
        ]);
        $conn->close();
        exit();
    }

    // ---- List endpoint (with search/filter) ----
    $where  = ["e.user_id = ?"];
    $params = [$userId];
    $types  = "i";

    if (!empty($_GET['category'])) {
        $where[]  = "e.category = ?";
        $params[] = sanitize($_GET['category']);
        $types   .= "s";
    }
    if (!empty($_GET['start_date'])) {
        $where[]  = "e.date >= ?";
        $params[] = sanitize($_GET['start_date']);
        $types   .= "s";
    }
    if (!empty($_GET['end_date'])) {
        $where[]  = "e.date <= ?";
        $params[] = sanitize($_GET['end_date']);
        $types   .= "s";
    }
    if (!empty($_GET['search'])) {
        $where[]  = "e.description LIKE ?";
        $params[] = '%' . sanitize($_GET['search']) . '%';
        $types   .= "s";
    }

    $sql  = "SELECT e.* FROM expenses e WHERE " . implode(" AND ", $where) . " ORDER BY e.date DESC, e.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $expenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $expenses]);
    $conn->close();
    exit();
}

// -------------------------------------------------------
// POST — add new expense
// -------------------------------------------------------
if ($method === 'POST') {
    $data        = jsonInput();
    $category    = sanitize($data['category']    ?? '');
    $amount      = (float)($data['amount']       ?? 0);
    $description = sanitize($data['description'] ?? '');
    $date        = sanitize($data['date']        ?? '');

    if (empty($category) || $amount <= 0 || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Category, amount, and date are required.']);
        $conn->close();
        exit();
    }
    if (!in_array($category, validCategories())) {
        echo json_encode(['success' => false, 'message' => 'Invalid category.']);
        $conn->close();
        exit();
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO expenses (user_id, category, amount, description, date) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isdss", $userId, $category, $amount, $description, $date);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();

        // Return the newly created row
        $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->bind_param("i", $newId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Expense added.', 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add expense.']);
    }

    $conn->close();
    exit();
}

// -------------------------------------------------------
// PUT — update existing expense
// -------------------------------------------------------
if ($method === 'PUT') {
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $data = jsonInput();

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID.']);
        $conn->close();
        exit();
    }

    $category    = sanitize($data['category']    ?? '');
    $amount      = (float)($data['amount']       ?? 0);
    $description = sanitize($data['description'] ?? '');
    $date        = sanitize($data['date']        ?? '');

    if (empty($category) || $amount <= 0 || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Category, amount, and date are required.']);
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare(
        "UPDATE expenses SET category=?, amount=?, description=?, date=?
         WHERE id=? AND user_id=?"
    );
    $stmt->bind_param("sdssii", $category, $amount, $description, $date, $id, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Expense updated.', 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed or expense not found.']);
    }

    $conn->close();
    exit();
}

// -------------------------------------------------------
// DELETE — remove expense
// -------------------------------------------------------
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID.']);
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Expense deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Expense not found.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
$conn->close();
?>
