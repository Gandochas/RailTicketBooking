<?php
session_start();

// Удаляем сессию пользователя
session_destroy();

// Перенаправляем пользователя на страницу входа
header("Location: login.php");
exit();
?>
