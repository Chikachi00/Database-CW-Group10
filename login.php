<?php
session_start();

// 如果已经登录，直接跳走
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: Admin/manage_students.php");
        exit();
    } else if ($_SESSION['role'] === 'Assessor') {
        header("Location: Assessor/evaluate_student.php");
        exit();
    }
}

require_once 'Includes/db_connect.php';

$error_msg = "";

// 获取之前可能保存的用户名 Cookie
$remembered_user = isset($_COOKIE['remembered_username']) ? $_COOKIE['remembered_username'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // 假设你的表名是 Users，字段有 user_id, username, password, role
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 验证密码（支持明文测试和哈希加密）
        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // 登录成功时，设置 Cookie 记住账号，有效期 30 天
            setcookie('remembered_username', $username, time() + (86400 * 30), "/");

            // 根据角色重定向到不同文件夹
            if ($user['role'] === 'Admin') {
                header("Location: Admin/manage_students.php");
            } else {
                header("Location: Assessor/evaluate_student.php");
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
        /* 针对超链接的悬停效果 */
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