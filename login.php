<?php
session_start();
require_once 'Includes/db_connect.php'; // 确保这里连的是你的数据库

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 去数据库里找这个账号
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 验证密码
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // 【新增逻辑】登录成功后，发一张有效期 30 天的 Cookie 记住用户名
        setcookie("remember_user", $username, time() + (86400 * 30), "/");

    // 根据角色跳到不同页面
        if ($user['role'] === 'Admin') {
            header("Location: Admin/manage_students.php");
        } else {
            header("Location: Assessor/evaluate_student.php");
        }
        exit();
    } else {
        // 【修改逻辑】和 Moodle 一模一样的报错文案
        $error_message = "Invalid login, please try again";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Login</title>
    <style>
        /* =========================================
           1. 基础页面样式 
           ========================================= */
        body {
            background-color: #fcfbf9; 
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px; 
            padding: 40px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05); 
            box-sizing: border-box;
            border-radius: 4px;
        }

        .logo-container {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .logo-container img {
            width: 100%;
            max-width: 320px; 
            display: block;
            margin: 0 auto;
        }

        /* 【新增样式】完美复刻的粉红色报错方块 */
        .error-msg {
            background-color: #f8d7da; /* 浅粉色底 */
            color: #842029; /* 深红色字 */
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c2c7;
            border-radius: 4px;
        }

        .input-group { margin-bottom: 15px; }
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #8f959e; 
            border-radius: 4px; 
            font-size: 16px;
            box-sizing: border-box;
            background-color: #ffffff;
        }
        .input-group input:focus {
            outline: none;
            border-color: #10263b; 
            box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2);
            background-color: #e8f0fe; 
        }

        .btn-login {
            background-color: #10263b; 
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block; 
        }
        .btn-login:hover { background-color: #0d1e2e; }

        .problems-link {
            display: block;
            margin-top: 25px;
            color: #555;
            text-decoration: underline;
            font-size: 14px;
        }

        hr.divider {
            border: 0;
            border-top: 1px solid #e1e1e1;
            margin: 25px 0;
        }

        .btn-cookies {
            background-color: #7a327e; 
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cookies:hover { background-color: #5e2661; }

        /* =========================================
           2. 弹窗专属样式 (Cookie Modal)
           ========================================= */
        .moodle-modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(67, 83, 99, 0.6); 
            z-index: 1000; 
            justify-content: center;
            align-items: center;
        }

        .moodle-modal-box {
            background-color: #ffffff;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-family: Arial, Helvetica, sans-serif;
            color: #1d2125; 
            border-radius: 4px;
            overflow: hidden;
        }

        .moodle-modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .moodle-modal-header h2 {
            margin: 0;
            font-size: 18px;
            color: #10263b; 
        }
        .moodle-close-x {
            font-size: 24px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
        }
        .moodle-close-x:hover { color: #333; }

        .moodle-modal-body {
            padding: 20px;
            font-size: 15px;
            line-height: 1.6;
        }
        .moodle-modal-body p { margin-top: 0; margin-bottom: 15px; }

        .moodle-modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #dee2e6; 
            text-align: right; 
            background-color: #f8f9fa; 
        }
        .moodle-btn-ok {
            background-color: #10263b; 
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .moodle-btn-ok:hover { background-color: #0d1e2e; }
    </style>
</head>
<body>

<div class="login-card">
    
    <div class="logo-container">
        <img src="images/logo.png" alt="University Logo">
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Username" 
                   value="<?php echo isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : ''; ?>" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        
        <button type="submit" class="btn-login">Log in</button>
    </form>

    <a href="#" class="problems-link">Problems logging in?</a>
    
    <hr class="divider">
    
    <button type="button" class="btn-cookies" id="openModalBtn">Cookies notice</button>
</div>

<div id="cookieModal" class="moodle-modal-overlay">
    <div class="moodle-modal-box">
        <div class="moodle-modal-header">
            <h2>Cookies must be enabled in your browser</h2>
            <span class="moodle-close-x" id="closeModalX">&times;</span>
        </div>
        <div class="moodle-modal-body">
            <p>Two cookies are used on this site:</p>
            <p>The essential one is the session cookie. You must allow this cookie in your browser to provide continuity and to remain securely logged in as an Admin or Assessor when browsing the system. When you log out or close the browser, this cookie is destroyed.</p>
            <p>The other cookie is purely for convenience. It just remembers your username in the browser. It is safe to refuse this cookie - you will just have to retype your username each time you log in.</p>
        </div>
        <div class="moodle-modal-footer">
            <button id="closeModalBtn" class="moodle-btn-ok">OK</button>
        </div>
    </div>
</div>

<script>
    var modal = document.getElementById("cookieModal");
    var openBtn = document.getElementById("openModalBtn");
    var closeX = document.getElementById("closeModalX");
    var closeBtn = document.getElementById("closeModalBtn");

    openBtn.onclick = function() { modal.style.display = "flex"; }
    closeX.onclick = function() { modal.style.display = "none"; }
    closeBtn.onclick = function() { modal.style.display = "none"; }
    window.onclick = function(event) {
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

</body>
</html>