<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$conn   = getDBConnection();
$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    $conn->close();
    exit();
}

$data    = jsonInput();
$message = trim($data['message'] ?? '');

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Empty message.']);
    $conn->close();
    exit();
}

$response = generateResponse(strtolower($message), $userId, $conn);

// Save chat history
$stmt = $conn->prepare("INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $userId, $message, $response);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'response' => $response]);
$conn->close();
exit();

// -------------------------------------------------------
function generateResponse(string $msg, int $userId, mysqli $conn): string {
    $br = "<br>"; // Use HTML breaks so the chat widget renders them

    // --- Spending this month ---
    if (str_contains($msg, 'month') && (str_contains($msg, 'spend') || str_contains($msg, 'spent') || str_contains($msg, 'total'))) {
        $m = (int)date('n'); $y = (int)date('Y');
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM expenses WHERE user_id=? AND MONTH(date)=? AND YEAR(date)=?");
        $stmt->bind_param("iii", $userId, $m, $y);
        $stmt->execute();
        $total = (float)$stmt->get_result()->fetch_assoc()['t'];
        $stmt->close();
        return "You have spent <strong>\${$total}</strong> so far this month (" . date('F Y') . ").";
    }

    // --- Spending today ---
    if (str_contains($msg, 'today')) {
        $today = date('Y-m-d');
        $stmt  = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM expenses WHERE user_id=? AND date=?");
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $total = (float)$stmt->get_result()->fetch_assoc()['t'];
        $stmt->close();
        return $total > 0
            ? "You have spent <strong>\${$total}</strong> today."
            : "You have not recorded any expenses for today yet.";
    }

    // --- Category breakdown ---
    if (str_contains($msg, 'categor') || str_contains($msg, 'breakdown') || str_contains($msg, 'most')) {
        $m = (int)date('n'); $y = (int)date('Y');
        $stmt = $conn->prepare(
            "SELECT category, SUM(amount) AS t FROM expenses
             WHERE user_id=? AND MONTH(date)=? AND YEAR(date)=?
             GROUP BY category ORDER BY t DESC"
        );
        $stmt->bind_param("iii", $userId, $m, $y);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($rows)) {
            return "No expenses recorded this month yet.";
        }
        $lines = ["<strong>This month's breakdown:</strong>"];
        foreach ($rows as $r) {
            $lines[] = "• {$r['category']}: \$" . number_format((float)$r['t'], 2);
        }
        return implode($br, $lines);
    }

    // --- Recent expenses ---
    if (str_contains($msg, 'recent') || str_contains($msg, 'last expense') || str_contains($msg, 'latest')) {
        $stmt = $conn->prepare(
            "SELECT category, amount, description, date FROM expenses
             WHERE user_id=? ORDER BY date DESC, id DESC LIMIT 5"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($rows)) {
            return "You have no expenses recorded yet.";
        }
        $lines = ["<strong>Your 5 most recent expenses:</strong>"];
        foreach ($rows as $r) {
            $lines[] = "• \$" . number_format((float)$r['amount'], 2)
                     . " — {$r['category']} ({$r['date']})";
        }
        return implode($br, $lines);
    }

    // --- Budget tips / savings ---
    if (str_contains($msg, 'budget') || str_contains($msg, 'save') || str_contains($msg, 'tip')) {
        return implode($br, [
            "<strong>💡 Budget tips for you:</strong>",
            "1. Set monthly limits per category using the <em>Set Budget</em> button.",
            "2. Track every expense — even small coffees add up.",
            "3. Review your spending chart weekly.",
            "4. Cut unused subscriptions.",
            "5. Plan meals ahead to lower your Food spend.",
        ]);
    }

    // --- Help ---
    if (str_contains($msg, 'help') || str_contains($msg, 'what can you')) {
        return implode($br, [
            "<strong>I can help you with:</strong>",
            "• How much you spent <em>this month</em> or <em>today</em>",
            "• Your <em>category breakdown</em> this month",
            "• Your <em>recent expenses</em>",
            "• <em>Budget tips</em> and savings advice",
        ]);
    }

    // Default
    $defaults = [
        "Try asking: <em>How much did I spend this month?</em>",
        "Ask me about your <em>recent expenses</em> or <em>category breakdown</em>.",
        "Need tips? Ask me for <em>budget advice</em>!",
    ];
    return $defaults[array_rand($defaults)];
}
?>
