<?php
require_once 'config.php';
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Smart Expense Manager</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">💰</div>
            <h1>Welcome Back</h1>
            <p>Sign in to manage your expenses</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="auth.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control"
                       autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       autocomplete="current-password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block">
                Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
    </div>
</div>
</body>
</html>
