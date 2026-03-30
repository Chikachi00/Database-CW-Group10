<?php
// Admin/manage_students.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

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
        /* 临时内置样式，让它比原来好看一点，之后可以全部移到 style.css */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #333; margin: 0; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 900px; margin: 40px auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .nav-bar { margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;}
        .nav-bar a { text-decoration: none; color: #007bff; margin-right: 15px; font-weight: 500;}
        .nav-bar a:hover { text-decoration: underline; }
        form input { padding: 10px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; width: 200px; }
        .btn { padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; padding: 6px 12px; font-size: 14px; }
        .btn-danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #e9ecef; padding: 12px; text-align: left; }
        th { background-color: #343a40; color: white; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-bar">
        <div>
            <strong style="font-size: 18px; margin-right: 20px;">Admin Panel</strong>
            <a href="manage_students.php">Students</a>
            <a href="manage_users.php">Assessors</a>
            <a href="manage_internships.php">Assign Internships</a>
            <a href="../view_results.php">Results</a>
        </div>
        <a href="../logout.php" style="color: #dc3545;">Logout</a>
    </div>

    <h2>Student Directory</h2>
    
    <?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
    <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

    <fieldset style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <legend style="font-weight: bold; color: #007bff; padding: 0 5px;">Register New Student</legend>
        <form id="addStudentForm" method="POST" action="manage_students.php">
            <input type="text" id="student_id" name="student_id" placeholder="ID (e.g. S1234)" required>
            <input type="text" id="student_name" name="student_name" placeholder="Full Name" required>
            <input type="text" id="programme" name="programme" placeholder="Programme" required>
            <button type="submit" name="add_student" class="btn">+ Add Student</button>
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
                <a href="manage_students.php?delete_id=<?php echo $student['student_id']; ?>" class="btn btn-danger" onclick="return confirm('WARNING: Are you sure you want to delete this student?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
document.getElementById('addStudentForm').addEventListener('submit', function(event) {
    const studentId = document.getElementById('student_id').value.trim();
    const studentName = document.getElementById('student_name').value.trim();

    // 验证 1：学号不能太短
    if (studentId.length < 3) {
        alert("Error: Student ID must be at least 3 characters long.");
        event.preventDefault(); // 阻止表单提交给 PHP
        return;
    }

    // 验证 2：姓名只能包含字母和空格 (简单的正则)
    const nameRegex = /^[A-Za-z\s]+$/;
    if (!nameRegex.test(studentName)) {
        alert("Error: Student Name should only contain letters and spaces.");
        event.preventDefault(); // 阻止表单提交给 PHP
        return;
    }
});
</script>

</body>
</html>