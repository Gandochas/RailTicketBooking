<?php
session_start();

// Проверка на администратора
//if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Администратор') {
//    header("Location: login.php"); // Перенаправляем неадминистраторов на страницу входа
//    exit();
//}

// Подключение к базе данных
require 'db.php';

if (isset($_POST['update_role'])) {
    $userIdToUpdate = $_POST['userID'];
    $newRole = $_POST['role'];
    $updateSql = "UPDATE Users SET Role = ? WHERE UserID = ?";
    $updateStmt = mysqli_prepare($link, $updateSql);
    mysqli_stmt_bind_param($updateStmt, "si", $newRole, $userIdToUpdate);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
}

// Получение параметров сортировки
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'UserID';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

// SQL-запрос с учетом сортировки
$sql = "SELECT UserID, Username, FullName, Email, Role FROM Users ORDER BY $sort $order";
$result = mysqli_query($link, $sql);

// Функция для создания ссылки сортировки
function sortLink($column, $currentSort, $currentOrder) {
    $newOrder = $currentSort === $column && $currentOrder === 'ASC' ? 'desc' : 'asc';
    echo "<a href='?sort=$column&order=$newOrder'>$column</a>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Список Пользователей</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h1 class="text-xl mb-4">Список Зарегистрированных Пользователей</h1>

        <table class="table-auto w-full mb-4">
            <tr class="bg-gray-200">
                <th><?php sortLink('UserID', $sort, $order); ?></th>
                <th><?php sortLink('Username', $sort, $order); ?></th>
                <th><?php sortLink('FullName', $sort, $order); ?></th>
                <th><?php sortLink('Email', $sort, $order); ?></th>
                <th><?php sortLink('Role', $sort, $order); ?></th>
                <th></th>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<form method='post' class='bg-white'>";
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['UserID']) . "<input type='hidden' name='userID' value='" . $row['UserID'] . "'></td>";
                echo "<td>" . htmlspecialchars($row['Username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['FullName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                echo "<td><select name='role' class='border border-gray-300 rounded'>";
                echo "<option value='Пользователь'" . ($row['Role'] == 'Пользователь' ? ' selected' : '') . ">Пользователь</option>";
                echo "<option value='Администратор'" . ($row['Role'] == 'Администратор' ? ' selected' : '') . ">Администратор</option>";
                echo "</select></td>";
                echo "<td><button type='submit' name='update_role' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Обновить</button></td>";
                echo "</tr>";
                echo "</form>";
            }
            ?>
        </table>

        <div class="flex justify-center space-x-3">
            <button onclick="window.location.href='booking.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Вернуться на страницу бронирования</button>
            <button onclick="window.location.href='bookings_list.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Просмотреть все бронирования</button>
            <button onclick="window.location.href='add_route.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Добавить новый маршрут</button>
        </div>
    </div>
</div>
</body>
</html>

