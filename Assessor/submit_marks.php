<?php
// Assessor/submit_marks.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

$assessor_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

$success_message = "";
$error_message = "";

// receive form data, calculate total score,save to database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['internship_id'])) {
    $internship_id = $_POST['internship_id'];
    $task = floatval($_POST['task_score']);
    $health = floatval($_POST['health_safety_score']);
    $connectivity = floatval($_POST['connectivity_score']);
    $report = floatval($_POST['report_score']);
    $clarity = floatval($_POST['clarity_score']);
    $lifelong = floatval($_POST['lifelong_score']);
    $project = floatval($_POST['project_mgmt_score']);
    $time = floatval($_POST['time_mgmt_score']);
    $comments = trim($_POST['qualitative_comments']);

    // Server-side validation: all scores must be within 0-100 range
    $all_scores = [
        'Undertaking Tasks/Projects' => $task,
        'Health and Safety' => $health,
        'Connectivity/Theory' => $connectivity,
        'Report Presentation' => $report,
        'Clarity of Language' => $clarity,
        'Lifelong Learning' => $lifelong,
        'Project Management' => $project,
        'Time Management' => $time
    ];
    $invalid_fields = [];
    foreach ($all_scores as $label => $val) {
        if ($val < 0 || $val > 100) {
            $invalid_fields[] = $label;
        }
    }

    if (!empty($invalid_fields)) {
        $error_message = "Invalid score range. All scores must be between 0 and 100. Please check: <strong>" . htmlspecialchars(implode(', ', $invalid_fields)) . "</strong>.";
    } else if ($comments === '') {
        $error_message = "Qualitative comments are required. Please provide justification for the assigned scores.";
    } else if (!is_numeric($internship_id) || intval($internship_id) <= 0) {
        $error_message = "Invalid student selection. Please go back and select a valid student.";
    } else {
        // Calculate weighted total score
        $raw_total = ($task * 0.10) + ($health * 0.10) + ($connectivity * 0.10) + 
                     ($report * 0.15) + ($clarity * 0.10) + ($lifelong * 0.15) + 
                     ($project * 0.15) + ($time * 0.15);
        $total_score = round($raw_total, 2);

        try {
            $sql_insert = "INSERT INTO Assessments (
                        internship_id, task_score, health_safety_score, connectivity_score, 
                        report_score, clarity_score, lifelong_score, project_mgmt_score, 
                        time_mgmt_score, total_score, qualitative_comments
                    ) VALUES (
                        :id, :task, :health, :conn, :rep, :clar, :life, :proj, :time_m, :total, :comm
                    )";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                'id' => $internship_id, 'task' => $task, 'health' => $health, 'conn' => $connectivity,
                'rep' => $report, 'clar' => $clarity, 'life' => $lifelong, 'proj' => $project,
                'time_m' => $time, 'total' => $total_score, 'comm' => $comments
            ]);
            
            // Redirect after successful submission
            header("Location: submit_marks.php?status=success&score=" . $total_score);
            exit();
        } catch (PDOException $e) {
            $error_message = "Error saving assessment: " . $e->getMessage();
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $success_message = "Assessment submitted successfully! Final Weighted Score: <strong>" . htmlspecialchars($_GET['score']) . " / 100</strong>";
}

// Fetch all evaluated students for this assessor
$sql_evaluated = "SELECT i.internship_id, s.student_id, s.student_name, 
                         a.total_score, a.task_score, a.health_safety_score, 
                         a.connectivity_score, a.report_score, a.clarity_score, 
                         a.lifelong_score, a.project_mgmt_score, a.time_mgmt_score,
                         a.qualitative_comments
                  FROM Internships i
                  JOIN Students s ON i.student_id = s.student_id
                  JOIN Assessments a ON i.internship_id = a.internship_id
                  WHERE i.assessor_id = :assessor_id
                  ORDER BY s.student_id ASC";
