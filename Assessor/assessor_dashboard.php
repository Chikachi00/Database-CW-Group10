<?php
// Assessor/assessor_dashboard.php - Assessor overview dashboard (Admin Style)
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

$assessor_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

// Collect key statistics for this specific assessor
// 1. Total students assigned
$stmt_assigned = $pdo->prepare("SELECT COUNT(*) FROM Internships WHERE assessor_id = :assessor_id");
$stmt_assigned->execute(['assessor_id' => $assessor_id]);
$total_assigned = $stmt_assigned->fetchColumn();

// 2. Total evaluated
$stmt_eval = $pdo->prepare("SELECT COUNT(*) FROM Assessments a JOIN Internships i ON a.internship_id = i.internship_id WHERE i.assessor_id = :assessor_id");
$stmt_eval->execute(['assessor_id' => $assessor_id]);
$total_evaluated = $stmt_eval->fetchColumn();

// 3. Pending
$total_pending = $total_assigned - $total_evaluated;

// 4. Assessor's average score given
$stmt_avg = $pdo->prepare("SELECT AVG(a.total_score) FROM Assessments a JOIN Internships i ON a.internship_id = i.internship_id WHERE i.assessor_id = :assessor_id");
$stmt_avg->execute(['assessor_id' => $assessor_id]);
$avg_raw = $stmt_avg->fetchColumn();
$assessor_avg = $avg_raw ? number_format($avg_raw, 2) : "0.00";

// 5. Recent 5 evaluations by this assessor
$stmt_recent = $pdo->prepare("SELECT s.student_name, s.student_id, a.total_score 
                              FROM Assessments a 
                              JOIN Internships i ON a.internship_id = i.internship_id
                              JOIN Students s ON i.student_id = s.student_id
                              WHERE i.assessor_id = :assessor_id
                              ORDER BY a.assessment_id DESC LIMIT 5");
$stmt_recent->execute(['assessor_id' => $assessor_id]);
$recent_evals = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Calculate assessment progress percent
$percent = $total_assigned > 0 ? round(($total_evaluated / $total_assigned) * 100) : 0; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Assessor</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modified CSS for Unified Dark Center-Icon Style (Admin Style) */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 25px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; display: block; text-decoration: none; color: inherit; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #7a327e; }
        
        /* Force icons to a dark, uniform color, center-aligned within the card, exactly like Admin dashboard */
        .stat-icon { font-size: 36px; margin-bottom: 10px; color: #1d2125; }
        
        .stat-number { font-size: 36px; font-weight: bold; color: #10263b; margin: 5px 0; }
        .stat-label { font-size: 14px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Apply colors to numbers only, as per Admin style */
        .stat-card.purple .stat-number { color: #7a327e; }
        .stat-card.green .stat-number { color: #0f5132; }
        .stat-card.orange .stat-number { color: #b54708; }
        
        .stat-hint { font-size: 11px; color: #7a327e; margin-top: 8px; opacity: 0; transition: opacity 0.2s; }
        .stat-card:hover .stat-hint { opacity: 1; }
        
        /* 修改进度条的 CSS 布局：背景使用相对定位，文字使用绝对定位悬浮靠左 */
        .progress-bar-bg { background-color: #e9ecef; border-radius: 10px; height: 30px; margin-top: 15px; position: relative; }
        .progress-bar-fill { background: linear-gradient(90deg, #10263b, #7a327e); height: 100%; border-radius: 10px; transition: width 0.6s ease; }
        .progress-text { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: flex-start; padding-left: 15px; font-weight: bold; white-space: nowrap; pointer-events: none; }
        
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }

        /* Moodle Card White styles (Shared Admin Style) */
        .moodle-card-white { background-color: #ffffff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 40px; margin-bottom: 30px;}
        .section-title { font-size: 20px; color: #10263b; border-bottom: 2px solid #dee2e6; padding-bottom: 12px; margin-bottom: 30px; margin-top:0; }
        
        /* Table styles (Shared style) */
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 20px; border: 1px solid #dee2e6; border-radius: 4px; }
        .moodle-table { width: 100%; border-collapse: collapse; font-size: 13.5px; white-space: nowrap; }
        .moodle-table th, .moodle-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .moodle-table th { background-color: #f8f9fa; color: #10263b; cursor: pointer; user-select: none; }
        .moodle-table tbody tr { transition: background-color 0.2s; }
        .moodle-table tbody tr:hover { background-color: #f1f3f5; }

        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .moodle-btn-submit:hover { background-color: #0d1e2e; }

        /* Shared Modal Styles */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="assessor_dashboard.php" class="active-link">Dashboard</a>
                <a href="evaluate_student.php">Evaluate</a>
                <a href="submit_marks.php">View Results</a>
                <a id="openRubricModalBtn" style="cursor: pointer;">Grading Rubric & Help</a>
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
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?= $total_assigned ?></div>
                <div class="stat-label">My Assigned Students</div>
                <div class="stat-hint">Total workload</div>
            </div>

            <a href="evaluate_student.php" class="stat-card orange" title="Go to Evaluate Student">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?= $total_pending ?></div>
                <div class="stat-label">Pending Evaluations</div>
                <div class="stat-hint">Click to evaluate &rarr;</div>
            </a>

            <a href="submit_marks.php" class="stat-card green" title="View submitted results">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number"><?= $total_evaluated ?></div>
                <div class="stat-label">Completed</div>
                <div class="stat-hint">Click to view results &rarr;</div>
            </a>

            <div class="stat-card purple">
                <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="stat-number"><?= $assessor_avg ?></div>
                <div class="stat-label">My Average Given</div>
                <div class="stat-hint">Average score awarded</div>
            </div>
        </div>

        <div class="moodle-card-white">
            <h2 class="section-title">My Assessment Progress</h2>
            <p style="color:#555; margin-bottom:5px;">Completion of your assigned internships:</p>
            <?php 
                $percent = $total_assigned > 0 ? round(($total_evaluated / $total_assigned) * 100) : 0; 
            ?>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
                <div class="progress-text" style="color: <?= $percent > 50 ? '#ffffff' : '#10263b' ?>;">
                    <?= $percent ?>% Complete
                </div>
            </div>
            <p style="color:#6c757d; font-size:13px; margin-top:10px;">You have evaluated <?= $total_evaluated ?> out of <?= $total_assigned ?> students.</p>
            
            <?php if($total_pending > 0): ?>
                <a href="evaluate_student.php" class="moodle-btn-submit" style="display:inline-block; margin-top:15px; text-decoration:none;">Continue Evaluating &rarr;</a>
            <?php endif; ?>
        </div>

        <div class="moodle-card-white" style="margin-top: 25px;">
            <h2 class="section-title">My Recent Evaluations</h2>
            <?php if (count($recent_evals) == 0): ?>
                <p style="color:#666; padding:20px 0;">You have not submitted any evaluations yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="moodle-table">
                        <thead>
                            <tr><th>Student ID</th><th>Student Name</th><th>Final Score</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_evals as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                                <td><strong style="color:#7a327e;"><?= number_format($r['total_score'], 2) ?></strong> / 100</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align:right; margin-top:15px;">
                    <a href="submit_marks.php" class="admin-link">View full details &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'assessor_help_modal.php'; ?>

</body>
</html>