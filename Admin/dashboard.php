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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 25px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; display: block; text-decoration: none; color: inherit; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #7a327e; }
        .stat-icon { font-size: 36px; margin-bottom: 10px; }
        .stat-number { font-size: 36px; font-weight: bold; color: #10263b; margin: 5px 0; }
        .stat-label { font-size: 14px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card.purple .stat-number { color: #7a327e; }
        .stat-card.green .stat-number { color: #0f5132; }
        .stat-card.orange .stat-number { color: #b54708; }
        .stat-hint { font-size: 11px; color: #7a327e; margin-top: 8px; opacity: 0; transition: opacity 0.2s; }
        .stat-card:hover .stat-hint { opacity: 1; }
        
        .progress-bar-bg { background-color: #e9ecef; border-radius: 10px; height: 30px; overflow: hidden; margin-top: 15px; }
        .progress-bar-fill { background: linear-gradient(90deg, #10263b, #7a327e); height: 100%; color: white; display: flex; align-items: center; padding-left: 15px; font-weight: bold; transition: width 0.6s ease; }
        
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }

        /* Help & Rubric Modal Styles */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
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

    <div id="rubricModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box" style="max-width: 650px;">
            <div class="moodle-modal-header">
                <h2>Admin Help & Grading Rubric</h2>
                <span class="moodle-close-x" id="closeRubricModalX">&times;</span>
            </div>
            <div class="moodle-modal-body">
                <div style="background-color: #e8f0fe; border-left: 4px solid #10263b; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                    <strong style="color: #10263b; font-size: 16px;">Need Assistance?</strong><br>
                    <span style="color: #555; font-size: 14px;">If you encounter unexpected behavior, system crashes, issues with deleting linked records, or need database maintenance, please contact:</span>
                    <div style="margin-top: 8px;">
                        <i class="fas fa-chalkboard-teacher"></i> <a href="https://www.nottingham.edu.my/computer-mathematical-sciences/People/chyecheah.tan" target="_blank" class="admin-link">TAN CHYE CHEAH</a><br>
                        &#9993; <a href="mailto:ChyeCheah.Tan@nottingham.edu.my" class="admin-link">ChyeCheah.Tan@nottingham.edu.my</a>
                    </div>
                </div>

                <p><strong>System Guidelines & Potential Issues:</strong></p>
                <ul style="color: #555; margin-bottom: 15px; font-size: 14px;">
                    <li><strong>Record Deletion:</strong> You cannot delete a student or assessor if they have existing internship records linked to them.</li>
                    <li><strong>Duplicate Entries:</strong> The system prevents adding users or students with IDs/Usernames that already exist.</li>
                    <li><strong>Data Integrity:</strong> All modifications are permanently saved to maintain accurate internship records.</li>
                </ul>

                <p><strong>Reference: Assessment Weightages (Fixed):</strong></p>
                <div style="background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 20px;">
                    <ul style="column-count: 2; column-gap: 20px; margin: 0; padding-left: 20px; color: #333; font-size: 14px;">
                        <li style="margin-bottom: 8px;">Tasks/Projects: <strong>10%</strong></li>
                        <li style="margin-bottom: 8px;">Health & Safety: <strong>10%</strong></li>
                        <li style="margin-bottom: 8px;">Connectivity/Theory: <strong>10%</strong></li>
                        <li style="margin-bottom: 8px;">Report Presentation: <strong>15%</strong></li>
                        <li style="margin-bottom: 8px;">Clarity of Language: <strong>10%</strong></li>
                        <li style="margin-bottom: 8px;">Lifelong Learning: <strong>15%</strong></li>
                        <li style="margin-bottom: 8px;">Project Management: <strong>15%</strong></li>
                        <li style="margin-bottom: 8px;">Time Management: <strong>15%</strong></li>
                    </ul>
                </div>
            </div>
            <div class="moodle-modal-footer">
                <button id="closeRubricModalBtn" class="moodle-btn-submit" style="margin-top:0; padding: 8px 20px;">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Help & Rubric Modal
        var rubricModal = document.getElementById("rubricModal");
        var openRubricBtn = document.getElementById("openRubricModalBtn");
        var closeRubricX = document.getElementById("closeRubricModalX");
        var closeRubricBtn = document.getElementById("closeRubricModalBtn");
        
        if(openRubricBtn) openRubricBtn.onclick = function() { rubricModal.style.display = "flex"; }
        if(closeRubricX) closeRubricX.onclick = function() { rubricModal.style.display = "none"; }
        if(closeRubricBtn) closeRubricBtn.onclick = function() { rubricModal.style.display = "none"; }

        // Close modal when clicking outside of it
        window.onclick = function(event) { 
            if (event.target == rubricModal) rubricModal.style.display = "none";
        }
    </script>

</body>
</html>