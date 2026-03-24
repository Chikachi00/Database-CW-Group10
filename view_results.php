<?php
// view_results.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'Includes/db_connect.php'; // 根目录直接连 Includes

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$search_query = "";
$params = [];

// 基础 SQL 查询 (把 4 张表连起来，才能同时显示学号、姓名、老师名和总分)
$sql = "SELECT s.student_id, s.student_name, u.username as assessor_name, i.company_name, a.total_score, a.qualitative_comments 
        FROM Assessments a
        JOIN Internships i ON a.internship_id = i.internship_id
        JOIN Students s ON i.student_id = s.student_id
        JOIN Users u ON i.assessor_id = u.user_id
        WHERE 1=1"; 

// 权限控制：如果是 Assessor，强制在 SQL 里加一个条件，只能看自己打分的学生
if ($role === 'Assessor') {
    $sql .= " AND i.assessor_id = ?";
    $params[] = $user_id;
}

// 搜索过滤：如果用户在搜索框输入了内容，拼接 LIKE 语句
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_term = "%" . trim($_GET['search']) . "%";
    $sql .= " AND (s.student_id LIKE ? OR s.student_name LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Results</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1000px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #17a2b8; color: white; }
        .search-box { margin-bottom: 20px; }
        input[type="text"] { padding: 8px; width: 300px; }
        button { padding: 8px 12px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <div style="margin-bottom: 20px;">
        <?php if($role === 'Admin'): ?>
            <a href="Admin/manage_students.php">⬅ Back to Admin Dashboard</a>
        <?php else: ?>
            <a href="Assessor/evaluate_student.php">⬅ Back to Assessor Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" style="color: red; float: right;">Logout</a>
    </div>

    <h2>Internship Results <?= $role === 'Assessor' ? '(Your Students)' : '(All Students)' ?></h2>

    <div class="search-box">
        <form method="GET" action="view_results.php">
            <input type="text" name="search" placeholder="Search by Student ID or Name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Search</button>
            <a href="view_results.php" style="margin-left:10px;">Clear</a>
        </form>
    </div>

    <table>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Assessor</th>
            <th>Company</th>
            <th>Total Score (/100)</th>
            <th>Comments</th>
        </tr>
        <?php if(count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['assessor_name']) ?></td>
                <td><?= htmlspecialchars($row['company_name']) ?></td>
                <td><strong><?= htmlspecialchars($row['total_score']) ?></strong></td>
                <td><?= htmlspecialchars($row['qualitative_comments']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No results found.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>