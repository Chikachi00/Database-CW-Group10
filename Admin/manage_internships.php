<?php
// Admin/manage_internships.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../Includes/db_connect.php';

$msg = '';

// 处理分配实习表单
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_internship'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Internships (student_id, assessor_id, company_name, other_details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['student_id'], $_POST['assessor_id'], $_POST['company'], $_POST['details']]);
        $msg = "<p style='color:green;'>Internship assigned successfully!</p>";
    } catch (PDOException $e) {
        $msg = "<p style='color:red;'>Error assigning internship. Student might already be assigned.</p>";
    }
}

// 获取下拉菜单所需的数据
$students = $pdo->query("SELECT student_id, student_name FROM Students")->fetchAll();
$assessors = $pdo->query("SELECT user_id, username FROM Users WHERE role = 'Assessor'")->fetchAll();

// 获取已分配的实习列表 (使用 JOIN 连表查询，让页面显示名字而不是冷冰冰的 ID)
$sql = "SELECT i.internship_id, s.student_name, u.username as assessor_name, i.company_name 
        FROM Internships i 
        JOIN Students s ON i.student_id = s.student_id 
        JOIN Users u ON i.assessor_id = u.user_id";
$internships = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Internships - Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; }
        .nav-bar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        select, input { padding: 8px; margin-right: 10px; }
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

    <h2>Assign Internship to Student</h2>
    <?= $msg ?>

    <fieldset>
        <legend>New Assignment</legend>
        <form method="POST">
            <select name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['student_id'] ?>"><?= $s['student_name'] ?> (<?= $s['student_id'] ?>)</option>
                <?php endforeach; ?>
            </select>
            
            <select name="assessor_id" required>
                <option value="">-- Select Assessor --</option>
                <?php foreach ($assessors as $a): ?>
                    <option value="<?= $a['user_id'] ?>"><?= $a['username'] ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="company" placeholder="Company Name" required>
            <input type="text" name="details" placeholder="Other Details (Optional)">
            <button type="submit" name="assign_internship" class="btn">Assign</button>
        </form>
    </fieldset>

    <table>
        <tr><th>ID</th><th>Student</th><th>Assessor</th><th>Company</th></tr>
        <?php foreach ($internships as $row): ?>
        <tr>
            <td><?= $row['internship_id'] ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['assessor_name']) ?></td>
            <td><?= htmlspecialchars($row['company_name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>