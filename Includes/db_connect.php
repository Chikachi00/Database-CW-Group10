<?php
// Includes/db_connect.php
$host = 'localhost';
$dbname = 'COMP1044_database'; 
$username = 'root';         
$password = 'root';           

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    //set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    //error handling
    die("Database connection failed: " . $e->getMessage());
}
?>