<?php
// Admin/manage_students.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

// 获取动态头像
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $programme = trim($_POST['programme']);

    try {
        $stmt = $pdo->prepare("INSERT INTO Students (student_id, student_name, programme) VALUES (:id, :name, :prog)");
        $stmt->execute(['id' => $student_id, 'name' => $student_name, 'prog' => $programme]);
        $success_msg = "Student added successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error adding student: ID might already exist in the system.";
    }
}

if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Students WHERE student_id = :id");
        $stmt->execute(['id' => $_GET['delete_id']]);
        header("Location: manage_students.php"); 
        exit();
    } catch (PDOException $e) {
        $error_msg = "Cannot delete student. They might have internship records linked to them.";
    }
}

$stmt = $pdo->query("SELECT * FROM Students ORDER BY student_id ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - Admin Dashboard</title>
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
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-right: 15px; box-sizing: border-box; width: 220px;}
        .moodle-form-input:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .moodle-btn-submit:hover { background-color: #0d1e2e; }
        .btn-danger { background-color: #dc3545; color: white; border: none; padding: 8px 15px; font-size: 13px; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold;}
        .btn-danger:hover { background-color: #c82333; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="manage_students.php" class="active-link">Students</a>
                <a href="manage_internships.php">Internships</a>
                <a href="manage_users.php">Users</a>
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
            <h2 class="section-title">Student Management</h2>

            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Register New Student</label>
                <form id="addStudentForm" method="POST" action="manage_students.php" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="student_id" name="student_id" class="moodle-form-input" placeholder="ID (e.g. 2026001)" required>
                    <input type="text" id="student_name" name="student_name" class="moodle-form-input" placeholder="Full Name" required>
                    <input type="text" id="programme" name="programme" class="moodle-form-input" placeholder="Programme" required>
                    <button type="submit" name="add_student" class="moodle-btn-submit">+ Add Student</button>
                </form>
            </div>

            <table class="moodle-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Programme</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['student_id']); ?></td>
                        <td><?= htmlspecialchars($student['student_name']); ?></td>
                        <td><?= htmlspecialchars($student['programme']); ?></td>
                        <td>
                            <a href="manage_students.php?delete_id=<?= $student['student_id']; ?>" class="btn-danger" onclick="return confirm('WARNING: Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.getElementById('addStudentForm').addEventListener('submit', function(event) {
        const studentId = document.getElementById('student_id').value.trim();
        const studentName = document.getElementById('student_name').value.trim();
        if (studentId.length < 3) {
            alert("Error: Student ID must be at least 3 characters long.");
            event.preventDefault(); return;
        }
        const nameRegex = /^[A-Za-z\s]+$/;
        if (!nameRegex.test(studentName)) {
            alert("Error: Student Name should only contain letters and spaces.");
            event.preventDefault(); return;
        }
    });
    </script>
</body>
</html>