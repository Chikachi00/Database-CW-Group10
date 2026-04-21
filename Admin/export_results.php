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

// Send HTTP headers for CSV download
$filename = "internship_results_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility (so Chinese/special chars display correctly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
fputcsv($output, [
    'Student ID', 'Student Name', 'Programme', 'Assessor', 'Company',
    'Tasks (10%)', 'Health & Safety (10%)', 'Theory (10%)', 'Report (15%)',
    'Clarity (10%)', 'Lifelong (15%)', 'Proj Mgmt (15%)', 'Time Mgmt (15%)',
    'Final Weighted Score', 'Qualitative Comments'
]);

// CSV data rows
foreach ($rows as $r) {
    fputcsv($output, [
        $r['student_id'], $r['student_name'], $r['programme'], $r['assessor_name'], $r['company_name'],
        $r['task_score'], $r['health_safety_score'], $r['connectivity_score'], $r['report_score'],
        $r['clarity_score'], $r['lifelong_score'], $r['project_mgmt_score'], $r['time_mgmt_score'],
        $r['total_score'], $r['qualitative_comments']
    ]);
}

fclose($output);
exit();
?>