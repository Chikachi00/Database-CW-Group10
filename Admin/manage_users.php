<?php
// Admin/manage_users.php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password']; 
    try {
        // Hash the password before storing (never store plain text passwords)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:user, :pass, 'Assessor')");
        $stmt->execute(['user' => $new_username, 'pass' => $hashed_password]);
        $success_msg = "Assessor added successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error: Username might already exist.";
    }
}

// Update assessor details (password is optional - leave blank to keep the current one)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $update_id = trim($_POST['update_id']);
    $new_username = trim($_POST['edit_username']);
    $new_password = trim($_POST['edit_password']);

    try {
        if ($new_password !== '') {
            $stmt = $pdo->prepare("UPDATE Users SET username = :user, password = :pass WHERE user_id = :id AND role = 'Assessor'");
            $stmt->execute(['user' => $new_username, 'pass' => $new_password, 'id' => $update_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE Users SET username = :user WHERE user_id = :id AND role = 'Assessor'");
            $stmt->execute(['user' => $new_username, 'id' => $update_id]);
        }
        $success_msg = "Assessor details updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error updating assessor: Username might already exist.";
    }
}

// Delete assessor account
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = :id AND role = 'Assessor'");
        $stmt->execute(['id' => $_GET['delete_id']]);
        header("Location: manage_users.php?deleted=1"); 
        exit();
    } catch (PDOException $e) {
        $error_msg = "Cannot delete assessor. They might have internship records linked to them.";
    }
}

// Show success message after successful deletion redirect
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success_msg = "Assessor deleted successfully!";
}

$stmt = $pdo->query("SELECT * FROM Users WHERE role = 'Assessor' ORDER BY user_id DESC");
$assessors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assessors - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .moodle-form-input { padding: 12px 18px; border: 1px solid #8f959e; border-radius: 4px; font-size: 15px; margin-right: 15px; box-sizing: border-box; width: 250px;}
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
        
        .moodle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(67, 83, 99, 0.6); z-index: 2000; justify-content: center; align-items: center; }
        .moodle-modal-box { background-color: #ffffff; width: 90%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 6px; overflow: hidden; }
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
                <a href="manage_internships.php">Internships</a>
                <a href="manage_users.php" class="active-link">Users</a>
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
            <h2 class="section-title">User Management</h2>

            <div style="background:#f8f9fa; padding:20px; border:1px solid #dee2e6; border-radius:6px; margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:15px; color:#1d2125; font-size:15px;">Add New Assessor Account</label>
                <form id="addUserForm" method="POST" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="new_username" name="username" class="moodle-form-input" placeholder="Username (e.g., Dr_Smith)" required>
                    <input type="password" id="new_password" name="password" class="moodle-form-input" placeholder="Password" required>
                    <button type="submit" name="add_user" class="moodle-btn-submit">+ Add Assessor</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="moodle-table">
                    <thead>
                        <tr><th>User ID</th><th>Username</th><th>Role</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessors as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_id']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>
                                <button class="btn-action btn-edit" onclick="openEditModal('<?= htmlspecialchars($row['user_id']); ?>', '<?= htmlspecialchars($row['username']); ?>')">Edit</button>
                                <a href="manage_users.php?delete_id=<?= $row['user_id']; ?>" class="btn-action btn-danger" onclick="return confirm('WARNING: Are you sure you want to delete this assessor?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editModal" class="moodle-modal-overlay">
        <div class="moodle-modal-box" style="max-width: 550px;">
            <div class="moodle-modal-header">
                <h2>Edit Assessor Details</h2>
                <span class="moodle-close-x" id="closeEditX">&times;</span>
            </div>
            <form method="POST" action="manage_users.php">
                <div class="moodle-modal-body">
                    <input type="hidden" id="update_id" name="update_id">
                    
                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">User ID (Cannot be changed):</label>
                        <input type="text" id="display_id" class="moodle-form-input" style="width: 100%; background-color: #e9ecef; cursor: not-allowed;" disabled>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">Username:</label>
                        <input type="text" id="edit_username" name="edit_username" class="moodle-form-input" style="width: 100%;" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="moodle-form-label">New Password (leave blank to keep current):</label>
                        <input type="password" id="edit_password" name="edit_password" class="moodle-form-input" style="width: 100%;" placeholder="Enter a new password only if changing">
                    </div>
                </div>
                <div class="moodle-modal-footer">
                    <button type="button" id="cancelEditBtn" style="background-color: #f8f9fa; color: #555; border: 1px solid #dee2e6; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-weight: bold; font-size: 15px;">Cancel</button>
                    <button type="submit" name="update_user" class="moodle-btn-submit" style="margin-top:0; padding: 10px 25px;">Save Changes</button>
                </div>
            </form>
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
        // Edit
        var editModal = document.getElementById("editModal");
        var closeEditX = document.getElementById("closeEditX");
        var cancelEditBtn = document.getElementById("cancelEditBtn");

        function openEditModal(id, uname) {
            document.getElementById('update_id').value = id;
            document.getElementById('display_id').value = id;
            document.getElementById('edit_username').value = uname;
            document.getElementById('edit_password').value = '';
            editModal.style.display = "flex";
        }

        closeEditX.onclick = function() { editModal.style.display = "none"; }
        cancelEditBtn.onclick = function() { editModal.style.display = "none"; }

        // Client-side validation for Add Assessor form
        document.getElementById('addUserForm').addEventListener('submit', function(event) {
            const uname = document.getElementById('new_username').value.trim();
            const pwd = document.getElementById('new_password').value;
            const unameRegex = /^[A-Za-z0-9_]+$/;
            if (!unameRegex.test(uname)) { alert("Error: Username should only contain letters, numbers, and underscores."); event.preventDefault(); return; }
            if (pwd.length < 6) { alert("Error: Password must be at least 6 characters long."); event.preventDefault(); return; }
        });

        // Help & Rubric Modal 
        var rubricModal = document.getElementById("rubricModal");
        var openRubricBtn = document.getElementById("openRubricModalBtn");
        var closeRubricX = document.getElementById("closeRubricModalX");
        var closeRubricBtn = document.getElementById("closeRubricModalBtn");
        
        if(openRubricBtn) openRubricBtn.onclick = function() { rubricModal.style.display = "flex"; }
        if(closeRubricX) closeRubricX.onclick = function() { rubricModal.style.display = "none"; }
        if(closeRubricBtn) closeRubricBtn.onclick = function() { rubricModal.style.display = "none"; }

        window.onclick = function(event) { 
            if (event.target == rubricModal) rubricModal.style.display = "none";
            if (event.target == editModal) editModal.style.display = "none";
        }
    </script>
</body>
</html>