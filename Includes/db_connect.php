<?php
// Includes/db_connect.php
$host = 'localhost';
$dbname = 'COMP1044_CW_DB'; // 咱们之前SQL里建的数据库名
$username = 'root';         // XAMPP 默认账号
$password = '';             // XAMPP 默认密码为空

try {
    // 使用 PDO 连接数据库
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // 设置报错模式为抛出异常，方便排错
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 如果连接失败，网页会直接停止并显示错误信息
    die("Database connection failed: " . $e->getMessage());
}
?>