$stmt_evaluated = $pdo->prepare($sql_evaluated);
$stmt_evaluated->execute(['assessor_id' => $assessor_id]);
$evaluated_students = $stmt_evaluated->fetchAll(PDO::FETCH_ASSOC);

// Calculate class average
$total_students_evaluated = count($evaluated_students);
$average_score = 0;
if ($total_students_evaluated > 0) {
    $sum = 0;
    foreach ($evaluated_students as $student) { $sum += $student['total_score']; }
    $average_score = round($sum / $total_students_evaluated, 2);
}

// Helper: return CSS class based on 5 score grade bands
function getScoreBadgeClass($score) {
    if ($score >= 70) return 'score-tier-1';
    if ($score >= 60) return 'score-tier-2';
    if ($score >= 50) return 'score-tier-3';
    if ($score >= 40) return 'score-tier-4';
    return 'score-tier-5';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Results - Assessor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; transition: color 0.2s ease; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }
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
        
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }

        .moodle-search-bar { width: 100%; padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .moodle-search-bar:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 20px; border: 1px solid #dee2e6; border-radius: 4px; }
        .moodle-table { width: 100%; border-collapse: collapse; font-size: 13.5px; white-space: nowrap; }
        .moodle-table th, .moodle-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .moodle-table th { background-color: #f8f9fa; color: #10263b; cursor: pointer; user-select: none; }
        .moodle-table tbody tr { cursor: pointer; transition: background-color 0.2s ease; }
        .moodle-table tbody tr:hover { background-color: #f1f3f5; }
        
        .th-content { display: flex; align-items: center; justify-content: space-between; }
        .sort-icons { display: flex; flex-direction: column; font-size: 9px; margin-left: 8px; color: #ced4da; }
        .active-sort { color: #7a327e; }

        /* Modal Styles - 增加了 backdrop-filter: blur(3px); */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none;}
        .moodle-btn-submit:hover { background-color: #0d1e2e; }

        /* Stats Dashboard */
        .stats-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; }
        .stats-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; display: flex; align-items: center; gap: 15px; white-space: nowrap; }
        .stats-label { font-size: 15px; color: #555; font-weight: bold; }
        .stats-value { font-size: 24px; color: #7a327e; font-weight: bold; }
        
        .detail-raw-score { font-size: 16px; color: #10263b; }
        
        /* 5-Tier Score Grade Badges (Unified Pastel/Muted Colors) */
        .score-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-weight: bold; font-size: 14px; min-width: 60px; text-align: center; border: 1px solid transparent; }
        .score-tier-1 { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; }
        .score-tier-2 { background-color: #e0e7ff; color: #1e3a8a; border-color: #c7d2fe; }
        .score-tier-3 { background-color: #cfe2ff; color: #084298; border-color: #b6d4fe; }
        .score-tier-4 { background-color: #fff3cd; color: #856404; border-color: #ffe69c; }
        .score-tier-5 { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }
        
        .logout-link { color: #555; text-decoration: none; font-size: 14px; transition: all 0.2s ease; }
        .logout-link:hover { color: #842029; text-decoration: underline; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="assessor_dashboard.php">Dashboard</a>
                <a href="evaluate_student.php">Evaluate</a>
                <a href="submit_marks.php" class="active-link">View Results</a>
                <a id="openRubricModalBtn" style="cursor: pointer;">Grading Rubric & Help</a>
            </div>
        </div>
        <div class="nav-right-white">
            <a href="../logout.php" class="logout-link">Log out</a>
            <div class="user-avatar"><?= $initial ?></div>
        </div>
    </nav>

    <div class="moodle-dashboard-container" style="margin-top: 30px;">
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Assessor View)</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="moodle-card-white">
            <h2 class="section-title">My Evaluated Students (Detailed Scores)</h2>
            
            <?php if ($total_students_evaluated == 0): ?>
                <input type="text" id="searchInput" class="moodle-search-bar" placeholder="Search ID or Name..." disabled>
                <div style="padding: 30px; text-align: center; color: #666; margin-top:20px;">No students have been evaluated yet.</div>
            <?php else: ?>
                <div class="stats-dashboard">
                    <input type="text" id="searchInput" class="moodle-search-bar" placeholder="Search ID or Name..." onkeyup="filterResults()" style="flex-grow: 1;">
                    <div class="stats-box">
                        <span class="stats-label">Class Average:</span>
                        <span class="stats-value"><?= number_format($average_score, 2) ?></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="moodle-table" id="resultsTable">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0, 'str')"><div class="th-content">Student ID <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(1, 'str')"><div class="th-content">Name <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(2, 'num')"><div class="th-content">Tasks <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(3, 'num')"><div class="th-content">H&S <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(4, 'num')"><div class="th-content">Theory <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(5, 'num')"><div class="th-content">Report <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(6, 'num')"><div class="th-content">Clarity <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(7, 'num')"><div class="th-content">Lifelong <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(8, 'num')"><div class="th-content">Proj. Mgmt <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(9, 'num')"><div class="th-content">Time Mgmt <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(10, 'num')" style="min-width: 115px; padding-right: 25px;"><div class="th-content">Final Score <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($evaluated_students as $row): ?>
                                <tr class="evaluated-row" title="Double-click to view full details and comments"
                                    data-id="<?= htmlspecialchars($row['student_id']) ?>"
                                    data-name="<?= htmlspecialchars($row['student_name']) ?>"
                                    data-task="<?= htmlspecialchars($row['task_score']) ?>"
                                    data-health="<?= htmlspecialchars($row['health_safety_score']) ?>"
                                    data-conn="<?= htmlspecialchars($row['connectivity_score']) ?>"
                                    data-report="<?= htmlspecialchars($row['report_score']) ?>"
                                    data-clarity="<?= htmlspecialchars($row['clarity_score']) ?>"
                                    data-life="<?= htmlspecialchars($row['lifelong_score']) ?>"
                                    data-proj="<?= htmlspecialchars($row['project_mgmt_score']) ?>"
                                    data-time="<?= htmlspecialchars($row['time_mgmt_score']) ?>"
                                    data-total="<?= number_format($row['total_score'], 2) ?>"
                                    data-comments="<?= htmlspecialchars($row['qualitative_comments']) ?>">
                                    
                                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                                    <td><strong><?= htmlspecialchars($row['student_name']) ?></strong></td>
                                    
                                    <td><?= number_format($row['task_score'] * 0.10, 2) ?>/10.00</td>
                                    <td><?= number_format($row['health_safety_score'] * 0.10, 2) ?>/10.00</td>
                                    <td><?= number_format($row['connectivity_score'] * 0.10, 2) ?>/10.00</td>
                                    <td><?= number_format($row['report_score'] * 0.15, 2) ?>/15.00</td>
                                    <td><?= number_format($row['clarity_score'] * 0.10, 2) ?>/10.00</td>
                                    <td><?= number_format($row['lifelong_score'] * 0.15, 2) ?>/15.00</td>
                                    <td><?= number_format($row['project_mgmt_score'] * 0.15, 2) ?>/15.00</td>
                                    <td><?= number_format($row['time_mgmt_score'] * 0.15, 2) ?>/15.00</td>
                                    
                                    <td><span class="score-badge <?= getScoreBadgeClass($row['total_score']) ?>"><?= number_format($row['total_score'], 2) ?></span> / 100.00</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="studentDetailsModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box" style="max-width: 700px;">
            <div class="moodle-modal-header">
                <h2>Assessment Details: <span id="detailStudentName" style="color: #7a327e;"></span></h2>
                <span class="moodle-close-x" id="closeDetailsX">&times;</span>
            </div>
            <div class="moodle-modal-body">
                <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #dee2e6; padding-bottom: 15px; margin-bottom: 20px;">
                    <div style="font-size: 16px;"><strong>Student ID:</strong> <span id="detailId"></span></div>
                    <div style="font-size: 16px;"><strong>Final Weighted Score:</strong> <span id="detailTotal" class="score-badge" style="font-size: 18px; padding: 6px 16px;"></span> / 100.00</div>
                </div>
                
                <h4 style="margin-top: 0; color: #10263b;">Weighted Component Scores</h4>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1; margin-bottom: 25px;">
                    <ul style="column-count: 2; column-gap: 30px; margin: 0; padding-left: 20px; color: #555;">
                        <li style="margin-bottom: 8px;">Tasks/Projects: <strong class="detail-raw-score" id="detTask"></strong></li>
                        <li style="margin-bottom: 8px;">Health & Safety: <strong class="detail-raw-score" id="detHealth"></strong></li>
                        <li style="margin-bottom: 8px;">Connectivity/Theory: <strong class="detail-raw-score" id="detConn"></strong></li>
                        <li style="margin-bottom: 8px;">Report Presentation: <strong class="detail-raw-score" id="detReport"></strong></li>
                        <li style="margin-bottom: 8px;">Clarity of Language: <strong class="detail-raw-score" id="detClarity"></strong></li>
                        <li style="margin-bottom: 8px;">Lifelong Learning: <strong class="detail-raw-score" id="detLife"></strong></li>
                        <li style="margin-bottom: 8px;">Project Management: <strong class="detail-raw-score" id="detProj"></strong></li>
                        <li style="margin-bottom: 8px;">Time Management: <strong class="detail-raw-score" id="detTime"></strong></li>
                    </ul>
                </div>

                <h4 style="color: #10263b; margin-bottom: 10px;">Qualitative Comments</h4>
                <div id="detailComments" style="background-color: #fcfbf9; padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1; min-height: 80px; color: #333; line-height: 1.5; white-space: pre-wrap;"></div>
            </div>
            <div class="moodle-modal-footer">
                <button id="closeDetailsBtn" class="moodle-btn-submit" style="margin-top:0; background-color: #6c757d;">Close</button>
            </div>
        </div>
    </div>

    <?php include 'assessor_help_modal.php'; ?>

    <script>
        // Downgraded Student JS Syntax
        var detailsModal = document.getElementById("studentDetailsModal");
        var tableRows = document.getElementsByClassName('evaluated-row');
        
        for (var i = 0; i < tableRows.length; i++) {
            tableRows[i].addEventListener('dblclick', function() {
                document.getElementById('detailStudentName').innerText = this.getAttribute('data-name');
                document.getElementById('detailId').innerText = this.getAttribute('data-id');
                
                var totalScore = parseFloat(this.getAttribute('data-total'));
                var totalEl = document.getElementById('detailTotal');
                totalEl.innerText = this.getAttribute('data-total');
                
                totalEl.className = 'score-badge';
                if (totalScore >= 70) totalEl.className += ' score-tier-1';
                else if (totalScore >= 60) totalEl.className += ' score-tier-2';
                else if (totalScore >= 50) totalEl.className += ' score-tier-3';
                else if (totalScore >= 40) totalEl.className += ' score-tier-4';
                else totalEl.className += ' score-tier-5';
                
                // JS 里获取原分，再乘以对应的权重比例
                document.getElementById('detTask').innerText = (parseFloat(this.getAttribute('data-task')) * 0.10).toFixed(2) + '/10.00';
                document.getElementById('detHealth').innerText = (parseFloat(this.getAttribute('data-health')) * 0.10).toFixed(2) + '/10.00';
                document.getElementById('detConn').innerText = (parseFloat(this.getAttribute('data-conn')) * 0.10).toFixed(2) + '/10.00';
                document.getElementById('detReport').innerText = (parseFloat(this.getAttribute('data-report')) * 0.15).toFixed(2) + '/15.00';
                document.getElementById('detClarity').innerText = (parseFloat(this.getAttribute('data-clarity')) * 0.10).toFixed(2) + '/10.00';
                document.getElementById('detLife').innerText = (parseFloat(this.getAttribute('data-life')) * 0.15).toFixed(2) + '/15.00';
                document.getElementById('detProj').innerText = (parseFloat(this.getAttribute('data-proj')) * 0.15).toFixed(2) + '/15.00';
                document.getElementById('detTime').innerText = (parseFloat(this.getAttribute('data-time')) * 0.15).toFixed(2) + '/15.00';
                
                document.getElementById('detailComments').innerText = this.getAttribute('data-comments');
                
                detailsModal.style.display = "flex";
            });
        }

        document.getElementById("closeDetailsX").onclick = function() { detailsModal.style.display = "none"; };
        document.getElementById("closeDetailsBtn").onclick = function() { detailsModal.style.display = "none"; };

        function filterResults() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toUpperCase();
            var table = document.getElementById("resultsTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var rowText = tr[i].innerText.toUpperCase();
                if (rowText.indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        var curCol = -1;
        var curDir = 'asc';
        
        function sortTable(idx, type) {
            var table = document.getElementById("resultsTable");
            var tbody = table.getElementsByTagName("tbody")[0];
            var rows = tbody.getElementsByTagName("tr");
            var rowsArray = [];
            
            for (var i = 0; i < rows.length; i++) {
                rowsArray.push(rows[i]);
            }

            var dir = (curCol === idx && curDir === 'asc') ? 'desc' : 'asc';
            
            var icons = document.querySelectorAll('.sort-icons span');
            for (var j = 0; j < icons.length; j++) {
                icons[j].classList.remove('active-sort');
            }
            
            var targetIcon = table.getElementsByTagName("th")[idx].querySelector(dir === 'asc' ? '.up' : '.down');
            if (targetIcon) targetIcon.classList.add('active-sort');

            curCol = idx;
            curDir = dir;

            rowsArray.sort(function(a, b) {
                var vA = a.getElementsByTagName("td")[idx].innerText;
                var vB = b.getElementsByTagName("td")[idx].innerText;
                
                if (type === 'num') {
                    vA = parseFloat(vA);
                    vB = parseFloat(vB);
                    return (dir === 'asc') ? (vA - vB) : (vB - vA);
                } else {
                    vA = vA.toLowerCase();
                    vB = vB.toLowerCase();
                    if (vA < vB) return (dir === 'asc') ? -1 : 1;
                    if (vA > vB) return (dir === 'asc') ? 1 : -1;
                    return 0;
                }
            });

            for (var k = 0; k < rowsArray.length; k++) {
                tbody.appendChild(rowsArray[k]);
            }
        }

        var rubricModal = document.getElementById("rubricModal"); // 注意这个在包含的文件里，或者如果在其他逻辑里可能会被覆盖，由于引入了外部modal，这个变量依然能找到对应的ID
        var openRubricBtn = document.getElementById("openRubricModalBtn");
        var closeRubricX = document.getElementById("closeRubricModalX");
        
        // 这里的 onclick 可能会和 assessor_help_modal.php 里的 listener 重复，但无妨。如果觉得冗余，你也可以删掉。这里为你保留原有逻辑。
        if(openRubricBtn) openRubricBtn.onclick = function() { rubricModal = document.getElementById("rubricModal"); if(rubricModal) rubricModal.style.display = "flex"; }
        if(closeRubricX) closeRubricX.onclick = function() { rubricModal = document.getElementById("rubricModal"); if(rubricModal) rubricModal.style.display = "none"; }

        window.onclick = function(event) { 
            if (event.target == detailsModal) detailsModal.style.display = "none"; 
            if (event.target == document.getElementById("rubricModal")) document.getElementById("rubricModal").style.display = "none";
        }
    </script>
</body>
</html>