<?php
// Admin/dashboard.php - Admin overview dashboard with statistics
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

// Collect key statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM Students")->fetchColumn();
$total_assessors = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'Assessor'")->fetchColumn();
$total_internships = $pdo->query("SELECT COUNT(*) FROM Internships")->fetchColumn();
$total_evaluated = $pdo->query("SELECT COUNT(*) FROM Assessments")->fetchColumn();
$total_pending = $total_internships - $total_evaluated;

// University average
$avg_row = $pdo->query("SELECT AVG(total_score) as avg FROM Assessments")->fetch(PDO::FETCH_ASSOC);
$university_avg = $avg_row['avg'] ? round($avg_row['avg'], 2) : 0;

// Recent 5 evaluations
$recent = $pdo->query("SELECT s.student_name, s.student_id, a.total_score, u.username as assessor_name
                       FROM Assessments a 
                       JOIN Internships i ON a.internship_id = i.internship_id
                       JOIN Students s ON i.student_id = s.student_id
                       JOIN Users u ON i.assessor_id = u.user_id
                       ORDER BY a.assessment_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 25px; text-align: center; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .stat-icon { font-size: 36px; margin-bottom: 10px; }
        .stat-number { font-size: 36px; font-weight: bold; color: #10263b; margin: 5px 0; }
        .stat-label { font-size: 14px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card.purple .stat-number { color: #7a327e; }
        .stat-card.green .stat-number { color: #0f5132; }
        .stat-card.orange .stat-number { color: #b54708; }
        
        .progress-bar-bg { background-color: #e9ecef; border-radius: 10px; height: 30px; overflow: hidden; margin-top: 15px; }
        .progress-bar-fill { background: linear-gradient(90deg, #10263b, #7a327e); height: 100%; color: white; display: flex; align-items: center; padding-left: 15px; font-weight: bold; transition: width 0.6s ease; }
        
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="dashboard.php" class="active-link">Dashboard</a>
                <a href="manage_students.php">Students</a>
                <a href="manage_internships.php">Internships</a>
                <a href="manage_users.php">Users</a>
                <a href="view_all_results.php">Results</a> 
            </div>
        </div>
        <div class="nav-right-white">
            <a href="../logout.php" class="logout-link">Log out</a>
            <div class="user-avatar"><?= $initial ?></div>
        </div>
    </nav>

    <div class="moodle-dashboard-container" style="margin-top: 30px;">
        <h1 class="moodle-page-header" style="margin-left: 0;">Welcome back, <?= htmlspecialchars($username) ?>!</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">&#128218;</div>
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#128100;</div>
                <div class="stat-number"><?= $total_assessors ?></div>
                <div class="stat-label">Total Assessors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#127970;</div>
                <div class="stat-number"><?= $total_internships ?></div>
                <div class="stat-label">Internships Assigned</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">&#9989;</div>
                <div class="stat-number"><?= $total_evaluated ?></div>
                <div class="stat-label">Evaluated</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon">&#8987;</div>
                <div class="stat-number"><?= $total_pending ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">&#127942;</div>
                <div class="stat-number"><?= $university_avg ?></div>
                <div class="stat-label">University Average</div>
            </div>
        </div>

        <div class="moodle-card-white">
            <h2 class="section-title">Assessment Progress</h2>
            <p style="color:#555; margin-bottom:5px;">Overall completion of assigned internships:</p>
            <?php 
                $percent = $total_internships > 0 ? round(($total_evaluated / $total_internships) * 100) : 0; 
            ?>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $percent ?>%;">
                    <?= $percent ?>% Complete
                </div>
            </div>
            <p style="color:#6c757d; font-size:13px; margin-top:10px;"><?= $total_evaluated ?> of <?= $total_internships ?> internships have been evaluated.</p>
        </div>

        <div class="moodle-card-white" style="margin-top: 25px;">
            <h2 class="section-title">Recent Evaluations</h2>
            <?php if (count($recent) == 0): ?>
                <p style="color:#666; padding:20px 0;">No evaluations have been submitted yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="moodle-table">
                        <thead>
                            <tr><th>Student ID</th><th>Student Name</th><th>Assessor</th><th>Final Score</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['assessor_name']) ?></td>
                                <td><strong style="color:#7a327e;"><?= number_format($r['total_score'], 2) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align:right; margin-top:15px;">
                    <a href="view_all_results.php" class="admin-link">View all results &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>