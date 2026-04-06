<?php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $programme = trim($_POST['programme']);

    try {
        $stmt = $pdo->prepare("INSERT INTO Students (student_id, student_name, programme) VALUES (:id, :name, :prog)");
        $stmt->execute(['id' => $student_id, 'name' => $student_name, 'prog' => $programme]);
        $success_msg = "Student added successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error adding student: ID might already exist.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $update_id = trim($_POST['update_id']);
    $new_name = trim($_POST['edit_student_name']);
    $new_prog = trim($_POST['edit_programme']);

    try {
        $stmt = $pdo->prepare("UPDATE Students SET student_name = :name, programme = :prog WHERE student_id = :id");
        $stmt->execute(['name' => $new_name, 'prog' => $new_prog, 'id' => $update_id]);
        $success_msg = "Student updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error updating student.";
    }
}

if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Students WHERE student_id = :id");
        $stmt->execute(['id' => $_GET['delete_id']]);
        header("Location: manage_students.php");
        exit();
    } catch (PDOException $e) {
        $error_msg = "Cannot delete student.";
    }
}

$stmt = $pdo->query("SELECT * FROM Students ORDER BY student_id ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<h2>Add Student</h2>

<form method="POST" action="manage_students.php">
    <input type="text" name="student_id" placeholder="ID" required>
    <input type="text" name="student_name" placeholder="Name" required>

    <!-- ✅ autocomplete wrapper -->
    <div style="position: relative; display:inline-block;">
        <input type="text" id="programme" name="programme" placeholder="Programme" autocomplete="off" required>
        
        <div id="suggestions" style="
            position:absolute;
            top:100%;
            left:0;
            width:100%;
            background:white;
            border:1px solid #ccc;
            z-index:999;
        "></div>
    </div>

    <button type="submit" name="add_student">Add</button>
</form>

<hr>

<h2>Students</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Programme</th>
    <th>Action</th>
</tr>

<?php foreach ($students as $student): ?>
<tr>
    <td><?= htmlspecialchars($student['student_id']); ?></td>
    <td><?= htmlspecialchars($student['student_name']); ?></td>
    <td><?= htmlspecialchars($student['programme']); ?></td>
    <td>
        <a href="manage_students.php?delete_id=<?= $student['student_id']; ?>">Delete</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<script>
const programmeInput = document.getElementById("programme");
const suggestionBox = document.getElementById("suggestions");

let timeout = null;

programmeInput.addEventListener("input", function() {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        let query = this.value;

        if (query.length < 1) {
            suggestionBox.innerHTML = "";
            return;
        }

        fetch("search_programme.php?q=" + query)
        .then(res => res.json())
        .then(data => {
            suggestionBox.innerHTML = "";

            data.forEach(item => {
                let div = document.createElement("div");
                div.textContent = item;
                div.style.padding = "8px";
                div.style.cursor = "pointer";

                // hover效果
                div.onmouseover = () => div.style.background = "#eee";
                div.onmouseout = () => div.style.background = "#fff";

                div.onclick = function() {
                    programmeInput.value = item;
                    suggestionBox.innerHTML = "";
                };

                suggestionBox.appendChild(div);
            });
        })
        .catch(err => {
            console.error("Fetch error:", err);
        });

    }, 300);
});

// 点击外面关闭
document.addEventListener("click", function(e) {
    if (!programmeInput.contains(e.target) && !suggestionBox.contains(e.target)) {
        suggestionBox.innerHTML = "";
    }
});
</script>

</body>
</html>
