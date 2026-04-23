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

// Basic queries to get counts
$stmt = $pdo->query("SELECT COUNT(*) as total FROM Students");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_students = $row['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM Users WHERE role = 'Assessor'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_assessors = $row['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM Internships");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_internships = $row['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM Assessments");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_evaluated = $row['total'];

$total_pending = $total_internships - $total_evaluated;

// Calculate average
$stmt_avg = $pdo->query("SELECT AVG(total_score) as avg_score FROM Assessments");
$avg_row = $stmt_avg->fetch(PDO::FETCH_ASSOC);
if ($avg_row['avg_score']) {
    $university_avg = round($avg_row['avg_score'], 2);
} else {
    $university_avg = 0;
}

// Get recent evaluations
$sql_recent = "SELECT s.student_name, s.student_id, a.total_score, u.username as assessor_name
               FROM Assessments a 
               JOIN Internships i ON a.internship_id = i.internship_id
               JOIN Students s ON i.student_id = s.student_id
               JOIN Users u ON i.assessor_id = u.user_id
               ORDER BY a.assessment_id DESC LIMIT 5";
$recent = $pdo->query($sql_recent)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 25px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; display: block; text-decoration: none; color: inherit; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #7a327e; }
        .stat-icon { font-size: 36px; margin-bottom: 10px; color: #1d2125; }
        .stat-number { font-size: 36px; font-weight: bold; color: #10263b; margin: 5px 0; }
        .stat-label { font-size: 14px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card.purple .stat-number { color: #7a327e; }
        .stat-card.green .stat-number { color: #0f5132; }
        .stat-card.orange .stat-number { color: #b54708; }
        .stat-hint { font-size: 11px; color: #7a327e; margin-top: 8px; opacity: 0; transition: opacity 0.2s; }
        .stat-card:hover .stat-hint { opacity: 1; }
        
        .progress-container { position: relative; margin-top: 45px; }
        .progress-marker { position: absolute; bottom: 100%; transform: translateX(-50%); color: #10263b; font-weight: bold; font-size: 15px; padding-bottom: 8px; white-space: nowrap; transition: left 0.6s ease; }
        .progress-triangle { position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #10263b; }
        .progress-bar-bg { background-color: #e9ecef; border-radius: 10px; height: 30px; position: relative; }
        .progress-bar-fill { background: linear-gradient(90deg, #10263b, #7a327e); height: 100%; border-radius: 10px; transition: width 0.6s ease; }
        
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }

        /* 注意这里的 max-width 已经统一改成了 550px */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 550px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
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
                <a id="openRubricModalBtn" style="cursor: pointer;">Help & Rubric</a>
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
            <a href="manage_students.php" class="stat-card" title="Go to Manage Students">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Total Students</div>
                <div class="stat-hint">Click to manage &rarr;</div>
            </a>
            <a href="manage_users.php" class="stat-card" title="Go to Manage Assessors">
                <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-number"><?= $total_assessors ?></div>
                <div class="stat-label">Total Assessors</div>
                <div class="stat-hint">Click to manage &rarr;</div>
            </a>
            <a href="manage_internships.php" class="stat-card" title="Go to Manage Internships">
                <div class="stat-icon"><i class="fas fa-building"></i></div>
                <div class="stat-number"><?= $total_internships ?></div>
                <div class="stat-label">Internships Assigned</div>
                <div class="stat-hint">Click to manage &rarr;</div>
            </a>
            <a href="view_all_results.php" class="stat-card green" title="View all evaluation results">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number"><?= $total_evaluated ?></div>
                <div class="stat-label">Evaluated</div>
                <div class="stat-hint">Click to view results &rarr;</div>
            </a>
            <a href="manage_internships.php" class="stat-card orange" title="View pending internships">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?= $total_pending ?></div>
                <div class="stat-label">Pending</div>
                <div class="stat-hint">Click to view assignments &rarr;</div>
            </a>
            <a href="view_all_results.php" class="stat-card purple" title="View all evaluation results">
                <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="stat-number"><?= $university_avg ?></div>
                <div class="stat-label">University Average</div>
                <div class="stat-hint">Click to view results &rarr;</div>
            </a>
        </div>

        <div class="moodle-card-white">
            <h2 class="section-title">Assessment Progress</h2>
            <p style="color:#555; margin-bottom:5px;">Overall completion of assigned internships:</p>
            <?php 
                if ($total_internships > 0) {
                    $percent = round(($total_evaluated / $total_internships) * 100);
                } else {
                    $percent = 0;
                }
            ?>
            <div class="progress-container">
                <div class="progress-marker" style="left: <?= $percent ?>%;">
                    <?= $percent ?>% Complete
                    <div class="progress-triangle"></div>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
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

    <?php include 'admin_help_modal.php'; ?>

</body>
</html>