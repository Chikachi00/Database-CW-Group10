<?php
// Admin/manage_students.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

//first letter of the username for avatar display
$username = $_SESSION['username'];
$initial = strtoupper(substr($username, 0, 1)); 

// Read and clear flash messages from session (shown once, then gone)
$success_msg = $_SESSION['flash_success'] ?? '';
$error_msg   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $programme = trim($_POST['programme']);

    try {
        $stmt = $pdo->prepare("INSERT INTO Students (student_id, student_name, programme) VALUES (:id, :name, :prog)");
        $stmt->execute(['id' => $student_id, 'name' => $student_name, 'prog' => $programme]);
        $_SESSION['flash_success'] = "Student added successfully!";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Error adding student: ID might already exist in the system.";
    }
    header("Location: manage_students.php");
    exit();
}

// Update student details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $update_id = trim($_POST['update_id']);
    $new_name = trim($_POST['edit_student_name']);
    $new_prog = trim($_POST['edit_programme']);

    try {
        $stmt = $pdo->prepare("UPDATE Students SET student_name = :name, programme = :prog WHERE student_id = :id");
        $stmt->execute(['name' => $new_name, 'prog' => $new_prog, 'id' => $update_id]);
        $_SESSION['flash_success'] = "Student details updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Error updating student details.";
    }
    header("Location: manage_students.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Students WHERE student_id = :id");
        $stmt->execute(['id' => $_GET['delete_id']]);
        $_SESSION['flash_success'] = "Student deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Cannot delete student. They might have internship records linked to them.";
    }
    header("Location: manage_students.php");
    exit();
}

// Fetch all unique programmes for the filter dropdown
$programmes = $pdo->query("SELECT DISTINCT programme FROM Students ORDER BY programme ASC")->fetchAll(PDO::FETCH_COLUMN);

