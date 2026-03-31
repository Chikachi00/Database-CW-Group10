<?php
// Admin/manage_internships.php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_internship'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Internships (student_id, assessor_id, company_name, other_details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['student_id'], $_POST['assessor_id'], $_POST['company'], $_POST['details']]);
        $success_msg = "Internship assigned successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error assigning internship. Student might already be assigned.";
    }
}

$students = $pdo->query("SELECT student_id, student_name FROM Students")->fetchAll();
$assessors = $pdo->query("SELECT user_id, username FROM Users WHERE role = 'Assessor'")->fetchAll();

$sql = "SELECT i.internship_id, s.student_name, u.username as assessor_name, i.company_name 
        FROM Internships i 
        JOIN Students s ON i.student_id = s.student_id 
        JOIN Users u ON i.assessor_id = u.user_id";
$internships = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Internships - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-bottom: 15px; width: 100%; box-sizing: border-box;}
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
                <a href="manage_internships.php" class="active-link">Internships</a>
                <a href="manage_users.php">Users</a>
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
            <h2 class="section-title">Internship Management</h2>
            
            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Assign Internship to Student</label>
                <form method="POST">
                    <select name="student_id" class="moodle-form-input" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['student_id'] ?>"><?= $s['student_name'] ?> (<?= $s['student_id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="assessor_id" class="moodle-form-input" required>
                        <option value="">-- Select Assessor --</option>
                        <?php foreach ($assessors as $a): ?>
                            <option value="<?= $a['user_id'] ?>"><?= $a['username'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="company" class="moodle-form-input" placeholder="Company Name" required>
                    <input type="text" name="details" class="moodle-form-input" placeholder="Other Details (Optional)">
                    <button type="submit" name="assign_internship" class="moodle-btn-submit">Assign Internship</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="moodle-table">
                    <thead>
                        <tr><th>Assignment ID</th><th>Student</th><th>Assessor</th><th>Company</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($internships as $row): ?>
                        <tr>
                            <td><?= $row['internship_id'] ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['assessor_name']) ?></td>
                            <td><?= htmlspecialchars($row['company_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>