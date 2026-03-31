<?php
// Admin/manage_users.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../Includes/db_connect.php';

$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password']; 
    try {
        $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:user, :pass, 'Assessor')");
        $stmt->execute(['user' => $new_username, 'pass' => $new_password]);
        $success_msg = "Assessor added successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error: Username might already exist.";
    }
}

$stmt = $pdo->query("SELECT * FROM Users WHERE role = 'Assessor' ORDER BY user_id DESC");
$assessors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assessors - Admin</title>
    <style>
        /* 1:1 复制 evaluate_student.php 的原生 CSS */
        body { background-color: #f8f9fa; color: #1d2125; font-family: Arial, Helvetica, sans-serif; margin: 0; padding-top: 80px; }
        .moodle-navbar-white { background-color: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; height: 70px; border-bottom: 1px solid #dee2e6; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; }
        .nav-left-white { display: flex; align-items: center; height: 100%; }
        .nav-logo-white { height: 68px; width: auto; margin-right: 25px; border-right: 1px solid #dee2e6; padding-right: 25px; display: block; }
        .nav-links { display: flex; gap: 25px; align-items: center; height: 100%; }
        .nav-links a { color: #555; text-decoration: none; font-size: 15px; font-weight: 500; height: 100%; display: flex; align-items: center; border-bottom: 3px solid transparent; box-sizing: border-box; cursor: pointer; }
        .nav-links a:hover { color: #10263b; text-decoration: underline; }
        .nav-links a.active-link { color: rgba(16, 38, 59, 0.9); border-bottom: 3px solid #7a327e; }
        .nav-right-white { display: flex; align-items: center; gap: 20px; }
        .user-avatar { background-color: #7a327e; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 16px; }
        .moodle-page-header { max-width: 95%; margin: 20px auto 20px; font-size: 30px; color: #10263b; font-weight: normal; }
        .moodle-dashboard-container { max-width: 95%; margin: 0 auto 60px; padding: 0 25px; }
        .moodle-card-white { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 40px; }
        .section-title { font-size: 20px; color: #10263b; border-bottom: 2px solid #dee2e6; padding-bottom: 12px; margin-bottom: 30px; margin-top:0; }
        .logout-link { color: #555; text-decoration: none; font-size: 14px; transition: all 0.2s ease; }
        .logout-link:hover { color: #842029; text-decoration: underline; }

        /* Admin 专属统一组件 */
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .moodle-table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: left; margin-top: 10px; }
        .moodle-table th, .moodle-table td { padding: 12px 15px; border-bottom: 1px solid #dee2e6; }
        .moodle-table th { background-color: #f8f9fa; color: #10263b; font-weight: bold; }
        .moodle-table tbody tr:hover { background-color: #f1f3f5; }
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-right: 15px; box-sizing: border-box; width: 250px;}
        .moodle-form-input:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .moodle-btn-submit:hover { background-color: #0d1e2e; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="manage_students.php">Students</a>
                <a href="manage_internships.php">Internships</a>
                <a href="manage_users.php" class="active-link">Users</a>
            </div>
        </div>
        <div class="nav-right-white">
            <a href="../logout.php" class="logout-link">Log out</a>
            <div class="user-avatar"><?= $initial ?></div>
        </div>
    </nav>

    <div class="moodle-dashboard-container" style="margin-top: 30px;">
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Admin View)</h1>

        <?php if ($success_msg): ?> <div class="alert-success"><?= $success_msg ?></div> <?php endif; ?>
        <?php if ($error_msg): ?> <div class="alert-danger"><?= $error_msg ?></div> <?php endif; ?>

        <div class="moodle-card-white">
            <h2 class="section-title">User Management</h2>

            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Add New Assessor Account</label>
                <form method="POST" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="username" class="moodle-form-input" placeholder="Username (e.g., Dr_Smith)" required>
                    <input type="password" name="password" class="moodle-form-input" placeholder="Password" required>
                    <button type="submit" name="add_user" class="moodle-btn-submit">+ Add Assessor</button>
                </form>
            </div>

            <table class="moodle-table">
                <thead>
                    <tr><th>User ID</th><th>Username</th><th>Role</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assessors as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>