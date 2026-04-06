<?php
require_once '../Includes/db_connect.php';

if (isset($_GET['q'])) {
    $q = $_GET['q'];

    $stmt = $pdo->prepare("
        SELECT DISTINCT programme 
        FROM Students 
        WHERE programme LIKE :query 
        LIMIT 5
    ");

    $stmt->execute(['query' => "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($results);
}
?>