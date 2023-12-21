<?php
session_start();

require 'db.php';

// Убедитесь, что запрос является POST-запросом
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из JSON-запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $trainId = $data['trainId'];

    // Выполнение запроса к базе данных для получения информации о маршруте
    $sql = "SELECT r.StartStation, r.EndStation FROM trains t 
            JOIN routes r ON t.RouteID = r.RouteID 
            WHERE t.TrainID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $trainId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $startStation, $endStation);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Отправка данных обратно на фронтенд
    echo json_encode(['startStation' => $startStation, 'endStation' => $endStation]);
    exit;
}

mysqli_close($link);
?>
