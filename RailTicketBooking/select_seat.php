<?php
session_start();

// Проверяем, вошел ли пользователь в систему
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

// Проверяем, был ли POST-запрос
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['trainID']) && isset($_POST['travelDate']) && isset($_POST['seatType'])) {
    $trainID = $_POST['trainID'];
    $travelDate = $_POST['travelDate'];
    $selectedSeatType = $_POST['seatType'];

    // Определение максимального количества мест
    $maxSeatColumn = match ($selectedSeatType) {
        'Seat' => 'SeatCount',
        'Platskart' => 'PlatskartCount',
        'Coupe' => 'CoupeCount',
        default => exit('Неверный тип места')
    };

    // Запрос к базе данных для получения общего количества мест данного типа
    $stmt = mysqli_prepare($link, "SELECT $maxSeatColumn FROM Trains WHERE TrainID = ?");
    mysqli_stmt_bind_param($stmt, "i", $trainID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $seatCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    $maxRows = ceil($seatCount / 4);

    // Подготовка массива занятых мест
    $bookedSeats = [];
    $stmt = mysqli_prepare($link, "SELECT SeatNumber FROM Bookings WHERE TrainID = ? AND TravelDate = ? AND SeatType = ?");
    mysqli_stmt_bind_param($stmt, "iss", $trainID, $travelDate, $selectedSeatType);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookedSeats[] = $row['SeatNumber'];
    }
    mysqli_stmt_close($stmt);
} else {
    // Если данные не были переданы через POST-запрос, перенаправляем обратно на booking.php
    header("Location: booking.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Выбор места</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <h1 class="text-3xl font-bold text-center mb-4">Выбор места</h1>

    <form action="booking.php" method="post" class="mb-4">
        <input type="hidden" name="trainID" value="<?php echo htmlspecialchars($trainID); ?>">
        <input type="hidden" name="travelDate" value="<?php echo htmlspecialchars($travelDate); ?>">
        <input type="hidden" name="seatType" value="<?php echo htmlspecialchars($selectedSeatType); ?>">

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <tbody>
                <?php
                for ($row = 1; $row <= $maxRows; $row++) {
                    echo "<tr>";
                    for ($seat = 1; $seat <= 4; $seat++) {
                        $seatNumber = ($row - 1) * 4 + $seat;
                        $isTaken = in_array($seatNumber, $bookedSeats);
                        $buttonText = $isTaken ? "Занято" : "Место $seatNumber";
                        $disabledAttribute = $isTaken ? "disabled" : "";
                        echo "<td class='text-center border px-4 py-2'>";
                        echo "<button type='submit' name='seatNumber' value='$seatNumber' $disabledAttribute class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed'>$buttonText</button>";
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </form>

    <div class="flex justify-between">
        <form action="booking.php" method="post">
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Назад</button>
        </form>
        <form action="profile.php" method="get">
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Личный кабинет</button>
        </form>
    </div>
</div>
</body>
</html>

