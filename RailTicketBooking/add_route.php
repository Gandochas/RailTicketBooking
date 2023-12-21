<?php
session_start();

// Проверяем, вошел ли пользователь в систему и является ли он администратором
if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

$errorMsg = "";
$successMsg = "";

function getCoordinates($stationName, $apiKey) {
    $url = "https://geocode-maps.yandex.ru/1.x/?format=json&apikey=" . $apiKey . "&geocode=" . urlencode($stationName);
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Извлекаем координаты
    $coordinates = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
    return explode(" ", $coordinates);
}

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startStation = $_POST['startStation'];
    $endStation = $_POST['endStation'];
    $startLatitude = $_POST['startLatitude'];
    $startLongitude = $_POST['startLongitude'];
    $endLatitude = $_POST['endLatitude'];
    $endLongitude = $_POST['endLongitude'];
    $trainNumber = $_POST['trainNumber'];
    $seatCount = $_POST['seatCount'];
    $platskartCount = $_POST['platskartCount'];
    $coupeCount = $_POST['coupeCount'];


    // API ключ Yandex Maps
//    $apiKey = 'bcc3f0db-d708-4a49-83a8-bbadb927eb28';
//
//    // Получение координат
//    list($startLongitude, $startLatitude) = getCoordinates($startStation, $apiKey);
//    list($endLongitude, $endLatitude) = getCoordinates($endStation, $apiKey);

    // Сохранение маршрута в базе данных
    $stmt = mysqli_prepare($link, "INSERT INTO routes (StartStation, EndStation, StartLatitude, StartLongitude, EndLatitude, EndLongitude) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssdddd", $startStation, $endStation, $startLatitude, $startLongitude, $endLatitude, $endLongitude);
    if (mysqli_stmt_execute($stmt)) {

        $routeId = mysqli_insert_id($link);

        $trainStmt = mysqli_prepare($link, "INSERT INTO trains (RouteID, TrainNumber, SeatCount, PlatskartCount, CoupeCount) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($trainStmt, "isiii", $routeId, $trainNumber, $seatCount, $platskartCount, $coupeCount);

        if (mysqli_stmt_execute($trainStmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении поезда.']);
        }

        mysqli_stmt_close($trainStmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении маршрута.']);
    }
    mysqli_stmt_close($stmt);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Добавление маршрута</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h1 class="text-xl font-bold text-center mb-4">Добавление нового маршрута</h1>

        <template x-if="errorMsg">
            <p x-text="errorMsg" class="text-red-500"></p>
        </template>
        <template x-if="successMsg">
            <p x-text="successMsg" class="text-green-500"></p>
        </template>

        <form @submit.prevent="addRoute" class="space-y-4">
            <div>
                <input type="text" x-model="trainNumber" placeholder="Номер поезда" required class="w-full px-3 py-2 border rounded">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <input type="text" x-model="seatCount" placeholder="Сидячие места" required class="w-full px-3 py-2 border rounded">
                <input type="text" x-model="platskartCount" placeholder="Плацкарт" required class="w-full px-3 py-2 border rounded">
                <input type="text" x-model="coupeCount" placeholder="Купе" required class="w-full px-3 py-2 border rounded">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <input type="text" x-model="startStation" placeholder="Станция отправления" required class="w-full px-3 py-2 border rounded">
                <input type="text" x-model="endStation" placeholder="Станция назначения" required class="w-full px-3 py-2 border rounded">
            </div>

            <input type="hidden" x-model="startLatitude" name="startLatitude">
            <input type="hidden" x-model="startLongitude" name="startLongitude">
            <input type="hidden" x-model="endLatitude" name="endLatitude">
            <input type="hidden" x-model="endLongitude" name="endLongitude">

            <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Добавить маршрут и поезд</button>
        </form>

        <div class="mt-4 text-center flex justify-center space-x-3">
            <button onclick="window.location.href='users_list.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Просмотреть список пользователей</button>
            <button onclick="window.location.href='bookings_list.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Просмотреть все бронирования</button>
            <button onclick="window.location.href='booking.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Вернуться на страницу бронирования</button>
        </div>
    </div>
</div>

<script>
    function routeForm() {
        return {
            startStation: '',
            endStation: '',
            startLatitude: '',
            startLongitude: '',
            endLatitude: '',
            endLongitude: '',
            errorMsg: '',
            successMsg: '',
            trainID: '',
            trainNumber: '',
            seatCount: '',
            platskartCount: '',
            coupeCount: '',


            addRoute() {
                this.errorMsg = ''; // Очистка предыдущих сообщений об ошибке
                this.successMsg = ''; // Очистка предыдущих сообщений об успехе

                // Вызываем вычисление координат
                Promise.all([
                    this.fetchCoordinates(this.startStation),
                    this.fetchCoordinates(this.endStation)
                ]).then(([startCoords, endCoords]) => {
                    if (startCoords) {
                        this.startLatitude = startCoords[1];
                        this.startLongitude = startCoords[0];
                    }

                    if (endCoords) {
                        this.endLatitude = endCoords[1];
                        this.endLongitude = endCoords[0];
                    }

                    // После успешного получения координат отправляем данные на сервер
                    this.submitRoute();
                }).catch(error => {
                    this.errorMsg = "Ошибка при получении координат: " + error;
                });
            },

            fetchCoordinates(stationName) {
                const apiKey = 'bcc3f0db-d708-4a49-83a8-bbadb927eb28';
                const url = `https://geocode-maps.yandex.ru/1.x/?format=json&apikey=${apiKey}&geocode=${encodeURIComponent(stationName)}`;

                return fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const point = data.response.GeoObjectCollection.featureMember[0]?.GeoObject.Point.pos;
                        return point ? point.split(" ").map(Number) : null;
                    });
            },

            submitRoute() {
                const formData = new FormData();
                formData.append('startStation', this.startStation);
                formData.append('endStation', this.endStation);
                formData.append('startLatitude', this.startLatitude);
                formData.append('startLongitude', this.startLongitude);
                formData.append('endLatitude', this.endLatitude);
                formData.append('endLongitude', this.endLongitude);
                formData.append('trainID', this.trainID);
                formData.append('trainNumber', this.trainNumber);
                formData.append('seatCount', this.seatCount);
                formData.append('platskartCount', this.platskartCount);
                formData.append('coupeCount', this.coupeCount);

                fetch('add_route.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.successMsg = 'Маршрут успешно добавлен';
                            // Очистка формы
                            this.startStation = '';
                            this.endStation = '';
                            this.startLatitude = '';
                            this.startLongitude = '';
                            this.endLatitude = '';
                            this.endLongitude = '';
                            this.trainID = '';
                            this.trainNumber = '';
                            this.seatCount = '';
                            this.platskartCount = '';
                            this.coupeCount = '';
                        } else {
                            this.errorMsg = 'Ошибка при добавлении маршрута или поезда: ' + (data.error || '');
                        }
                    })
                    .catch(error => {
                        this.errorMsg = 'Сетевая ошибка: ' + error.message;
                    });
            }
        }
    }
</script>
</body>
</html>
