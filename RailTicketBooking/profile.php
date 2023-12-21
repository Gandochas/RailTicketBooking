<?php
session_start();

// Проверяем, вошел ли пользователь в систему
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookingId = $data['bookingId'];

    // Проверка, принадлежит ли бронирование пользователю
    $userId = $_SESSION['user_id'];

    $deleteStmt = mysqli_prepare($link, "DELETE FROM Bookings WHERE BookingID = ? AND UserID = ?");
    mysqli_stmt_bind_param($deleteStmt, "ii", $bookingId, $userId);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    echo json_encode(['success' => true]);
    exit();
}

// Получаем информацию о пользователе (за исключением пароля)
$sqlUserInfo = "SELECT UserID, Username, FullName, Email FROM users WHERE UserID = ?";
$stmtUserInfo = mysqli_prepare($link, $sqlUserInfo);
mysqli_stmt_bind_param($stmtUserInfo, "i", $userID);
mysqli_stmt_execute($stmtUserInfo);
mysqli_stmt_bind_result($stmtUserInfo, $userID, $username, $fullName, $email);
mysqli_stmt_fetch($stmtUserInfo);
mysqli_stmt_close($stmtUserInfo);

// Получаем список бронирований пользователя
$sqlBookings = "SELECT BookingID, TrainID, TravelDate, SeatNumber, SeatType FROM Bookings WHERE UserID = ?";
$stmtBookings = mysqli_prepare($link, $sqlBookings);
mysqli_stmt_bind_param($stmtBookings, "i", $userID);
mysqli_stmt_execute($stmtBookings);
$resultBookings = mysqli_stmt_get_result($stmtBookings);

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <h1 class="text-3xl font-bold text-center mb-4">Личный кабинет</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-4">Информация о пользователе</h2>
        <p class="mb-2">Имя пользователя: <span class="text-gray-700"><?php echo htmlspecialchars($username); ?></span></p>
        <p class="mb-2">Полное имя: <span class="text-gray-700"><?php echo htmlspecialchars($fullName); ?></span></p>
        <p class="mb-4">Email: <span class="text-gray-700"><?php echo htmlspecialchars($email); ?></span></p>

        <h2 class="text-xl font-bold mb-4">Список бронирований</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">Номер бронирования</th>
                    <th class="px-4 py-2">Номер поезда</th>
                    <th class="px-4 py-2">Дата поездки</th>
                    <th class="px-4 py-2">Номер места</th>
                    <th class="px-4 py-2">Тип места</th>
                    <th class="px-4 py-2">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($rowBooking = mysqli_fetch_assoc($resultBookings)) { ?>
                    <tr>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($rowBooking['BookingID']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($rowBooking['TrainID']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($rowBooking['TravelDate']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($rowBooking['SeatNumber']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($rowBooking['SeatType']); ?></td>
                        <td class="border px-4 py-2">
                            <button onclick="cancelBooking(<?php echo $rowBooking['BookingID']; ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                Отменить
                            </button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-between">
        <form action="booking.php" method="post">
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Назад</button>
        </form>
        <form action="logout.php" method="post">
            <button type="submit" name="logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Выход</button>
        </form>
    </div>
</div>

<script>
    function cancelBooking(bookingId) {
        if (!confirm('Вы уверены, что хотите отменить это бронирование?')) return;

        fetch('profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ bookingId: bookingId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Бронирование отменено.');
                    window.location.reload(); // Перезагрузка страницы после отмены бронирования
                }
            })
            .catch(error => console.error('Ошибка:', error));
    }
</script>
</body>
</html>