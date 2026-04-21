<?php
// Admin/view_all_results.php
session_start();

//verify Admin identity
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

//  get first letter of username for avatar display
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

// Fetch all evaluated students with their scores and assessor names
$sql_evaluated = "SELECT i.internship_id, s.student_id, s.student_name, u.username as assessor_name,
                         a.total_score, a.task_score, a.health_safety_score, 
                         a.connectivity_score, a.report_score, a.clarity_score, 
                         a.lifelong_score, a.project_mgmt_score, a.time_mgmt_score,
                         a.qualitative_comments
                  FROM Internships i
                  JOIN Students s ON i.student_id = s.student_id
                  JOIN Assessments a ON i.internship_id = a.internship_id
                  JOIN Users u ON i.assessor_id = u.user_id
                  ORDER BY s.student_id ASC";
$stmt_evaluated = $pdo->prepare($sql_evaluated);
$stmt_evaluated->execute();
$evaluated_students = $stmt_evaluated->fetchAll(PDO::FETCH_ASSOC);

// average
$total_students_evaluated = count($evaluated_students);
$average_score = 0;
if ($total_students_evaluated > 0) {
    $sum = 0;
    foreach ($evaluated_students as $student) { $sum += $student['total_score']; }
    $average_score = round($sum / $total_students_evaluated, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View All Results - Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Dashboard Stats */
        .stats-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; }
        .stats-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; display: flex; align-items: center; gap: 15px; white-space: nowrap; }
        .stats-label { font-size: 15px; color: #555; font-weight: bold; }
        .stats-value { font-size: 24px; color: #7a327e; font-weight: bold; }
        .detail-raw-score { font-size: 16px; color: #10263b; }
        
        /* search bar */
        .moodle-search-bar { width: 100%; padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .moodle-search-bar:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        
        /* search modal */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .moodle-btn-submit:hover { background-color: #0d1e2e; }
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; transition: color 0.2s ease; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }
        
        /* Table Header */
        .th-content { display: flex; align-items: center; justify-content: space-between; }
        .sort-icons { display: flex; flex-direction: column; font-size: 9px; margin-left: 8px; color: #ced4da; }
        .active-sort { color: #7a327e; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_students.php">Students</a>
                <a href="manage_internships.php">Internships</a>
                <a href="manage_users.php">Users</a>
                <a href="view_all_results.php" class="active-link">Results</a> 
                <a id="openRubricModalBtn" style="cursor: pointer;">Help & Rubric</a>
            </div>
        </div>
        <div class="nav-right-white">
            <a href="../logout.php" class="logout-link">Log out</a>
            <div class="user-avatar"><?= $initial ?></div>
        </div>
    </nav>

    <div class="moodle-dashboard-container" style="margin-top: 30px;">
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Admin View)</h1>

        <div class="moodle-card-white">
            <h2 class="section-title">All Student Results Overview</h2>
            
            <?php if ($total_students_evaluated == 0): ?>
                <input type="text" id="searchInput" class="moodle-search-bar" placeholder="Search ID, Name, or Assessor..." disabled>
                <div style="padding: 30px; text-align: center; color: #666; margin-top:20px;">No students have been evaluated yet.</div>
            <?php else: ?>
                <div class="stats-dashboard">
                    <input type="text" id="searchInput" class="moodle-search-bar" placeholder="Search ID, Name, or Assessor..." onkeyup="filterResults()" style="flex-grow: 1;">
                    <div class="stats-box">
                        <span class="stats-label">University Average:</span>
                        <span class="stats-value"><?= number_format($average_score, 2) ?></span>
                    </div>
                    <a href="export_results.php" class="moodle-btn-submit" style="text-decoration:none; padding: 12px 20px; white-space: nowrap;">&#128190; Export CSV</a>
                </div>

                <div class="table-responsive">
                    <table class="moodle-table" id="resultsTable">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0, 'str')"><div class="th-content">ID <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(1, 'str')"><div class="th-content">Name <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(2, 'str')"><div class="th-content">Assessor <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(3, 'num')"><div class="th-content">Tasks <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(4, 'num')"><div class="th-content">H&S <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(5, 'num')"><div class="th-content">Theory <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(6, 'num')"><div class="th-content">Report <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(7, 'num')"><div class="th-content">Clarity <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(8, 'num')"><div class="th-content">Life <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(9, 'num')"><div class="th-content">Proj <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(10, 'num')"><div class="th-content">Time <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                                <th onclick="sortTable(11, 'num')"><div class="th-content">Final Score <div class="sort-icons"><span class="up">▲</span><span class="down">▼</span></div></div></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($evaluated_students as $row): ?>
                                <tr class="evaluated-row" title="Double-click to view full details and comments"
                                    data-id="<?= htmlspecialchars($row['student_id']) ?>"
                                    data-name="<?= htmlspecialchars($row['student_name']) ?>"
                                    data-assessor="<?= htmlspecialchars($row['assessor_name']) ?>"
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
                                    <td><span style="background-color:#e8f0fe; padding:4px 8px; border-radius:4px; font-size:12px; color:#10263b;"><?= htmlspecialchars($row['assessor_name']) ?></span></td>
                                    <td><?= number_format($row['task_score'] * 0.10, 2) ?></td>
                                    <td><?= number_format($row['health_safety_score'] * 0.10, 2) ?></td>
                                    <td><?= number_format($row['connectivity_score'] * 0.10, 2) ?></td>
                                    <td><?= number_format($row['report_score'] * 0.15, 2) ?></td>
                                    <td><?= number_format($row['clarity_score'] * 0.10, 2) ?></td>
                                    <td><?= number_format($row['lifelong_score'] * 0.15, 2) ?></td>
                                    <td><?= number_format($row['project_mgmt_score'] * 0.15, 2) ?></td>
                                    <td><?= number_format($row['time_mgmt_score'] * 0.15, 2) ?></td>
                                    <td><strong style="color:#7a327e; font-size: 15px;"><?= number_format($row['total_score'], 2) ?></strong></td>
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
                    <div style="font-size: 15px;"><strong>Student ID:</strong> <span id="detailId"></span><br><strong>Evaluated By:</strong> <span id="detailAssessor" style="color:#555;"></span></div>
                    <div style="font-size: 16px; text-align:right;"><strong>Final Weighted Score:</strong><br><span id="detailTotal" style="color: #7a327e; font-size: 24px; font-weight: bold;"></span> / 100</div>
                </div>
                
                <h4 style="margin-top: 0; color: #10263b;">Raw Component Scores (Out of 100)</h4>
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
                <div id="detailComments" style="background-color: #fcfbf9; padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1; min-height: 80px; color: #333; line-height: 1.5; white-space: pre-wrap;">
                </div>
            </div>
            <div class="moodle-modal-footer">
                <button id="closeDetailsBtn" class="moodle-btn-submit" style="margin-top:0; background-color: #6c757d;">Close</button>
            </div>
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
                        &#128100; <a href="https://www.nottingham.edu.my/computer-mathematical-sciences/People/chyecheah.tan" target="_blank" class="admin-link">TAN CHYE CHEAH</a><br>
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
        // view details modal logic
        var detailsModal = document.getElementById("studentDetailsModal");
        document.querySelectorAll('.evaluated-row').forEach(row => {
            row.addEventListener('dblclick', function() {
                document.getElementById('detailStudentName').innerText = this.getAttribute('data-name');
                document.getElementById('detailId').innerText = this.getAttribute('data-id');
                document.getElementById('detailAssessor').innerText = this.getAttribute('data-assessor');
                document.getElementById('detailTotal').innerText = this.getAttribute('data-total');
                
                document.getElementById('detTask').innerText = this.getAttribute('data-task');
                document.getElementById('detHealth').innerText = this.getAttribute('data-health');
                document.getElementById('detConn').innerText = this.getAttribute('data-conn');
                document.getElementById('detReport').innerText = this.getAttribute('data-report');
                document.getElementById('detClarity').innerText = this.getAttribute('data-clarity');
                document.getElementById('detLife').innerText = this.getAttribute('data-life');
                document.getElementById('detProj').innerText = this.getAttribute('data-proj');
                document.getElementById('detTime').innerText = this.getAttribute('data-time');
                
                document.getElementById('detailComments').innerText = this.getAttribute('data-comments');
                
                detailsModal.style.display = "flex";
            });
        });

        document.getElementById("closeDetailsX").onclick = function() { detailsModal.style.display = "none"; }
        document.getElementById("closeDetailsBtn").onclick = function() { detailsModal.style.display = "none"; }

        // Help & Rubric 
        var rubricModal = document.getElementById("rubricModal");
        var openRubricBtn = document.getElementById("openRubricModalBtn");
        var closeRubricX = document.getElementById("closeRubricModalX");
        var closeRubricBtn = document.getElementById("closeRubricModalBtn");
        
        if(openRubricBtn) openRubricBtn.onclick = function() { rubricModal.style.display = "flex"; }
        if(closeRubricX) closeRubricX.onclick = function() { rubricModal.style.display = "none"; }
        if(closeRubricBtn) closeRubricBtn.onclick = function() { rubricModal.style.display = "none"; }

        // Close modals when clicking outside the modal box
        window.onclick = function(event) { 
            if (event.target == detailsModal) detailsModal.style.display = "none"; 
            if (event.target == rubricModal) rubricModal.style.display = "none";
        }

        // search filter 
        function filterResults() {
            let f = document.getElementById('searchInput').value.toUpperCase();
            document.querySelectorAll('#resultsTable tbody tr').forEach(r => {
                r.style.display = r.innerText.toUpperCase().indexOf(f) > -1 ? "" : "none";
            });
        }

        // sorting 
        let curCol = -1, curDir = 'asc';
        function sortTable(idx, type) {
            const table = document.getElementById("resultsTable");
            const rows = Array.from(table.tBodies[0].rows);
            let dir = (curCol === idx && curDir === 'asc') ? 'desc' : 'asc';
            document.querySelectorAll('.sort-icons span').forEach(s => s.classList.remove('active-sort'));
            table.querySelectorAll("th")[idx].querySelector(dir === 'asc' ? '.up' : '.down').classList.add('active-sort');
            curCol = idx; curDir = dir;
            rows.sort((a, b) => {
                let vA = a.cells[idx].innerText, vB = b.cells[idx].innerText;
                if(type === 'num') return dir === 'asc' ? parseFloat(vA) - parseFloat(vB) : parseFloat(vB) - parseFloat(vA);
                return dir === 'asc' ? vA.localeCompare(vB) : vB.localeCompare(vA);
            });
            rows.forEach(r => table.tBodies[0].appendChild(r));
        }
    </script>
</body>
</html>
