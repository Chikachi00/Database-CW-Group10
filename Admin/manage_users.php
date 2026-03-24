<?php
// Admin/manage_users.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../Includes/db_connect.php';

$msg = '';

// 添加 Assessor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // 实际项目中应加密，作业环境配合SQL假数据直存
    try {
        $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:user, :pass, 'Assessor')");
        $stmt->execute(['user' => $username, 'pass' => $password]);
        $msg = "<p style='color:green;'>Assessor added successfully!</p>";
    } catch (PDOException $e) {
        $msg = "<p style='color:red;'>Error: Username might already exist.</p>";
    }
}

// 获取所有 Assessor 列表
$stmt = $pdo->query("SELECT * FROM Users WHERE role = 'Assessor' ORDER BY user_id DESC");
$assessors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Assessors - Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; }
        .nav-bar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-bar">
        <strong>Admin Dashboard</strong> | 
        <a href="manage_students.php">Manage Students</a> | 
        <a href="manage_users.php">Manage Assessors</a> |
        <a href="manage_internships.php">Assign Internships</a> |
        <a href="../view_results.php">View Results</a> | 
        <a href="../logout.php" style="color: red; float: right;">Logout</a>
    </div>

    <h2>Manage Assessors</h2>
    <?= $msg ?>

    <fieldset>
        <legend>Add New Assessor</legend>
        <form method="POST">
            <input type="text" name="username" placeholder="Username (e.g., Dr_Smith)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_user" class="btn">Add Assessor</button>
        </form>
    </fieldset>

    <table>
        <tr><th>User ID</th><th>Username</th><th>Role</th></tr>
        <?php foreach ($assessors as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['user_id']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>