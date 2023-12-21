<?php
session_start();

// Подключение к базе данных
require 'db.php';

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    //    $roleDB = $_POST['role'];

    // Проверка учетных данных
    $sql = "SELECT UserID, Username, Password, Role FROM Users WHERE Username = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $userID, $usernameDB, $passwordDB, $roleDB);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $passwordDB)) {
            // Установка сессии
            $_SESSION['user_id'] = $userID;
            $_SESSION['username'] = $usernameDB;
            $_SESSION['role'] = $roleDB;

            header("Location: booking.php");

//            if ($_SESSION['role'] === 'Администратор') {
//                header("Location: users_list.php");
//                exit();
//            } else {
//                header("Location: booking.php");
//                exit();
//            }
        } else {
            $errorMsg = "Неверное имя пользователя или пароль.";
        }
    } else {
        $errorMsg = "Пользователь не найден.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-5">
    <div class="max-w-md mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h1 class="text-xl font-bold text-center mb-4">Вход в систему</h1>

        <?php if ($errorMsg) echo "<p class='text-red-500 text-center'>$errorMsg</p>"; ?>

        <form action="login.php" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Имя пользователя:</label>
                <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Пароль:</label>
                <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Войти</button>
                <a href="register.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Регистрация</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>