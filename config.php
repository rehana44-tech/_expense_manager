<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', '127.0.0.1:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_manager');

// Start session once at the top level
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------
// DB connection (returns MySQLi object or dies with JSON)
// -------------------------------------------------------
function getDBConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        // If we are inside an API endpoint, return JSON error
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// -------------------------------------------------------
// Auth helpers
// -------------------------------------------------------
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        // API requests expect JSON; page requests get a redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        header('Location: ' . (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false ? '../login.php' : 'login.php'));
        exit();
    }
}

function getCurrentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// -------------------------------------------------------
// Input helpers
// -------------------------------------------------------
function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function jsonInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Valid expense categories
function validCategories(): array {
    return ['Food','Transport','Shopping','Entertainment','Bills','Health','Education','Travel','Other'];
}
?>
