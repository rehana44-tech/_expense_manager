<?php
require_once 'config.php';

// -------------------------------------------------------
// Registration
// -------------------------------------------------------
if (isset($_POST['register'])) {
    $username         = sanitize($_POST['username']         ?? '');
    $email            = sanitize($_POST['email']            ?? '');
    $password         = $_POST['password']         ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = 'All fields are required.';
    }
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(' ', $errors);
        header('Location: register.php');
        exit();
    }

    $conn = getDBConnection();

    // Check for duplicates
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'Username or email is already registered.';
        $stmt->close();
        $conn->close();
        header('Location: register.php');
        exit();
    }
    $stmt->close();

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt   = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Account created! Please sign in.';
        $stmt->close();
        $conn->close();
        header('Location: login.php');
        exit();
    }

    $_SESSION['error'] = 'Registration failed. Please try again.';
    $stmt->close();
    $conn->close();
    header('Location: register.php');
    exit();
}

// -------------------------------------------------------
// Login
// -------------------------------------------------------
if (isset($_POST['login'])) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username/email and password are required.';
        header('Location: login.php');
        exit();
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);          // prevent session fixation
            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $stmt->close();
            $conn->close();
            header('Location: dashboard.php');
            exit();
        }
    }

    $_SESSION['error'] = 'Invalid username/email or password.';
    $stmt->close();
    $conn->close();
    header('Location: login.php');
    exit();
}

// -------------------------------------------------------
// Logout
// -------------------------------------------------------
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
