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

// Add Internship Assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_internship'])) {
    $student_id = $_POST['student_id'];
    $assessor_id = $_POST['assessor_id'];
    $company = $_POST['company'];
    $details = $_POST['details'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Internships (student_id, assessor_id, company_name, other_details) VALUES (:sid, :aid, :comp, :det)");
        $stmt->bindParam(':sid', $student_id);
        $stmt->bindParam(':aid', $assessor_id);
        $stmt->bindParam(':comp', $company);
        $stmt->bindParam(':det', $details);
        $stmt->execute();
        $success_msg = "Internship assigned successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error assigning internship. Student might already be assigned.";
    }
}

// Update internship details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_internship'])) {
    $update_id = $_POST['update_id'];
    $new_assessor = $_POST['edit_assessor_id'];
    $new_company = $_POST['edit_company'];
    $new_details = $_POST['edit_details'];

    try {
        $stmt = $pdo->prepare("UPDATE Internships SET assessor_id = :aid, company_name = :comp, other_details = :det WHERE internship_id = :id");
        $stmt->bindParam(':aid', $new_assessor);
        $stmt->bindParam(':comp', $new_company);
        $stmt->bindParam(':det', $new_details);
        $stmt->bindParam(':id', $update_id);
        $stmt->execute();
        $success_msg = "Internship details updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error updating internship details.";
    }
}

