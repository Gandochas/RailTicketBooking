<?php
session_start();
require 'db.php';

// Обработка запроса на удаление бронирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['bookingId'])) {
        $bookingId = $data['bookingId'];

        $deleteStmt = mysqli_prepare($link, "DELETE FROM Bookings WHERE BookingID = ?");
        mysqli_stmt_bind_param($deleteStmt, "i", $bookingId);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);

        // Отправка JSON ответа
        echo json_encode(['success' => true]);
        exit();
    }
}

// Выполните SQL-запрос для получения всех бронирований
$sql = "SELECT * FROM Bookings";
$result = mysqli_query($link, $sql);

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Список бронирований</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h1 class="text-xl mb-4">Список бронирований</h1>

        <table class="table-auto w-full mb-4">
            <thead class="bg-gray-200">
            <tr>
                <th>BookingID</th>
                <th>UserID</th>
                <th>TrainID</th>
                <th>TravelDate</th>
                <th>SeatNumber</th>
                <th>SeatType</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['BookingID'] . "</td>";
                    echo "<td>" . $row['UserID'] . "</td>";
                    echo "<td>" . $row['TrainID'] . "</td>";
                    echo "<td>" . $row['TravelDate'] . "</td>";
                    echo "<td>" . $row['SeatNumber'] . "</td>";
                    echo "<td>" . $row['SeatType'] . "</td>";
                    echo "<td><button @click='deleteBooking(" . $row['BookingID'] . ")' class='bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded'>Удалить</button></td>";
                    echo "</tr>";
                }
            }
            ?>
            </tbody>
        </table>

        <div class="flex justify-center space-x-3">
            <button onclick="window.location.href='users_list.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Просмотреть список пользователей</button>
            <button onclick="window.location.href='add_route.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Добавить новый маршрут</button>
            <button onclick="window.location.href='booking.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Вернуться на страницу бронирования</button>
        </div>
    </div>

</div>

<script>
    function bookingList() {
        return {
            deleteBooking(bookingId) {
                fetch('bookings_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ bookingId: bookingId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }
        }
    }
</script>
</body>
</html>
