-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Дек 21 2023 г., 23:20
-- Версия сервера: 8.0.31
-- Версия PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `railticketbooking`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `BookingID` int NOT NULL AUTO_INCREMENT,
  `UserID` int DEFAULT NULL,
  `TrainID` int DEFAULT NULL,
  `TravelDate` date DEFAULT NULL,
  `SeatNumber` int DEFAULT NULL,
  `SeatType` varchar(50) NOT NULL,
  PRIMARY KEY (`BookingID`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`BookingID`, `UserID`, `TrainID`, `TravelDate`, `SeatNumber`, `SeatType`) VALUES
(19, 6, 2, '2023-12-29', 11, 'Platskart'),
(18, 6, 5, '2023-12-30', 11, 'Coupe'),
(17, 6, 3, '2023-12-30', 52, 'Seat'),
(16, 8, 6, '2024-01-07', 7, 'Coupe'),
(12, 7, 5, '2023-12-14', 40, 'Coupe'),
(14, 6, 2, '2023-12-30', 18, 'Platskart');

-- --------------------------------------------------------

--
-- Структура таблицы `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE IF NOT EXISTS `routes` (
  `RouteID` int NOT NULL AUTO_INCREMENT,
  `StartStation` varchar(100) DEFAULT NULL,
  `EndStation` varchar(100) DEFAULT NULL,
  `StartLatitude` double DEFAULT NULL,
  `StartLongitude` double DEFAULT NULL,
  `EndLatitude` double DEFAULT NULL,
  `EndLongitude` double DEFAULT NULL,
  PRIMARY KEY (`RouteID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `routes`
--

INSERT INTO `routes` (`RouteID`, `StartStation`, `EndStation`, `StartLatitude`, `StartLongitude`, `EndLatitude`, `EndLongitude`) VALUES
(1, 'Москва', 'Санкт-Петербург', 55.7558, 37.6176, 59.9343, 30.3351),
(2, 'Санкт-Петербург', 'Нижний Новгород', 59.9343, 30.3351, 56.2965, 43.9361),
(3, 'Нижний Новгород', 'Казань', 56.2965, 43.9361, 55.8304, 49.0661),
(4, 'Казань', 'Екатеринбург', 55.8304, 49.0661, 56.838, 60.5975),
(5, 'Санкт-Петербург', 'Воркута', 59.9343, 30.3351, 67.4974, 64.0405),
(17, 'Краснодар', 'Архангельск', 45.03547, 38.975313, 64.539911, 40.515762);

-- --------------------------------------------------------

--
-- Структура таблицы `trains`
--

DROP TABLE IF EXISTS `trains`;
CREATE TABLE IF NOT EXISTS `trains` (
  `TrainID` int NOT NULL AUTO_INCREMENT,
  `TrainNumber` varchar(50) DEFAULT NULL,
  `RouteID` int DEFAULT NULL,
  `SeatCount` int DEFAULT NULL,
  `PlatskartCount` int DEFAULT NULL,
  `CoupeCount` int DEFAULT NULL,
  PRIMARY KEY (`TrainID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `trains`
--

INSERT INTO `trains` (`TrainID`, `TrainNumber`, `RouteID`, `SeatCount`, `PlatskartCount`, `CoupeCount`) VALUES
(1, '001А', 1, 50, 100, 50),
(2, '002А', 2, 40, 80, 40),
(3, '003А', 3, 50, 100, 30),
(4, '004А', 4, 100, 40, 0),
(5, '005А', 5, 100, 100, 40),
(6, '006A', 17, 20, 20, 20);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `FullName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `FullName`, `Email`, `Role`) VALUES
(7, 'Dzahbarov', '$2y$10$RQNICfwBvOSm6SYvUtDkB.q.t7Pl7ypo45dtrQBobgLIPa/I70sfS', 'Джахбаров Владимир Юрьевич', 'me@dzahbarov.ru', 'Пользователь'),
(6, 'admin', '$2y$10$6DSN0REGxLJk6vYKIS/DA.YlUnAGuuQ8eIr4DbD8/x7mC.CnEvdQm', 'Горелов Марк Андреевич', 'mark.gorelov.2018@bk.ru', 'Администратор'),
(8, 'glebich', '$2y$10$CNUKPLsCknQGATbZUT7Uzuq5p9k8cNcjAfCnMOptVC5vRuordQ052', 'Донцов Глеб Евгеньевич', 'dontcov@mail.ru', 'Администратор'),
(9, 'kozel', '$2y$10$Zz1foZ.U8kxwRldhr7eGEOTJovae16jkQG0b9kq0Ep7IQEaRv4dm6', 'Козел Игорь Маевич', 'kozel@petuh.com', 'Клиент');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
