<?php
// Assessor/evaluate_student.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

$assessor_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

// Fetch pending students for this assessor
$sql_pending = "SELECT i.internship_id, s.student_id, s.student_name 
                FROM Internships i
                JOIN Students s ON i.student_id = s.student_id
                LEFT JOIN Assessments a ON i.internship_id = a.internship_id
                WHERE i.assessor_id = :assessor_id AND a.assessment_id IS NULL
                ORDER BY s.student_id ASC";
$stmt_pending = $pdo->prepare($sql_pending);
$stmt_pending->execute(['assessor_id' => $assessor_id]);
$pending_students = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluate Student - Assessor View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .score-input { width: 100%; padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .score-input:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        .moodle-form-label { display: block; font-weight: bold; margin-bottom: 10px; color: #1d2125; font-size: 15px; }
        .score-display-box { background-color: #f8f9fa; padding: 20px; text-align: right; font-size: 20px; font-weight: bold; margin-top: 30px; border-radius: 6px; color: #10263b; border: 1px solid #dee2e6; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 35px; font-size: 16px; border-radius: 4px; cursor: pointer; margin-top: 25px; font-weight: bold; }
        .moodle-btn-submit:hover { background-color: #0d1e2e; }

        .autocomplete-wrapper { position: relative; width: 100%; }
        .dropdown-caret { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: 12px; pointer-events: none; }
        #student_search { padding-right: 35px; cursor: pointer; }
        .custom-dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background-color: #ffffff; border: 1px solid #8f959e; border-top: none; border-radius: 0 0 4px 4px; max-height: 250px; overflow-y: auto; z-index: 1001; display: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .custom-dropdown-item { padding: 12px 18px; cursor: pointer; color: #555; font-size: 16px; border-bottom: 1px solid #f1f3f5; }
        .custom-dropdown-item:hover { background-color: #f1f3f5; color: #10263b; }
        .custom-dropdown-item:last-child { border-bottom: none; }
        .highlight-match { background-color: #ffeb3b; color: #1d2125; font-weight: bold; border-radius: 2px; padding: 0 2px; }

        /*backdrop-filter: blur(3px); */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; transition: color 0.2s ease; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }
        
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
                <a href="evaluate_student.php" class="active-link">Evaluate</a>
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
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Assessor View)</h1>

        <div class="moodle-card-white">
            <h2 class="section-title">Evaluate Assigned Student</h2>
            <?php if (count($pending_students) == 0): ?>
                <p style="text-align:center; color:#666; background:#f8f9fa; padding:40px; border-radius:4px;">No pending evaluations. All caught up!</p>
            <?php else: ?>
                <form action="submit_marks.php" method="POST" id="evalForm">
                    
                    <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 20px;">
                        <label class="moodle-form-label">Search or Select Student (By ID or Name):</label>
                        <div class="autocomplete-wrapper">
                            <input type="text" id="student_search" class="score-input" placeholder="Click to view all, or type to search..." autocomplete="off" required>
                            <span class="dropdown-caret">&#9660;</span>
                            <div id="custom_dropdown" class="custom-dropdown-list"></div>
                        </div>
                        <input type="hidden" name="internship_id" id="hidden_internship_id" required>
                    </div>

                    <div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Undertaking Tasks/Projects <span style="color:#7a327e; font-weight:normal;">(10%)</span> (Max 100):</label><input type="number" id="task_score" name="task_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Health and Safety Requirements at the Workplace <span style="color:#7a327e; font-weight:normal;">(10%)</span> (Max 100):</label><input type="number" id="health_safety_score" name="health_safety_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Connectivity and Use of Theoretical Knowledge <span style="color:#7a327e; font-weight:normal;">(10%)</span> (Max 100):</label><input type="number" id="connectivity_score" name="connectivity_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Presentation of the Report as a Written Document <span style="color:#7a327e; font-weight:normal;">(15%)</span> (Max 100):</label><input type="number" id="report_score" name="report_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Clarity of Language and Illustration <span style="color:#7a327e; font-weight:normal;">(10%)</span> (Max 100):</label><input type="number" id="clarity_score" name="clarity_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Lifelong Learning Activities <span style="color:#7a327e; font-weight:normal;">(15%)</span> (Max 100):</label><input type="number" id="lifelong_score" name="lifelong_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Project Management <span style="color:#7a327e; font-weight:normal;">(15%)</span> (Max 100):</label><input type="number" id="project_mgmt_score" name="project_mgmt_score" min="0" max="100" step="1" class="score-input" required></div>
                        <div style="margin-bottom:15px;"><label class="moodle-form-label">Time Management <span style="color:#7a327e; font-weight:normal;">(15%)</span> (Max 100):</label><input type="number" id="time_mgmt_score" name="time_mgmt_score" min="0" max="100" step="1" class="score-input" required></div>
                    </div>
                    
                    <label class="moodle-form-label" style="margin-top:25px;">Qualitative Comments:</label>
                    <textarea name="qualitative_comments" rows="4" class="score-input" style="font-family:inherit; resize:vertical;" placeholder="Provide justification for the assigned scores..." required></textarea>
                    
                    <div class="score-display-box">Final Weighted Total: <span id="display_total" style="color:#7a327e;">0.00</span> / 100</div>
                    <button type="submit" class="moodle-btn-submit">Save changes</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div id="confirmSubmitModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box" style="max-width: 550px;">
            <div class="moodle-modal-header">
                <h2>Confirm Submission</h2>
                <span class="moodle-close-x" id="closeConfirmX">&times;</span>
            </div>
            <div class="moodle-modal-body" style="padding: 30px;">
                <h3 style="color: #10263b; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 20px;">Are you sure you want to submit this assessment?</h3>
                <div style="background-color: #f8f9fa; border-left: 4px solid #7a327e; padding: 15px; border-radius: 0 4px 4px 0;">
                    <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0;">
                        Once confirmed, the grades and comments will be permanently saved to the database and <strong style="color: #842029;">cannot be modified</strong>. <br><br>
                        Please ensure all entered raw scores and your qualitative comments are accurate.
                    </p>
                </div>
            </div>
            <div class="moodle-modal-footer">
                <button type="button" id="cancelSubmitBtn" style="background-color: #f8f9fa; color: #555; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-weight: bold; font-size: 15px;">Cancel</button>
                <button type="button" id="finalSubmitBtn" class="moodle-btn-submit" style="margin-top:0; padding: 10px 25px; font-size: 15px;">Confirm & Submit</button>
            </div>
        </div>
    </div>

    <?php include 'assessor_help_modal.php'; ?>

    <script>
        // Confirm submission modal logic
        var confirmModal = document.getElementById("confirmSubmitModal");
        var closeConfirmX = document.getElementById("closeConfirmX");
        var cancelSubmitBtn = document.getElementById("cancelSubmitBtn");
        var finalSubmitBtn = document.getElementById("finalSubmitBtn");
        var evalForm = document.getElementById('evalForm');
        var hiddenId = document.getElementById('hidden_internship_id');

        if(evalForm) {
            evalForm.addEventListener('submit', function(e) {
                e.preventDefault(); 
                if(hiddenId.value === '') {
                    alert('Please select a valid student from the dropdown list.');
                    return;
                }
                confirmModal.style.display = "flex";
            });
        }
        
        closeConfirmX.onclick = function() { confirmModal.style.display = "none"; }
        cancelSubmitBtn.onclick = function() { confirmModal.style.display = "none"; }
        finalSubmitBtn.onclick = function() { evalForm.submit(); }

        window.onclick = function(event) { 
            if (event.target == confirmModal) {
                confirmModal.style.display = "none"; 
            }
        }

        // Custom dropdown
        const studentsData = <?php echo json_encode($pending_students); ?>;
        const searchInput = document.getElementById('student_search');
        const dropdown = document.getElementById('custom_dropdown');

        if(searchInput) {
            function escapeRegExp(string) { return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
            function renderList(filterText = '') {
                dropdown.innerHTML = '';
                let hasMatch = false;
                const regex = filterText ? new RegExp('(' + escapeRegExp(filterText) + ')', 'gi') : null;

                studentsData.forEach(s => {
                    const displayText = s.student_id + ' - ' + s.student_name;
                    if (!filterText || displayText.toLowerCase().includes(filterText.toLowerCase())) {
                        hasMatch = true;
                        const item = document.createElement('div');
                        item.className = 'custom-dropdown-item';
                        if (filterText) {
                            item.innerHTML = displayText.replace(regex, '<span class="highlight-match">$1</span>');
                        } else {
                            item.innerText = displayText;
                        }
                        item.addEventListener('click', function(e) {
                            e.stopPropagation(); 
                            searchInput.value = displayText; 
                            hiddenId.value = s.internship_id; 
                            dropdown.style.display = 'none'; 
                        });
                        dropdown.appendChild(item);
                    }
                });
                dropdown.style.display = hasMatch ? 'block' : 'none';
            }

            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (dropdown.style.display === 'block') dropdown.style.display = 'none';
                else renderList(this.value.trim()); 
            });
            searchInput.addEventListener('input', function() {
                hiddenId.value = ''; 
                renderList(this.value.trim());
            });
            document.addEventListener('click', function() { dropdown.style.display = 'none'; });
        }

        // Real-time score calculation
        const weights = { 'task_score': 0.10, 'health_safety_score': 0.10, 'connectivity_score': 0.10, 'report_score': 0.15, 'clarity_score': 0.10, 'lifelong_score': 0.15, 'project_mgmt_score': 0.15, 'time_mgmt_score': 0.15 };
        document.querySelectorAll('.score-input').forEach(i => {
            if(i.type === 'number') {
                i.addEventListener('input', () => {
                    let total = 0;
                    document.querySelectorAll('.score-input[type=\"number\"]').forEach(inp => {
                        if(weights[inp.id]) total += (parseFloat(inp.value) || 0) * weights[inp.id];
                    });
                    document.getElementById('display_total').innerText = total.toFixed(2);
                });
            }
        });
    </script>
</body>
</html>