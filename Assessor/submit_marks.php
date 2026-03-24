<?php
// Assessor/submit_marks.php
session_start();

// 权限检查：如果没登录，或者角色不是 Assessor，直接踢回登录页
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: ../login.php");
    exit();
}

// 引入数据库连接 (注意路径：先退回上一层 ../)
require_once '../Includes/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. 接收前端传过来的所有数据
    $internship_id = $_POST['internship_id'];
    $task = $_POST['task_score'];
    $health = $_POST['health_safety_score'];
    $connectivity = $_POST['connectivity_score'];
    $report = $_POST['report_score'];
    $clarity = $_POST['clarity_score'];
    $lifelong = $_POST['lifelong_score'];
    $project = $_POST['project_mgmt_score'];
    $time = $_POST['time_mgmt_score'];
    $comments = $_POST['qualitative_comments'];

    // 2. 自动计算总分 (业务逻辑的核心要求)
    $total_score = $task + $health + $connectivity + $report + $clarity + $lifelong + $project + $time;

    // 3. 将数据插入到 Assessments 表中
    try {
        $sql = "INSERT INTO Assessments (
                    internship_id, task_score, health_safety_score, connectivity_score, 
                    report_score, clarity_score, lifelong_score, project_mgmt_score, 
                    time_mgmt_score, total_score, qualitative_comments
                ) VALUES (
                    :id, :task, :health, :conn, :rep, :clar, :life, :proj, :time_m, :total, :comm
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $internship_id, 'task' => $task, 'health' => $health, 'conn' => $connectivity,
            'rep' => $report, 'clar' => $clarity, 'life' => $lifelong, 'proj' => $project,
            'time_m' => $time, 'total' => $total_score, 'comm' => $comments
        ]);

        echo "<h3>Assessment Submitted Successfully!</h3>";
        echo "<p>Total Score Calculated: <strong>" . number_format($total_score, 2) . " / 100</strong></p>";
        echo "<a href='evaluate_student.php'>Go back</a>";

    } catch (PDOException $e) {
        die("Error submitting marks: " . $e->getMessage());
    }
} else {
    // 如果有人不填表单直接访问这个页面，让他回到打分页
    header("Location: evaluate_student.php");
}
?>