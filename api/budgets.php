<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$conn   = getDBConnection();
$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------------
// GET — list budgets with actual spending
// -------------------------------------------------------
if ($method === 'GET') {
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    $stmt = $conn->prepare(
        "SELECT b.*,
                COALESCE(e.spent, 0) AS spent
         FROM   budgets b
         LEFT JOIN (
             SELECT category, SUM(amount) AS spent
             FROM   expenses
             WHERE  user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
             GROUP BY category
         ) e ON b.category = e.category
         WHERE  b.user_id = ? AND b.month = ? AND b.year = ?
         ORDER BY b.category"
    );
    $stmt->bind_param("iiiiii", $userId, $month, $year, $userId, $month, $year);
    $stmt->execute();
    $budgets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $budgets]);
    $conn->close();
    exit();
}

// -------------------------------------------------------
// POST — upsert budget
// -------------------------------------------------------
if ($method === 'POST') {
    $data     = jsonInput();
    $category = sanitize($data['category'] ?? '');
    $amount   = (float)($data['amount']    ?? 0);
    $month    = isset($data['month']) ? (int)$data['month'] : (int)date('n');
    $year     = isset($data['year'])  ? (int)$data['year']  : (int)date('Y');

    if (empty($category) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Category and a positive amount are required.']);
        $conn->close();
        exit();
    }
    if (!in_array($category, validCategories())) {
        echo json_encode(['success' => false, 'message' => 'Invalid category.']);
        $conn->close();
        exit();
    }

    // UPSERT
    $stmt = $conn->prepare(
        "INSERT INTO budgets (user_id, category, amount, month, year)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE amount = VALUES(amount)"
    );
    $stmt->bind_param("isdii", $userId, $category, $amount, $month, $year);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Budget saved.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save budget.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// -------------------------------------------------------
// DELETE — remove budget
// -------------------------------------------------------
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing budget ID.']);
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Budget deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Budget not found.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
$conn->close();
?>
