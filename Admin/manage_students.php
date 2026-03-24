<?php
// Admin/manage_students.php
session_start();

// 1. 权限防线：如果没登录，或者不是 Admin，直接踢走！
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 2. 连接数据库 (注意路径 ../)
require_once '../Includes/db_connect.php';

$success_msg = '';
$error_msg = '';

// 3. 处理【添加学生】的表单提交 (POST 请求)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $programme = $_POST['programme'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Students (student_id, student_name, programme) VALUES (:id, :name, :prog)");
        $stmt->execute(['id' => $student_id, 'name' => $student_name, 'prog' => $programme]);
        $success_msg = "Student added successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error adding student: It might already exist.";
    }
}

// 4. 处理【删除学生】的请求 (GET 请求)
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Students WHERE student_id = :id");
        $stmt->execute(['id' => $_GET['delete_id']]);
        header("Location: manage_students.php"); // 刷新页面
        exit();
    } catch (PDOException $e) {
        $error_msg = "Cannot delete student. They might have internship records.";
    }
}

// 5. 从数据库获取【所有学生列表】准备在下方 HTML 表格中展示
$stmt = $pdo->query("SELECT * FROM Students ORDER BY student_id ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students - Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .nav-bar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-bar">
        <strong>Admin Dashboard</strong> | 
        <a href="manage_students.php">Manage Students</a> | 
        <a href="manage_users.php">Manage Assessors</a> |
        <a href="../view_results.php">View Results</a> | 
        <a href="../logout.php" style="color: red; float: right;">Logout</a>
    </div>

    <h2>Manage Students</h2>
    
    <?php if ($success_msg) echo "<p style='color:green;'>$success_msg</p>"; ?>
    <?php if ($error_msg) echo "<p style='color:red;'>$error_msg</p>"; ?>

    <fieldset>
        <legend>Add New Student</legend>
        <form method="POST" action="manage_students.php">
            <input type="text" name="student_id" placeholder="Student ID (e.g. S2024006)" required>
            <input type="text" name="student_name" placeholder="Full Name" required>
            <input type="text" name="programme" placeholder="Programme" required>
            <button type="submit" name="add_student" class="btn">Add Student</button>
        </form>
    </fieldset>

    <table>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Programme</th>
            <th>Action</th>
        </tr>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
            <td><?php echo htmlspecialchars($student['programme']); ?></td>
            <td>
                <a href="manage_students.php?delete_id=<?php echo $student['student_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>