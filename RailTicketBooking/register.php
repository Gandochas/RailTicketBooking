<?php
session_start();

// Подключение к базе данных
require 'db.php';

$registrationSuccess = false;
$errorMsg = "";

function validateInput($data) {
    global $link;
    $data = mysqli_real_escape_string($link, $data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = validateInput($_POST['username']);
    $password = $_POST['password'];
    $fullName = validateInput($_POST['fullName']);
    $email = validateInput($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный email адрес.";
    } elseif (strlen($password) < 8) {
        $errorMsg = "Пароль должен содержать минимум 8 символов.";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT); // Хэширование пароля
        $role = 'Клиент'; // Роль по умолчанию

        // Вставка данных в базу
        $sql = "INSERT INTO Users (Username, Password, FullName, Email, Role) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        $role = 'Клиент';

        mysqli_stmt_bind_param($stmt, "sssss", $username, $password, $fullName, $email, $role);

        if (mysqli_stmt_execute($stmt)) {
            $registrationSuccess = true;
            $_SESSION['user_id'] = mysqli_insert_id($link);
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
        } else {
            $errorMsg = "Ошибка: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}

if ($registrationSuccess) {
    header("Location: login.php"); // Перенаправление на страницу бронирования
    exit();
} else {
    // HTML-код формы регистрации
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Регистрация</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="styles.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
    <div class="container mx-auto px-4 py-5">
        <div class="max-w-md mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl mb-4">Форма Регистрации</h2>
            <?php if ($errorMsg) {
                echo "<p class='text-red-500'>$errorMsg</p>";
            } ?>
            <form action="register.php" method="post">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Имя пользователя:</label>
                    <input type="text" id="username" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Пароль:</label>
                    <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="fullName" class="block text-gray-700 text-sm font-bold mb-2">Полное имя:</label>
                    <input type="text" id="fullName" name="fullName" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Регистрация</button>
                </div>
            </form>
            <p class="text-center text-gray-600 text-lg mt-4">
                Уже зарегистрированы? <a href="login.php" class="text-blue-500 hover:text-blue-800">Вход</a>
            </p>
        </div>
    </div>
    </body>
    </html>

    <?php
}
?>