// Delete internship assignment
if (isset($_GET['delete_id'])) {
    try {
        $del_id = $_GET['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM Internships WHERE internship_id = :id");
        $stmt->bindParam(':id', $del_id);
        $stmt->execute();
        header("Location: manage_internships.php?deleted=1"); 
        exit();
    } catch (PDOException $e) {
        $error_msg = "Cannot delete internship. An assessment record might already be linked to it.";
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success_msg = "Internship assignment deleted successfully!";
}

$students = $pdo->query("SELECT student_id, student_name FROM Students")->fetchAll();
$assessors = $pdo->query("SELECT user_id, username FROM Users WHERE role = 'Assessor'")->fetchAll();

$sql = "SELECT i.internship_id, i.student_id, i.assessor_id, s.student_name, u.username as assessor_name, i.company_name, i.other_details
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-bottom: 15px; width: 100%; box-sizing: border-box;}
        .moodle-form-input:focus { outline: none; border-color: #10263b; box-shadow: 0 0 0 2px rgba(16, 38, 59, 0.2); background-color: #e8f0fe; }
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        
        .btn-action { padding: 8px 15px; font-size: 13px; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold; display: inline-block; margin-right: 5px; border: none; }
        .btn-edit { background-color: #6c757d; color: white; }
        .btn-edit:hover { background-color: #5a6268; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .moodle-form-label { display: block; font-weight: bold; margin-bottom: 8px; color: #1d2125; font-size: 14px; }
        
        /*max-width  550px */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 550px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .admin-link { color: #10263b; text-decoration: none; font-weight: bold; transition: color 0.2s ease; }
        .admin-link:hover { color: #7a327e; text-decoration: underline; }
    </style>
</head>
<body>

    <nav class="moodle-navbar-white">
        <div class="nav-left-white">
            <img src="../images/logo.png" alt="University Logo" class="nav-logo-white">
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_students.php">Students</a>
                <a href="manage_internships.php" class="active-link">Internships</a>
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
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Admin View)</h1>

        <?php if ($success_msg): ?> <div class="alert-success"><?= $success_msg ?></div> <?php endif; ?>
        <?php if ($error_msg): ?> <div class="alert-danger"><?= $error_msg ?></div> <?php endif; ?>

        <div class="moodle-card-white">
            <h2 class="section-title">Internship Management</h2>
            
            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Assign Internship to Student</label>
                <form id="assignForm" method="POST">
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

                    <input type="text" id="company" name="company" class="moodle-form-input" placeholder="Company Name" required>
                    <input type="text" name="details" class="moodle-form-input" placeholder="Other Details (Optional)">
                    <button type="submit" name="assign_internship" class="moodle-btn-submit">Assign Internship</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="moodle-table">
                    <thead>
                        <tr><th>Assignment ID</th><th>Student</th><th>Assessor</th><th>Company</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($internships as $row): ?>
                        <tr>
                            <td><?= $row['internship_id'] ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?> (<?= htmlspecialchars($row['student_id']) ?>)</td>
                            <td><?= htmlspecialchars($row['assessor_name']) ?></td>
                            <td><?= htmlspecialchars($row['company_name']) ?></td>
                            <td>
                                <button class="btn-action btn-edit" 
                                        onclick="openEditModal('<?= htmlspecialchars($row['internship_id']); ?>', '<?= htmlspecialchars($row['student_name']) . ' (' . htmlspecialchars($row['student_id']) . ')'; ?>', '<?= htmlspecialchars($row['assessor_id']); ?>', '<?= htmlspecialchars($row['company_name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['other_details'], ENT_QUOTES); ?>')">Edit</button>
                                <a href="manage_internships.php?delete_id=<?= $row['internship_id']; ?>" class="btn-action btn-danger" onclick="event.preventDefault(); showDeleteModal(this.href, 'Are you sure you want to delete this internship assignment?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box">
            <div class="moodle-modal-header">
                <h2>Edit Internship Assignment</h2>
                <span class="moodle-close-x" id="closeEditX">&times;</span>
            </div>
            <form method="POST" action="manage_internships.php">
                <div class="moodle-modal-body">
                    <input type="hidden" id="update_id" name="update_id">

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Student (Cannot be changed):</label>
                        <input type="text" id="display_student" class="moodle-form-input" style="background-color: #e9ecef; cursor: not-allowed; margin-bottom: 0;" disabled>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Assessor:</label>
                        <select id="edit_assessor_id" name="edit_assessor_id" class="moodle-form-input" style="margin-bottom: 0;" required>
                            <?php foreach ($assessors as $a): ?>
                                <option value="<?= $a['user_id'] ?>"><?= htmlspecialchars($a['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Company Name:</label>
                        <input type="text" id="edit_company" name="edit_company" class="moodle-form-input" style="margin-bottom: 0;" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Other Details (Optional):</label>
                        <input type="text" id="edit_details" name="edit_details" class="moodle-form-input" style="margin-bottom: 0;">
                    </div>
                </div>
                <div class="moodle-modal-footer">
                    <button type="button" id="cancelEditBtn" style="background-color: #f8f9fa; color: #555; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-weight: bold; font-size: 15px;">Cancel</button>
                    <button type="submit" name="update_internship" class="moodle-btn-submit" style="margin-top:0; padding: 10px 25px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
<div id="deleteConfirmModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box" style="max-width: 450px;">
            <div class="moodle-modal-header" style="border-bottom: 2px solid #f5c2c7; background-color: #fdf2f2;">
                <h2 style="color: #dc3545; font-size: 18px;"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h2>
                <span class="moodle-close-x" id="closeDeleteX">&times;</span>
            </div>
            <div class="moodle-modal-body">
                <p id="deleteConfirmMessage" style="font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #10263b;"></p>
                <p style="color: #666; font-size: 14px; margin-top: 0;">This action cannot be undone. All linked data may be affected.</p>
            </div>
            <div class="moodle-modal-footer">
                <button type="button" id="cancelDeleteBtn" style="background-color: #f8f9fa; color: #555; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-weight: bold; font-size: 15px;">Cancel</button>
                <a href="#" id="confirmDeleteLink" class="moodle-btn-submit" style="background-color: #dc3545; text-decoration: none; display: inline-block; padding: 10px 25px;">Continue</a>
            </div>
        </div>
    </div>

    <script>
        var deleteModal = document.getElementById("deleteConfirmModal");
        var confirmDeleteLink = document.getElementById("confirmDeleteLink");
        var deleteMessage = document.getElementById("deleteConfirmMessage");
        var closeDeleteX = document.getElementById("closeDeleteX");
        var cancelDeleteBtn = document.getElementById("cancelDeleteBtn");

        // open delete confirmation modal with dynamic message and link
        function showDeleteModal(deleteUrl, messageText) {
            deleteMessage.innerText = messageText;
            confirmDeleteLink.href = deleteUrl; 
            deleteModal.style.display = "flex";
        }

        // close modal on X or Cancel
        if(closeDeleteX) { closeDeleteX.onclick = function() { deleteModal.style.display = "none"; } }
        if(cancelDeleteBtn) { cancelDeleteBtn.onclick = function() { deleteModal.style.display = "none"; } }

        // close modal when clicking outside the box
        window.addEventListener('click', function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        });
    </script>
    <?php include 'admin_help_modal.php'; ?>

    <script>
        var editModal = document.getElementById("editModal");
        var closeEditX = document.getElementById("closeEditX");
        var cancelEditBtn = document.getElementById("cancelEditBtn");

        function openEditModal(id, studentDisplay, assessorId, company, details) {
            document.getElementById('update_id').value = id;
            document.getElementById('display_student').value = studentDisplay;
            document.getElementById('edit_assessor_id').value = assessorId;
            document.getElementById('edit_company').value = company;
            document.getElementById('edit_details').value = details;
            editModal.style.display = "flex";
        }

        if(closeEditX) { closeEditX.onclick = function() { editModal.style.display = "none"; } }
        if(cancelEditBtn) { cancelEditBtn.onclick = function() { editModal.style.display = "none"; } }

        var form = document.getElementById('assignForm');
        if (form) {
            form.onsubmit = function(event) {
                var company = document.getElementById('company').value.trim();
                if (company.length < 2) { 
                    alert("Error: Company Name must be at least 2 characters long."); 
                    event.preventDefault(); 
                    return false; 
                }
            };
        }

        window.onclick = function(event) { 
            if (event.target == editModal) { editModal.style.display = "none"; }
        }
    </script>
</body>
</html>