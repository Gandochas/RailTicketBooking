<?php
session_start();

// Проверяем, вошел ли пользователь в систему
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

// Обработка формы бронирования
$bookingSuccess = false;
//$maxRows = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $trainID = $_POST['trainID'];
    $travelDate = $_POST['travelDate'];
    $userID = $_SESSION['user_id'];
    $selectedSeatType = $_POST['seatType'];
    $seatType = $_POST['seatType'];

    // Определение максимального количества мест
    $maxSeatColumn = match ($seatType) {
        'Seat' => 'SeatCount',
        'Platskart' => 'PlatskartCount',
        'Coupe' => 'CoupeCount',
        default => exit('Неверный тип места')
    };

    $sql = "SELECT $maxSeatColumn FROM Trains WHERE TrainID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $trainID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $seatCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
//    $maxRows = ceil($seatCount / 4);

    $sql = "SELECT SeatNumber FROM Bookings WHERE TrainID = ? AND TravelDate = ? AND SeatType = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $trainID, $travelDate, $seatType);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bookedSeats = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

// Создаем массив доступных мест
    $seats = [];
    for ($i = 1; $i <= $seatCount; $i++) {
        $seats[] = ['number' => $i, 'isBooked' => in_array($i, array_column($bookedSeats, 'SeatNumber'))];
    }

    // Возвращаем информацию в формате JSON
    header('Content-Type: application/json');
    echo json_encode(['seats' => $seats]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['seatNumber'])) {
        // Обработка бронирования места
        $trainID = $_POST['trainID'];
        $travelDate = $_POST['travelDate'];
        $seatType = $_POST['seatType'];
        $seatNumber = $_POST['seatNumber'];
        $userID = $_SESSION['user_id'];

        // Проверка, занято ли место
        $stmt = mysqli_prepare($link, "SELECT COUNT(*) FROM Bookings WHERE TrainID = ? AND TravelDate = ? AND SeatNumber = ?");
        mysqli_stmt_bind_param($stmt, "isi", $trainID, $travelDate, $seatNumber);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $seatBookedCount);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($seatBookedCount == 0) {
            $sqlBooking = "INSERT INTO Bookings (UserID, TrainID, TravelDate, SeatNumber, SeatType) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sqlBooking);
            mysqli_stmt_bind_param($stmt, "iisss", $userID, $trainID, $travelDate, $seatNumber, $seatType);
            if (mysqli_stmt_execute($stmt)) {
                $bookingSuccess = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<p>Ошибка: выбранное место уже забронировано.</p>";
        }
    }
}

$sql = "SELECT * FROM Trains";
$result = mysqli_query($link, $sql);

$sqlTrains = "SELECT t.TrainID, t.TrainNumber, r.StartStation, r.EndStation 
              FROM trains t 
              JOIN routes r ON t.RouteID = r.RouteID";
$resultTrains = mysqli_query($link, $sqlTrains);

$userID = $_SESSION['user_id'];
$stmt = mysqli_prepare($link, "SELECT Role FROM Users WHERE UserID = ?");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $userRole);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Бронирование</title>
    <!-- Стили и прочее -->
    <!-- Ссылка на Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <!-- Ссылка на Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://api-maps.yandex.ru/2.1/?apikey=bcc3f0db-d708-4a49-83a8-bbadb927eb28&lang=ru_RU" type="text/javascript"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold text-center my-4">Страница Бронирования</h1>

    <!-- Кнопка "Админ-панель" -->
    <?php if ($userRole === 'Администратор') { ?>
        <a href="add_route.php" class="button-primary">Админ-панель</a>
    <?php } ?>
    <a href="profile.php" class="button-primary">Личный кабинет</a>

    <div class="my-4">
        <h2 class="text-xl font-semibold">Информация о поездах</h2>
        <table class="table-auto w-full mt-2">
            <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2">Номер поезда</th>
                <th class="px-4 py-2">Станция отправления</th>
                <th class="px-4 py-2">Станция прибытия</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($train = mysqli_fetch_assoc($resultTrains)) { ?>
                <tr>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($train['TrainNumber']); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($train['StartStation']); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($train['EndStation']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

<div id="map" class="w-full h-64 my-4" style="width: 600px; height: 400px;"></div>

    <div class="my-4">
        <?php if ($bookingSuccess) echo "<p class='text-green-500'>Бронирование успешно!</p>"; ?>
        <h3 class="text-xl font-semibold">Выберите поезд и место</h3>
        <form action="select_seat.php" method="post" class="my-4">
            <!-- Маршрут -->
            <div class="mb-4">
                <label for="trainID" class="block text-gray-700 text-sm font-bold mb-2">Маршрут:</label>
                <select name="trainID" id="trainID" onchange="updateMapWithTrainRoute(this.value)" class="block appearance-none w-full bg-white border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['TrainID'] . "'>" . $row['TrainNumber'] . "</option>";
                    } ?>
                </select>
            </div>

            <!-- Дата поездки -->
            <div class="mb-4">
                <label for="travelDate" class="block text-gray-700 text-sm font-bold mb-2">Дата поездки:</label>
                <input type="date" id="travelDate" name="travelDate" required class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal">
            </div>

            <!-- Тип места -->
            <div class="mb-4">
                <label for="seatType" class="block text-gray-700 text-sm font-bold mb-2">Тип места:</label>
                <select name="seatType" id="seatType" class="block appearance-none w-full bg-white border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="">Выберите тип места</option>
                    <option value="Seat">Обычное место</option>
                    <option value="Platskart">Плацкарт</option>
                    <option value="Coupe">Купе</option>
                </select>
            </div>

            <!-- Кнопка -->
            <div class="mb-4">
                <input type="submit" value="Выбрать место" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
            </div>
        </form>
        <form action="logout.php" method="post">
            <button type="submit" name="logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Выход</button>
        </form>
    </div>
</div>
<script>
    var myMap;
    ymaps.ready(init);

    function init() {
        myMap = new ymaps.Map("map", {
            center: [55.76, 37.64], // Установите центр карты по умолчанию
            zoom: 7
        });
    }

    function updateMapWithTrainRoute(trainID) {
        // Предполагается, что у вас есть способ получения станций отправления и прибытия по trainID
        fetch('getRouteByTrainId.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ trainId: trainID })
        })
            .then(response => response.json())
            .then(data => {
                var multiRoute = new ymaps.multiRouter.MultiRoute({
                    referencePoints: [
                        data.startStation, // Станция отправления
                        data.endStation    // Станция прибытия
                    ]
                });

                myMap.geoObjects.removeAll();
                myMap.geoObjects.add(multiRoute);
            })
            .catch(error => console.error('Ошибка:', error));
    }
</script>
</body>
</html>
