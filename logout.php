<?php
// logout.php
session_start();
session_unset();     // 清空所有的 Session 变量
session_destroy();   // 销毁 Session
header("Location: login.php"); // 把用户踢回登录界面
exit();
?>