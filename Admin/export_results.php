<?php
// Admin/export_results.php - Export all evaluated results as CSV
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../Includes/db_connect.php';

// Fetch all evaluated students with scores and assessor names
$sql = "SELECT s.student_id, s.student_name, s.programme, u.username as assessor_name, i.company_name,
               a.task_score, a.health_safety_score, a.connectivity_score, a.report_score, 
               a.clarity_score, a.lifelong_score, a.project_mgmt_score, a.time_mgmt_score,
               a.total_score, a.qualitative_comments
        FROM Internships i
        JOIN Students s ON i.student_id = s.student_id
        JOIN Assessments a ON i.internship_id = a.internship_id
        JOIN Users u ON i.assessor_id = u.user_id
        ORDER BY s.student_id ASC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send HTTP headers to force file download
$filename = "internship_results_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Print the CSV column headers using standard echo
echo "Student ID,Student Name,Programme,Assessor,Company,Tasks (10%),Health & Safety (10%),Theory (10%),Report (15%),Clarity (10%),Lifelong (15%),Proj Mgmt (15%),Time Mgmt (15%),Final Weighted Score,Qualitative Comments\n";

// Loop through each row and echo the data separated by commas
foreach ($rows as $row) {
    // Replace double quotes inside comments to avoid breaking CSV format
    $safe_comments = str_replace('"', '""', $row['qualitative_comments']);
    
    echo '"' . $row['student_id'] . '",';
    echo '"' . $row['student_name'] . '",';
    echo '"' . $row['programme'] . '",';
    echo '"' . $row['assessor_name'] . '",';
    echo '"' . $row['company_name'] . '",';
    echo '"' . $row['task_score'] . '",';
    echo '"' . $row['health_safety_score'] . '",';
    echo '"' . $row['connectivity_score'] . '",';
    echo '"' . $row['report_score'] . '",';
    echo '"' . $row['clarity_score'] . '",';
    echo '"' . $row['lifelong_score'] . '",';
    echo '"' . $row['project_mgmt_score'] . '",';
    echo '"' . $row['time_mgmt_score'] . '",';
    echo '"' . $row['total_score'] . '",';
    echo '"' . $safe_comments . '"' . "\n";
}

exit();
?>