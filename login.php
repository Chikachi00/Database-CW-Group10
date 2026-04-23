<?php
session_start();
// Test Accounts (Plaintext Passwords for Reference)
// admin       -> admin123
// Dr_smith    -> smith123
// Prof_jones  -> jones123

// if logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: Admin/dashboard.php");
        exit();
    } else if ($_SESSION['role'] === 'Assessor') {
        header("Location: Assessor/assessor_dashboard.php");
        exit();
    }
}

require_once 'Includes/db_connect.php';

$error_msg = "";

// Check if remembered username in the cookie
$remembered_user = isset($_COOKIE['remembered_username']) ? $_COOKIE['remembered_username'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // securely query the database for the username
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // verify password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Set a cookie to remember the username for 30 days
            setcookie('remembered_username', $username, time() + (86400 * 30), "/");

            // redirect based on role
            if ($user['role'] === 'Admin') {
                header("Location: Admin/dashboard.php");
            } else {
                header("Location: Assessor/assessor_dashboard.php");
            }
            exit();
        } else {
            $error_msg = "Invalid username or password. Please try again.";
        }
    } catch (PDOException $e) {
        $error_msg = "System error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Internship Assessment System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Login Page Styles */
        .moodle-links a {
            color: #10263b;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .moodle-links a:hover {
            color: #7a327e;
            text-decoration: underline;
        }
    </style>
</head>
<body class="moodle-login-body">
    <div class="moodle-page-bg">
        <div class="moodle-login-card">
            <div class="moodle-logo-container">
                <img src="images/logo.png" alt="University Logo" style="max-width: 250px; height: auto;">
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="error-msg"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="moodle-input-group">
                    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($remembered_user) ?>" required <?= empty($remembered_user) ? 'autofocus' : '' ?>>
                </div>
                <div class="moodle-input-group">
                    <input type="password" name="password" placeholder="Password" required <?= !empty($remembered_user) ? 'autofocus' : '' ?>>
                </div>
                <button type="submit" class="moodle-btn-login" style="width: 100%;">Log in</button>
            </form>

            <div class="moodle-links" style="text-align: center; margin-top: 25px;">
                <a href="https://www.nottingham.edu.my/it-services/" target="_blank" title="Contact IT Services for help">Problem logging in?</a>
            </div>
            
        </div>
    </div>
</body>
</html>