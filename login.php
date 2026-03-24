<?php
// login.php
session_start(); // 开启 Session，用来记住谁登录了
require_once 'Includes/db_connect.php'; // 引入数据库连接 (同一层级进 Includes)

$error_message = '';

// 如果用户点击了登录按钮 (表单使用 POST 提交)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 去 Users 表里查这个账号
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 检查账号是否存在，以及密码是否匹配
    // 注意：实际开发中应使用 password_verify()。为了配合咱们SQL里的假数据，这里先用直接等于(===)
    if ($user && $password === $user['password']) {
        // 登录成功！把信息存进 Session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // 根据角色进行页面跳转 (Routing)
        if ($user['role'] == 'Admin') {
            header("Location: Admin/manage_students.php"); // 跳转到 Admin 文件夹下
            exit();
        } elseif ($user['role'] == 'Assessor') {
            header("Location: Assessor/evaluate_student.php"); // 跳转到 Assessor 文件夹下
            exit();
        }
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Internship System Login</title>
    <style>
        body { font-family: Arial; background: #f4f7f6; display: flex; justify-content: center; margin-top: 100px; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>System Login</h2>
        <?php if ($error_message): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>