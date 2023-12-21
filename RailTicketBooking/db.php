<?php
$databaseHost = 'localhost';
$databaseName = 'railticketbooking';
$databaseUsername = 'root';
$databasePassword = '';

// Устанавливаем соединение
$link = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);

// Проверяем соединение
if (!$link) {
    die("Ошибка подключения: " . mysqli_connect_error());
}
?>