// Apply programme filter if one is selected via URL query
$filter_programme = isset($_GET['programme']) ? trim($_GET['programme']) : '';
if ($filter_programme !== '') {
    $stmt = $pdo->prepare("SELECT * FROM Students WHERE programme = :prog ORDER BY student_id ASC");
    $stmt->execute(['prog' => $filter_programme]);
} else {
    $stmt = $pdo->query("SELECT * FROM Students ORDER BY student_id ASC");
}
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-action { padding: 8px 15px; font-size: 13px; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold; display: inline-block; margin-right: 5px; border: none; }
        .btn-edit { background-color: #6c757d; color: white; }
        .btn-edit:hover { background-color: #5a6268; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }

        /* Added backdrop-filter */
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 550px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
        .moodle-modal-header { padding: 15px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; }
        .moodle-modal-header h2 { margin: 0; font-size: 20px; color: #10263b; }
        .moodle-close-x { font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .moodle-close-x:hover { color: #333; }
        .moodle-modal-body { padding: 25px; font-size: 15px; line-height: 1.6; color: #333; }
        .moodle-modal-footer { padding: 15px 25px; border-top: 1px solid #dee2e6; text-align: right; background-color: #f8f9fa; }
        .moodle-form-label { display: block; font-weight: bold; margin-bottom: 8px; color: #1d2125; font-size: 14px; }
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-right: 15px; box-sizing: border-box; width: 220px;}
        .moodle-btn-submit { background-color: #10263b; color: white; border: none; padding: 12px 25px; font-size: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; font-size: 16px; }
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
                <a href="manage_students.php" class="active-link">Students</a>
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
        <h1 class="moodle-page-header" style="margin-left: 0;">Internship Assessment (Admin View)</h1>

        <?php if ($success_msg): ?> <div class="alert-success"><?= $success_msg ?></div> <?php endif; ?>
        <?php if ($error_msg): ?> <div class="alert-danger"><?= $error_msg ?></div> <?php endif; ?>

        <div class="moodle-card-white">
            <h2 class="section-title">Student Management</h2>

            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Register New Student</label>
                <form id="addStudentForm" method="POST" action="manage_students.php" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="student_id" name="student_id" class="moodle-form-input" placeholder="ID (e.g. 2026001)" required>
                    <input type="text" id="student_name" name="student_name" class="moodle-form-input" placeholder="Full Name" required>
                    <input type="text" id="programme" name="programme" class="moodle-form-input" placeholder="Programme" required>
                    <button type="submit" name="add_student" class="moodle-btn-submit">+ Add Student</button>
                </form>
            </div>

            <div style="display:flex; align-items:center; gap:15px; margin-bottom:15px; flex-wrap:wrap;">
                <label style="font-weight:bold; color:#1d2125; font-size:14px;">Filter by Programme:</label>
                <form method="GET" action="manage_students.php" style="display:flex; gap:10px; align-items:center; flex-grow:1;">
                    <select name="programme" class="moodle-form-input" style="width:auto; margin-right:0; min-width:250px;" onchange="this.form.submit()">
                        <option value="">-- All Programmes --</option>
                        <?php foreach ($programmes as $prog): ?>
                            <option value="<?= htmlspecialchars($prog) ?>" <?= $filter_programme === $prog ? 'selected' : '' ?>><?= htmlspecialchars($prog) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($filter_programme !== ''): ?>
                        <a href="manage_students.php" class="btn-action btn-edit" style="padding:10px 15px;">Clear Filter</a>
                    <?php endif; ?>
                </form>
                <span style="color:#6c757d; font-size:13px;">Showing <?= count($students) ?> student(s)</span>
            </div>

            <div class="table-responsive">
                <table class="moodle-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Programme</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']); ?></td>
                            <td><?= htmlspecialchars($student['student_name']); ?></td>
                            <td><?= htmlspecialchars($student['programme']); ?></td>
                            <td>
                                <button class="btn-action btn-edit" onclick="openEditModal('<?= htmlspecialchars($student['student_id']); ?>', '<?= htmlspecialchars($student['student_name']); ?>', '<?= htmlspecialchars($student['programme']); ?>')">Edit</button>
                                <a href="manage_students.php?delete_id=<?= $student['student_id']; ?>" class="btn-action btn-danger" onclick="event.preventDefault(); showDeleteModal(this.href, 'Are you sure you want to delete this student?');">Delete</a>
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
                <h2>Edit Student Details</h2>
                <span class="moodle-close-x" id="closeEditX">&times;</span>
            </div>
            <form method="POST" action="manage_students.php">
                <div class="moodle-modal-body">
                    <input type="hidden" id="update_id" name="update_id">
                    
                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Student ID (Cannot be changed):</label>
                        <input type="text" id="display_id" class="moodle-form-input" style="width: 100%; background-color: #e9ecef; cursor: not-allowed;" disabled>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Full Name:</label>
                        <input type="text" id="edit_student_name" name="edit_student_name" class="moodle-form-input" style="width: 100%;" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Programme:</label>
                        <input type="text" id="edit_programme" name="edit_programme" class="moodle-form-input" style="width: 100%;" required>
                    </div>
                </div>
                <div class="moodle-modal-footer">
                    <button type="button" id="cancelEditBtn" style="background-color: #f8f9fa; color: #555; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-weight: bold; font-size: 15px;">Cancel</button>
                    <button type="submit" name="update_student" class="moodle-btn-submit" style="margin-top:0; padding: 10px 25px;">Save Changes</button>
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
            confirmDeleteLink.href = deleteUrl; // 动态赋予删除链接
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
    // Client-side validation for Add Student form
    document.getElementById('addStudentForm').addEventListener('submit', function(event) {
        const studentId = document.getElementById('student_id').value.trim();
        const studentName = document.getElementById('student_name').value.trim();
        if (studentId.length < 3) { alert("Error: Student ID must be at least 3 characters long."); event.preventDefault(); return; }
        const nameRegex = /^[A-Za-z\s]+$/;
        if (!nameRegex.test(studentName)) { alert("Error: Student Name should only contain letters and spaces."); event.preventDefault(); return; }
    });

    // Edit Modal Logic
    var editModal = document.getElementById("editModal");
    var closeEditX = document.getElementById("closeEditX");
    var cancelEditBtn = document.getElementById("cancelEditBtn");

    function openEditModal(id, name, programme) {
        document.getElementById('update_id').value = id;
        document.getElementById('display_id').value = id;
        document.getElementById('edit_student_name').value = name;
        document.getElementById('edit_programme').value = programme;
        editModal.style.display = "flex";
    }

    closeEditX.onclick = function() { editModal.style.display = "none"; }
    cancelEditBtn.onclick = function() { editModal.style.display = "none"; }

    // Click outside to close Edit Modal
    window.onclick = function(event) { 
        if (event.target == editModal) {
            editModal.style.display = "none"; 
        }
    }
    </script>
</body>
</html>