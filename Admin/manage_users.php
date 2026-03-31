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
    <link rel="stylesheet" href="../style.css">
    <style>
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-right: 15px; box-sizing: border-box; width: 250px;}
        .moodle-form-input:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
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
                <a href="view_all_results.php">Results</a> </div>
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

            <div class="table-responsive">
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
    </div>

</body>
</html>