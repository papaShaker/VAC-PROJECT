-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2024 a las 12:15:32
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `laravel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_types`
--

CREATE TABLE `contract_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(300) NOT NULL,
  `working_hours` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contract_types`
--

INSERT INTO `contract_types` (`id`, `name`, `description`, `working_hours`) VALUES
(1, 'Fijo 100%', '', 8.00),
(2, 'Fijo 62,5 %', '5 horas', 5.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `homerti` int(11) NOT NULL,
  `vacalia` int(11) NOT NULL,
  `users_same_day_holidays` int(11) NOT NULL DEFAULT 0,
  `holidays_per_month` double(10,2) NOT NULL DEFAULT 2.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departments`
--

INSERT INTO `departments` (`id`, `name`, `homerti`, `vacalia`, `users_same_day_holidays`, `holidays_per_month`) VALUES
(1, 'Informática', 0, 0, 2, 2.00),
(2, 'Reservas', 0, 0, 0, 2.00),
(3, 'Atención', 0, 0, 0, 2.00),
(4, 'Administración', 0, 0, 0, 1.90),
(5, 'Gestor', 0, 0, 2, 2.00),
(6, 'Booking', 0, 0, 2, 2.00),
(7, 'Diseño y fotografía', 0, 0, 2, 2.00),
(8, 'Recepción', 0, 0, 1, 2.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_days`
--

CREATE TABLE `extra_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `day_worked` date NOT NULL,
  `extra_day_type` int(11) NOT NULL,
  `user_insert` int(11) NOT NULL,
  `user_delete` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_day_types`
--

CREATE TABLE `extra_day_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `bonus_percentage` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `year_request` int(11) DEFAULT NULL,
  `status_type` int(11) NOT NULL DEFAULT 1,
  `validation_user` int(11) DEFAULT NULL,
  `validation_date` timestamp NULL DEFAULT NULL,
  `validation_comment` varchar(200) DEFAULT 'Disponible al validar.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `holidays`
--

INSERT INTO `holidays` (`id`, `user_id`, `date`, `year_request`, `status_type`, `validation_user`, `validation_date`, `validation_comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, '2024-10-25', NULL, 2, 2, '2024-10-21 13:20:54', 'Viaje', NULL, '2024-10-23 08:18:18', '2024-10-23 08:18:18'),
(2, 3, '2024-10-28', 2024, 2, 2, NULL, NULL, NULL, NULL, NULL),
(3, 5, '2024-10-28', 2024, 2, 2, NULL, NULL, NULL, NULL, NULL),
(5, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 09:08:13', '2024-10-23 09:08:13'),
(6, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 09:15:34', '2024-10-23 09:15:34'),
(7, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 09:17:19', '2024-10-23 09:17:19'),
(8, 2, '2024-10-25', 2024, 2, 2, '2024-10-21 13:20:54', 'Viaje', NULL, '2024-10-23 09:18:35', '2024-10-23 09:18:35'),
(9, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 09:31:10', '2024-10-23 09:31:10'),
(10, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:02:36', '2024-10-23 10:02:36'),
(11, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:03:56', '2024-10-23 10:03:56'),
(12, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:06:47', '2024-10-23 10:06:47'),
(13, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:08:02', '2024-10-23 10:08:02'),
(14, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:17:36', '2024-10-23 10:17:36'),
(15, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:19:08', '2024-10-23 10:19:08'),
(16, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:21:38', '2024-10-23 10:21:38'),
(17, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:28:50', '2024-10-23 10:28:50'),
(18, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 10:30:31', '2024-10-23 10:30:31'),
(19, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-23 11:03:23', '2024-10-23 11:03:23'),
(20, 2, '2024-10-25', 2024, 2, 2, '2024-10-26 13:03:52', 'Viaje', '2024-10-23 13:04:54', '2024-10-23 11:18:25', '2024-10-23 11:18:25'),
(21, 2, '2024-10-25', 2024, 2, 2, NULL, 'Viaje', NULL, '2024-10-24 12:31:07', '2024-10-24 12:31:07'),
(22, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 06:58:10', '2024-10-24 06:59:34', '2024-10-24 06:59:34'),
(23, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:01:25', '2024-10-24 07:01:53', '2024-10-24 07:01:53'),
(24, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:02:17', '2024-10-24 07:02:25', '2024-10-24 07:02:25'),
(25, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:04:23', '2024-10-24 07:04:48', '2024-10-24 07:04:48'),
(26, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:05:53', '2024-10-24 07:06:28', '2024-10-24 07:06:28'),
(27, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:06:33', '2024-10-24 07:07:49', '2024-10-24 07:07:49'),
(28, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:07:56', '2024-10-24 07:10:12', '2024-10-24 07:10:12'),
(29, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:14:25', '2024-10-24 07:17:52', '2024-10-24 07:17:52'),
(30, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:17:58', '2024-10-24 07:18:46', '2024-10-24 07:18:46'),
(31, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:18:53', '2024-10-24 07:20:58', '2024-10-24 07:20:58'),
(32, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:21:54', '2024-10-24 07:23:22', '2024-10-24 07:23:22'),
(33, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:23:42', '2024-10-24 07:23:58', '2024-10-24 07:23:58'),
(34, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:24:02', '2024-10-24 07:27:21', '2024-10-24 07:27:21'),
(35, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:27:25', '2024-10-24 07:29:12', '2024-10-24 07:29:12'),
(36, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:29:28', '2024-10-24 07:30:05', '2024-10-24 07:30:05'),
(37, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:30:08', '2024-10-24 07:31:41', '2024-10-24 07:31:41'),
(38, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:31:47', '2024-10-24 07:33:09', '2024-10-24 07:33:09'),
(39, 2, '2024-10-31', 2024, 2, NULL, NULL, NULL, '2024-10-24 07:33:12', '2024-10-24 07:33:37', '2024-10-24 07:33:37'),
(40, 2, '2024-10-31', 2024, 1, NULL, NULL, NULL, '2024-10-24 07:34:48', '2024-10-24 07:37:51', '2024-10-24 07:37:51'),
(41, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:37:53', '2024-10-24 07:38:57', '2024-10-24 07:38:57'),
(42, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:38:59', '2024-10-24 07:39:22', '2024-10-24 07:39:22'),
(43, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:39:24', '2024-10-24 07:39:30', '2024-10-24 07:39:30'),
(44, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:39:35', '2024-10-24 07:39:41', '2024-10-24 07:39:41'),
(45, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:39:47', '2024-10-24 07:41:58', '2024-10-24 07:41:58'),
(46, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:44:08', '2024-10-24 07:46:39', '2024-10-24 07:46:39'),
(47, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:46:45', '2024-10-24 07:46:57', '2024-10-24 07:46:57'),
(48, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:47:01', '2024-10-24 07:47:15', '2024-10-24 07:47:15'),
(49, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:47:19', '2024-10-24 07:48:00', '2024-10-24 07:48:00'),
(50, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:48:06', '2024-10-24 07:48:37', '2024-10-24 07:48:37'),
(51, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:48:43', '2024-10-24 07:51:20', '2024-10-24 07:51:20'),
(52, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 07:51:22', '2024-10-24 08:00:03', '2024-10-24 08:00:03'),
(53, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 08:00:31', '2024-10-24 08:00:37', '2024-10-24 08:00:37'),
(54, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 08:04:30', '2024-10-24 08:04:33', '2024-10-24 08:04:33'),
(55, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 08:04:52', '2024-10-24 08:05:03', '2024-10-24 08:05:03'),
(56, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 08:07:47', '2024-10-24 08:07:50', '2024-10-24 08:07:50'),
(57, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 08:07:53', '2024-10-24 08:07:55', '2024-10-24 08:07:55'),
(58, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 11:50:43', '2024-10-24 11:53:16', '2024-10-24 11:53:16'),
(59, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 11:53:27', '2024-10-24 11:54:36', '2024-10-24 11:54:36'),
(60, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 11:54:41', '2024-10-24 11:56:14', '2024-10-24 11:56:14'),
(61, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 11:56:17', '2024-10-24 11:58:18', '2024-10-24 11:58:18'),
(62, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 11:58:21', '2024-10-24 12:00:34', '2024-10-24 12:00:34'),
(63, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:00:38', '2024-10-24 12:05:42', '2024-10-24 12:05:42'),
(64, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:05:52', '2024-10-24 12:08:53', '2024-10-24 12:08:53'),
(65, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:08:58', '2024-10-24 12:09:31', '2024-10-24 12:09:31'),
(66, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:09:33', '2024-10-24 12:10:38', '2024-10-24 12:10:38'),
(67, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:10:45', '2024-10-24 12:12:08', '2024-10-24 12:12:08'),
(68, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:12:12', '2024-10-24 12:13:57', '2024-10-24 12:13:57'),
(69, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:26:10', '2024-10-24 12:30:56', '2024-10-24 12:30:56'),
(70, 2, '2024-10-25', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:31:19', '2024-10-24 12:31:35', '2024-10-24 12:31:35'),
(71, 2, '2024-10-31', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:31:40', '2024-10-24 12:32:57', '2024-10-24 12:32:57'),
(72, 2, '2024-10-29', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-10-24 12:31:40', '2024-10-24 12:32:59', '2024-10-24 12:32:59'),
(76, 2, '2024-11-11', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-11-11 11:21:26', '2024-11-11 12:04:50', '2024-11-11 12:04:50'),
(77, 2, '2024-11-12', 2024, 2, 5, '2024-11-12 09:35:40', 'Disponible al validar.', '2024-11-11 12:06:30', '2024-11-12 09:35:40', NULL),
(78, 2, '2024-11-13', 2024, 3, 14, '2024-11-19 09:36:40', 'Disponible al validar.', '2024-11-11 12:06:30', '2024-11-19 09:36:40', NULL),
(79, 5, '2024-11-14', 2024, 3, 5, '2024-11-19 09:38:14', 'Disponible al validar.', '2024-11-12 09:02:46', '2024-11-19 09:38:14', NULL),
(80, 14, '2024-11-13', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-11-12 14:50:27', '2024-11-12 14:50:27', NULL),
(81, 14, '2024-11-26', 2024, 2, 14, '2024-11-18 22:47:13', 'Disponible al validar.', '2024-11-18 22:46:35', '2024-11-18 22:47:13', NULL),
(82, 5, '2024-11-20', 2024, 3, 5, '2024-11-21 12:44:36', 'Disponible al validar.', '2024-11-19 09:37:44', '2024-11-21 12:44:36', NULL),
(83, 5, '2024-11-21', 2024, 3, 5, '2024-11-19 09:43:11', 'Disponible al validar.', '2024-11-19 09:42:28', '2024-11-19 09:43:11', NULL),
(84, 5, '2024-11-21', 2024, 2, 5, '2024-11-19 09:48:02', 'Disponible al validar.', '2024-11-19 09:47:47', '2024-11-21 12:45:37', '2024-11-21 12:45:37'),
(85, 5, '2024-11-22', 2024, 2, 5, '2024-11-19 12:27:02', 'Disponible al validar.', '2024-11-19 12:26:38', '2024-11-19 12:27:02', NULL),
(86, 5, '2024-11-27', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-11-21 09:03:09', '2024-11-21 09:06:10', '2024-11-21 09:06:10'),
(87, 5, '2024-11-27', 2024, 2, 5, '2024-11-21 09:06:50', 'Disponible al validar.', '2024-11-21 09:06:32', '2024-11-21 09:07:00', '2024-11-21 09:07:00'),
(88, 5, '2024-11-28', 2024, 2, 5, '2024-11-21 09:06:50', 'Disponible al validar.', '2024-11-21 09:06:32', '2024-11-21 09:07:03', '2024-11-21 09:07:03'),
(89, 5, '2024-11-29', 2024, 2, 5, '2024-11-21 09:06:50', 'Disponible al validar.', '2024-11-21 09:06:32', '2024-11-21 09:07:05', '2024-11-21 09:07:05'),
(90, 2, '2024-12-18', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-12-04 14:01:31', '2024-12-04 14:01:31', NULL),
(91, 2, '2024-12-19', 2024, 3, 2, '2024-12-04 14:02:37', 'Disponible al validar.', '2024-12-04 14:01:31', '2024-12-04 14:02:37', NULL),
(92, 2, '2024-12-26', 2024, 3, 2, '2024-12-04 14:17:34', 'Disponible al validar.', '2024-12-04 14:01:31', '2024-12-04 14:17:34', NULL),
(93, 2, '2024-12-19', 2024, 3, 2, '2024-12-04 14:13:35', 'Disponible al validar.', '2024-12-04 14:08:08', '2024-12-04 14:13:35', NULL),
(94, 5, '2024-12-10', 2024, 2, 5, '2024-12-05 09:34:42', 'Disponible al validar.', '2024-12-05 09:10:40', '2024-12-05 09:34:42', NULL),
(95, 5, '2024-12-17', 2024, 2, 5, '2024-12-05 09:11:01', 'Disponible al validar.', '2024-12-05 09:10:40', '2024-12-05 09:11:01', NULL),
(96, 9, '2024-12-18', 2024, 1, NULL, NULL, 'Disponible al validar.', '2024-12-05 09:51:25', '2024-12-05 09:51:25', NULL),
(97, 5, '2024-12-20', 2024, 3, 5, '2024-12-05 12:37:29', 'Disponible al validar.', '2024-12-05 12:34:31', '2024-12-05 12:37:29', NULL),
(98, 5, '2024-12-12', 2024, 3, 5, '2024-12-05 12:37:29', 'Disponible al validar.', '2024-12-05 12:34:31', '2024-12-05 12:37:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holidays_paid`
--

CREATE TABLE `holidays_paid` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `days` double NOT NULL,
  `comment` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holiday_status_types`
--

CREATE TABLE `holiday_status_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `holiday_status_types`
--

INSERT INTO `holiday_status_types` (`id`, `name`) VALUES
(1, 'Pendiente'),
(2, 'Confirmada'),
(3, 'Rechazada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holiday_zones`
--

CREATE TABLE `holiday_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone` varchar(200) NOT NULL,
  `parent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `holiday_zones`
--

INSERT INTO `holiday_zones` (`id`, `zone`, `parent`) VALUES
(1, 'España', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_ranges`
--

CREATE TABLE `job_ranges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contract_type` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `work_on_freedays` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `job_ranges`
--

INSERT INTO `job_ranges` (`id`, `contract_type`, `start_date`, `end_date`, `user_id`, `work_on_freedays`) VALUES
(1, 1, '2024-10-10', '2024-12-31', 2, 1),
(4, 1, '2024-07-31', '2025-09-30', 8, 0),
(5, 1, '2024-07-31', '2025-09-30', 3, 1),
(6, 1, '2024-07-31', '2025-09-30', 5, 1),
(7, 2, '2024-10-01', '2025-02-28', 9, 0),
(8, 1, '2024-09-01', '2025-03-31', 10, 1),
(9, 1, '2024-09-01', '2025-02-28', 11, 1),
(10, 1, '2024-09-30', '2025-01-31', 12, 1),
(11, 1, '2024-08-01', '2025-03-31', 13, 1),
(12, 1, '2024-09-01', '2025-02-28', 14, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(7, '2014_10_12_000000_create_users_table', 1),
(8, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(9, '2019_08_19_000000_create_failed_jobs_table', 1),
(10, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(11, '2024_10_15_101940_create_departments_table', 1),
(12, '2024_10_15_104932_create_holidayzones_table', 1),
(13, '2024_10_16_082422_create_extra_days_table', 2),
(14, '2024_10_16_085443_create_holidays_table', 2),
(15, '2024_10_16_091349_create_job_ranges_table', 2),
(16, '2024_10_16_092340_create_holidays_paid_table', 3),
(17, '2024_10_16_093100_create_contract_types_table', 3),
(18, '2024_10_16_093533_create_extra_day_types_table', 3),
(19, '2024_10_16_093946_create_non_working_days_table', 3),
(20, '2024_10_16_094150_create_non_working_day_zones_table', 3),
(21, '2024_10_16_095442_create_holiday_status_types_table', 3),
(22, '2024_10_20_135901_add_deleted_at_to_holidays_table', 4),
(23, '2024_10_22_112713_add_new_column_to_users_table', 5),
(24, '2024_11_05_105105_create_user_availability_table', 6),
(25, '2024_11_05_103759_create_night_shifts_table', 7),
(26, '2024_11_05_103836_create_weekly_schedules_table', 8),
(27, '2024_11_27_145312_add_rotation_column_to_weekly_schedules_table', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `night_shifts`
--

CREATE TABLE `night_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `week_number` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `non_working_days`
--

CREATE TABLE `non_working_days` (
  `day` date NOT NULL,
  `description` varchar(200) NOT NULL,
  `zone` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `non_working_days`
--

INSERT INTO `non_working_days` (`day`, `description`, `zone`) VALUES
('2024-11-01', 'Día de todos Los Santos.', 1),
('2024-12-06', 'Día de la Constitución Española', 1),
('2024-11-15', 'Fiesta Inventada', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `non_working_day_zones`
--

CREATE TABLE `non_working_day_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone` varchar(200) NOT NULL,
  `parent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `non_working_day_zones`
--

INSERT INTO `non_working_day_zones` (`id`, `zone`, `parent`) VALUES
(1, 'España', NULL),
(2, 'Islas Baleares', 1),
(3, 'Inca', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL COMMENT '1 for Monday, 7 for Sunday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_free_day` tinyint(1) NOT NULL DEFAULT 0,
  `user_availability_id` bigint(20) UNSIGNED NOT NULL,
  `user_group` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `schedules`
--

INSERT INTO `schedules` (`id`, `day_of_week`, `start_time`, `end_time`, `is_free_day`, `user_availability_id`, `user_group`, `created_at`, `updated_at`) VALUES
(1, 1, '09:00:00', '17:00:00', 0, 1, 0, NULL, NULL),
(2, 2, '09:00:00', '17:00:00', 0, 1, 0, NULL, NULL),
(3, 3, '09:00:00', '17:00:00', 0, 1, 0, NULL, NULL),
(4, 4, '09:00:00', '17:00:00', 0, 1, 0, NULL, NULL),
(5, 5, '09:00:00', '17:00:00', 0, 1, 0, NULL, NULL),
(6, 6, '00:00:00', '00:00:00', 1, 1, 0, NULL, NULL),
(7, 7, '00:00:00', '00:00:00', 1, 1, 0, NULL, NULL),
(8, 1, '09:00:00', '17:00:00', 0, 1, 1, NULL, NULL),
(9, 2, '09:00:00', '17:00:00', 0, 1, 1, NULL, NULL),
(10, 3, '09:00:00', '17:00:00', 0, 1, 1, NULL, NULL),
(11, 4, '00:00:00', '00:00:00', 1, 1, 1, NULL, NULL),
(12, 5, '00:00:00', '00:00:00', 1, 1, 1, NULL, NULL),
(13, 6, '09:00:00', '17:00:00', 0, 1, 1, NULL, NULL),
(14, 7, '09:00:00', '17:00:00', 0, 1, 1, NULL, NULL),
(15, 1, '09:00:00', '17:00:00', 0, 1, 2, NULL, NULL),
(16, 2, '09:00:00', '17:00:00', 0, 1, 2, NULL, NULL),
(17, 3, '09:00:00', '17:00:00', 0, 1, 2, NULL, NULL),
(18, 4, '09:00:00', '17:00:00', 0, 1, 2, NULL, NULL),
(19, 5, '09:00:00', '17:00:00', 0, 1, 2, NULL, NULL),
(20, 6, '00:00:00', '00:00:00', 1, 1, 2, NULL, NULL),
(21, 7, '00:00:00', '00:00:00', 1, 1, 2, NULL, NULL),
(22, 1, '09:00:00', '17:00:00', 0, 2, 0, NULL, NULL),
(23, 2, '09:00:00', '17:00:00', 0, 2, 0, NULL, NULL),
(24, 3, '09:00:00', '17:00:00', 0, 2, 0, NULL, NULL),
(25, 4, '09:00:00', '17:00:00', 0, 2, 0, NULL, NULL),
(26, 5, '09:00:00', '17:00:00', 0, 2, 0, NULL, NULL),
(27, 6, '00:00:00', '00:00:00', 1, 2, 0, NULL, NULL),
(28, 7, '00:00:00', '00:00:00', 1, 2, 0, NULL, NULL),
(29, 1, '08:00:00', '16:00:00', 0, 2, 1, NULL, NULL),
(30, 2, '08:00:00', '16:00:00', 0, 2, 1, NULL, NULL),
(31, 3, '08:00:00', '16:00:00', 0, 2, 1, NULL, NULL),
(32, 4, '00:00:00', '00:00:00', 1, 2, 1, NULL, NULL),
(33, 5, '00:00:00', '00:00:00', 1, 2, 1, NULL, NULL),
(34, 6, '14:00:00', '22:00:00', 0, 2, 1, NULL, NULL),
(35, 7, '14:00:00', '22:00:00', 0, 2, 1, NULL, NULL),
(36, 1, '14:00:00', '22:00:00', 0, 2, 2, NULL, NULL),
(37, 2, '14:00:00', '22:00:00', 0, 2, 2, NULL, NULL),
(38, 3, '14:00:00', '22:00:00', 0, 2, 2, NULL, NULL),
(39, 4, '14:00:00', '22:00:00', 0, 2, 2, NULL, NULL),
(40, 5, '14:00:00', '22:00:00', 0, 2, 2, NULL, NULL),
(41, 6, '00:00:00', '00:00:00', 1, 2, 2, NULL, NULL),
(42, 7, '00:00:00', '00:00:00', 1, 2, 2, NULL, NULL),
(43, 1, '10:00:00', '18:00:00', 0, 2, 3, NULL, NULL),
(44, 2, '00:00:00', '00:00:00', 1, 2, 3, NULL, NULL),
(45, 3, '00:00:00', '00:00:00', 1, 2, 3, NULL, NULL),
(46, 4, '08:00:00', '16:00:00', 0, 2, 3, NULL, NULL),
(47, 5, '08:00:00', '16:00:00', 0, 2, 3, NULL, NULL),
(48, 6, '08:00:00', '16:00:00', 0, 2, 3, NULL, NULL),
(49, 7, '08:00:00', '16:00:00', 0, 2, 3, NULL, NULL),
(50, 1, '08:00:00', '13:00:00', 0, 3, 0, NULL, NULL),
(51, 2, '08:00:00', '13:00:00', 0, 3, 0, NULL, NULL),
(52, 3, '08:00:00', '13:00:00', 0, 3, 0, NULL, NULL),
(53, 4, '08:00:00', '13:00:00', 0, 3, 0, NULL, NULL),
(54, 5, '08:00:00', '13:00:00', 0, 3, 0, NULL, NULL),
(55, 6, '00:00:00', '00:00:00', 1, 3, 0, NULL, NULL),
(56, 7, '00:00:00', '00:00:00', 1, 3, 0, NULL, NULL),
(57, 1, '08:00:00', '16:00:00', 0, 3, 1, NULL, NULL),
(58, 2, '08:00:00', '16:00:00', 0, 3, 1, NULL, NULL),
(59, 3, '08:00:00', '16:00:00', 0, 3, 1, NULL, NULL),
(60, 4, '00:00:00', '00:00:00', 1, 3, 1, NULL, NULL),
(61, 5, '00:00:00', '00:00:00', 1, 3, 1, NULL, NULL),
(62, 6, '08:00:00', '16:00:00', 0, 3, 1, NULL, NULL),
(63, 7, '08:00:00', '16:00:00', 0, 3, 1, NULL, NULL),
(64, 1, '09:00:00', '17:00:00', 0, 3, 2, NULL, NULL),
(65, 2, '09:00:00', '17:00:00', 0, 3, 2, NULL, NULL),
(66, 3, '09:00:00', '17:00:00', 0, 3, 2, NULL, NULL),
(67, 4, '09:00:00', '17:00:00', 0, 3, 2, NULL, NULL),
(68, 5, '09:00:00', '17:00:00', 0, 3, 2, NULL, NULL),
(69, 6, '00:00:00', '00:00:00', 1, 3, 2, NULL, NULL),
(70, 7, '00:00:00', '00:00:00', 1, 3, 2, NULL, NULL),
(71, 1, '08:00:00', '16:00:00', 0, 3, 3, NULL, NULL),
(72, 2, '08:00:00', '16:00:00', 0, 3, 3, NULL, NULL),
(73, 3, '08:00:00', '16:00:00', 0, 3, 3, NULL, NULL),
(74, 4, '08:00:00', '16:00:00', 0, 3, 3, NULL, NULL),
(75, 5, '08:00:00', '16:00:00', 0, 3, 3, NULL, NULL),
(76, 6, '00:00:00', '00:00:00', 1, 3, 3, NULL, NULL),
(77, 7, '00:00:00', '00:00:00', 1, 3, 3, NULL, NULL),
(78, 1, '08:00:00', '13:00:00', 0, 4, 0, NULL, NULL),
(79, 2, '08:00:00', '13:00:00', 0, 4, 0, NULL, NULL),
(80, 3, '08:00:00', '13:00:00', 0, 4, 0, NULL, NULL),
(81, 4, '08:00:00', '13:00:00', 0, 4, 0, NULL, NULL),
(82, 5, '08:00:00', '13:00:00', 0, 4, 0, NULL, NULL),
(83, 6, '00:00:00', '00:00:00', 1, 4, 0, NULL, NULL),
(84, 7, '00:00:00', '00:00:00', 1, 4, 0, NULL, NULL),
(85, 1, '08:00:00', '16:00:00', 0, 4, 1, NULL, NULL),
(86, 2, '08:00:00', '16:00:00', 0, 4, 1, NULL, NULL),
(87, 3, '08:00:00', '16:00:00', 0, 4, 1, NULL, NULL),
(88, 4, '00:00:00', '00:00:00', 1, 4, 1, NULL, NULL),
(89, 5, '00:00:00', '00:00:00', 1, 4, 1, NULL, NULL),
(90, 6, '08:00:00', '16:00:00', 0, 4, 1, NULL, NULL),
(91, 7, '08:00:00', '16:00:00', 0, 4, 1, NULL, NULL),
(92, 1, '09:00:00', '17:00:00', 0, 4, 2, NULL, NULL),
(93, 2, '09:00:00', '17:00:00', 0, 4, 2, NULL, NULL),
(94, 3, '09:00:00', '17:00:00', 0, 4, 2, NULL, NULL),
(95, 4, '09:00:00', '17:00:00', 0, 4, 2, NULL, NULL),
(96, 5, '09:00:00', '17:00:00', 0, 4, 2, NULL, NULL),
(97, 6, '00:00:00', '00:00:00', 1, 4, 2, NULL, NULL),
(98, 7, '00:00:00', '00:00:00', 1, 4, 2, NULL, NULL),
(99, 1, '09:00:00', '17:00:00', 0, 4, 3, NULL, NULL),
(100, 2, '09:00:00', '17:00:00', 0, 4, 3, NULL, NULL),
(101, 3, '09:00:00', '17:00:00', 0, 4, 3, NULL, NULL),
(102, 4, '09:00:00', '17:00:00', 0, 4, 3, NULL, NULL),
(103, 5, '09:00:00', '17:00:00', 0, 4, 3, NULL, NULL),
(104, 6, '00:00:00', '00:00:00', 1, 4, 3, NULL, NULL),
(105, 7, '00:00:00', '00:00:00', 1, 4, 3, NULL, NULL),
(106, 1, '08:00:00', '16:00:00', 0, 4, 4, NULL, NULL),
(107, 2, '08:00:00', '16:00:00', 0, 4, 4, NULL, NULL),
(108, 3, '08:00:00', '16:00:00', 0, 4, 4, NULL, NULL),
(109, 4, '08:00:00', '16:00:00', 0, 4, 4, NULL, NULL),
(110, 5, '08:00:00', '16:00:00', 0, 4, 4, NULL, NULL),
(111, 6, '00:00:00', '00:00:00', 1, 4, 4, NULL, NULL),
(112, 7, '00:00:00', '00:00:00', 1, 4, 4, NULL, NULL),
(113, 1, '08:00:00', '13:00:00', 0, 5, 0, NULL, NULL),
(114, 2, '08:00:00', '13:00:00', 0, 5, 0, NULL, NULL),
(115, 3, '08:00:00', '13:00:00', 0, 5, 0, NULL, NULL),
(116, 4, '08:00:00', '13:00:00', 0, 5, 0, NULL, NULL),
(117, 5, '08:00:00', '13:00:00', 0, 5, 0, NULL, NULL),
(118, 6, '00:00:00', '00:00:00', 1, 5, 0, NULL, NULL),
(119, 7, '00:00:00', '00:00:00', 1, 5, 0, NULL, NULL),
(120, 1, '08:00:00', '16:00:00', 0, 5, 1, NULL, NULL),
(121, 2, '08:00:00', '16:00:00', 0, 5, 1, NULL, NULL),
(122, 3, '08:00:00', '16:00:00', 0, 5, 1, NULL, NULL),
(123, 4, '00:00:00', '00:00:00', 1, 5, 1, NULL, NULL),
(124, 5, '00:00:00', '00:00:00', 1, 5, 1, NULL, NULL),
(125, 6, '08:00:00', '16:00:00', 0, 5, 1, NULL, NULL),
(126, 7, '08:00:00', '16:00:00', 0, 5, 1, NULL, NULL),
(127, 1, '09:00:00', '17:00:00', 0, 5, 2, NULL, NULL),
(128, 2, '09:00:00', '17:00:00', 0, 5, 2, NULL, NULL),
(129, 3, '09:00:00', '17:00:00', 0, 5, 2, NULL, NULL),
(130, 4, '09:00:00', '17:00:00', 0, 5, 2, NULL, NULL),
(131, 5, '09:00:00', '17:00:00', 0, 5, 2, NULL, NULL),
(132, 6, '00:00:00', '00:00:00', 1, 5, 2, NULL, NULL),
(133, 7, '00:00:00', '00:00:00', 1, 5, 2, NULL, NULL),
(134, 1, '09:00:00', '17:00:00', 0, 5, 3, NULL, NULL),
(135, 2, '09:00:00', '17:00:00', 0, 5, 3, NULL, NULL),
(136, 3, '09:00:00', '17:00:00', 0, 5, 3, NULL, NULL),
(137, 4, '09:00:00', '17:00:00', 0, 5, 3, NULL, NULL),
(138, 5, '09:00:00', '17:00:00', 0, 5, 3, NULL, NULL),
(139, 6, '00:00:00', '00:00:00', 1, 5, 3, NULL, NULL),
(140, 7, '00:00:00', '00:00:00', 1, 5, 3, NULL, NULL),
(141, 1, '08:00:00', '16:00:00', 0, 5, 4, NULL, NULL),
(142, 2, '08:00:00', '16:00:00', 0, 5, 4, NULL, NULL),
(143, 3, '08:00:00', '16:00:00', 0, 5, 4, NULL, NULL),
(144, 4, '08:00:00', '16:00:00', 0, 5, 4, NULL, NULL),
(145, 5, '08:00:00', '16:00:00', 0, 5, 4, NULL, NULL),
(146, 6, '00:00:00', '00:00:00', 1, 5, 4, NULL, NULL),
(147, 7, '00:00:00', '00:00:00', 1, 5, 4, NULL, NULL),
(148, 1, '09:00:00', '17:00:00', 0, 5, 5, NULL, NULL),
(149, 2, '09:00:00', '17:00:00', 0, 5, 5, NULL, NULL),
(150, 3, '09:00:00', '17:00:00', 0, 5, 5, NULL, NULL),
(151, 4, '00:00:00', '00:00:00', 1, 5, 5, NULL, NULL),
(152, 5, '00:00:00', '00:00:00', 1, 5, 5, NULL, NULL),
(153, 6, '09:00:00', '17:00:00', 0, 5, 5, NULL, NULL),
(154, 7, '09:00:00', '17:00:00', 0, 5, 5, NULL, NULL),
(155, 1, '09:00:00', '17:00:00', 0, 6, 0, NULL, NULL),
(156, 2, '09:00:00', '17:00:00', 0, 6, 0, NULL, NULL),
(157, 3, '09:00:00', '17:00:00', 0, 6, 0, NULL, NULL),
(158, 4, '09:00:00', '17:00:00', 0, 6, 0, NULL, NULL),
(159, 5, '09:00:00', '17:00:00', 0, 6, 0, NULL, NULL),
(160, 6, '00:00:00', '00:00:00', 1, 6, 0, NULL, NULL),
(161, 7, '00:00:00', '00:00:00', 1, 6, 0, NULL, NULL),
(162, 1, '08:00:00', '16:00:00', 0, 6, 1, NULL, NULL),
(163, 2, '08:00:00', '16:00:00', 0, 6, 1, NULL, NULL),
(164, 3, '08:00:00', '16:00:00', 0, 6, 1, NULL, NULL),
(165, 4, '00:00:00', '00:00:00', 1, 6, 1, NULL, NULL),
(166, 5, '00:00:00', '00:00:00', 1, 6, 1, NULL, NULL),
(167, 6, '12:00:00', '20:00:00', 0, 6, 1, NULL, NULL),
(168, 7, '12:00:00', '20:00:00', 0, 6, 1, NULL, NULL),
(169, 1, '14:00:00', '22:00:00', 0, 6, 2, NULL, NULL),
(170, 2, '14:00:00', '22:00:00', 0, 6, 2, NULL, NULL),
(171, 3, '14:00:00', '22:00:00', 0, 6, 2, NULL, NULL),
(172, 4, '14:00:00', '22:00:00', 0, 6, 2, NULL, NULL),
(173, 5, '12:00:00', '20:00:00', 0, 6, 2, NULL, NULL),
(174, 6, '00:00:00', '00:00:00', 1, 6, 2, NULL, NULL),
(175, 7, '00:00:00', '00:00:00', 1, 6, 2, NULL, NULL),
(176, 1, '14:00:00', '22:00:00', 0, 6, 3, NULL, NULL),
(177, 2, '00:00:00', '00:00:00', 1, 6, 3, NULL, NULL),
(178, 3, '00:00:00', '00:00:00', 1, 6, 3, NULL, NULL),
(179, 3, '08:00:00', '16:00:00', 0, 6, 3, NULL, NULL),
(180, 4, '08:00:00', '16:00:00', 0, 6, 3, NULL, NULL),
(181, 5, '08:00:00', '16:00:00', 0, 6, 3, NULL, NULL),
(182, 6, '08:00:00', '16:00:00', 0, 6, 3, NULL, NULL),
(183, 7, '08:00:00', '16:00:00', 0, 6, 3, NULL, NULL),
(184, 1, '08:00:00', '16:00:00', 0, 6, 4, NULL, NULL),
(185, 2, '12:00:00', '20:00:00', 0, 6, 4, NULL, NULL),
(186, 3, '00:00:00', '00:00:00', 1, 6, 4, NULL, NULL),
(187, 4, '00:00:00', '00:00:00', 1, 6, 4, NULL, NULL),
(188, 5, '14:00:00', '22:00:00', 0, 6, 4, NULL, NULL),
(189, 6, '14:00:00', '22:00:00', 0, 6, 4, NULL, NULL),
(190, 7, '14:00:00', '22:00:00', 0, 6, 4, NULL, NULL),
(191, 1, '09:00:00', '17:00:00', 0, 7, 0, NULL, NULL),
(192, 2, '09:00:00', '17:00:00', 0, 7, 0, NULL, NULL),
(193, 3, '09:00:00', '17:00:00', 0, 7, 0, NULL, NULL),
(194, 4, '09:00:00', '17:00:00', 0, 7, 0, NULL, NULL),
(195, 5, '09:00:00', '17:00:00', 0, 7, 0, NULL, NULL),
(196, 6, '00:00:00', '00:00:00', 1, 7, 0, NULL, NULL),
(197, 7, '00:00:00', '00:00:00', 1, 7, 0, NULL, NULL),
(198, 1, '08:00:00', '16:00:00', 0, 7, 1, NULL, NULL),
(199, 2, '08:00:00', '16:00:00', 0, 7, 1, NULL, NULL),
(200, 3, '08:00:00', '16:00:00', 0, 7, 1, NULL, NULL),
(201, 4, '00:00:00', '00:00:00', 1, 7, 1, NULL, NULL),
(202, 5, '00:00:00', '00:00:00', 1, 7, 1, NULL, NULL),
(203, 6, '14:00:00', '22:00:00', 0, 7, 1, NULL, NULL),
(204, 7, '14:00:00', '22:00:00', 0, 7, 1, NULL, NULL),
(205, 1, '14:00:00', '22:00:00', 0, 7, 2, NULL, NULL),
(206, 2, '14:00:00', '22:00:00', 0, 7, 2, NULL, NULL),
(207, 3, '14:00:00', '22:00:00', 0, 7, 2, NULL, NULL),
(208, 4, '14:00:00', '22:00:00', 0, 7, 2, NULL, NULL),
(209, 5, '14:00:00', '22:00:00', 0, 7, 2, NULL, NULL),
(210, 6, '00:00:00', '00:00:00', 1, 7, 2, NULL, NULL),
(211, 7, '00:00:00', '00:00:00', 1, 7, 2, NULL, NULL),
(212, 1, '14:00:00', '22:00:00', 0, 7, 3, NULL, NULL),
(213, 2, '00:00:00', '00:00:00', 1, 7, 3, NULL, NULL),
(214, 3, '00:00:00', '00:00:00', 1, 7, 3, NULL, NULL),
(215, 4, '08:00:00', '16:00:00', 0, 7, 3, NULL, NULL),
(216, 5, '08:00:00', '16:00:00', 0, 7, 3, NULL, NULL),
(217, 6, '08:00:00', '16:00:00', 0, 7, 3, NULL, NULL),
(218, 7, '08:00:00', '16:00:00', 0, 7, 3, NULL, NULL),
(219, 1, '08:00:00', '16:00:00', 0, 7, 4, NULL, NULL),
(220, 2, '12:00:00', '20:00:00', 0, 7, 4, NULL, NULL),
(221, 3, '00:00:00', '00:00:00', 1, 7, 4, NULL, NULL),
(222, 4, '00:00:00', '00:00:00', 1, 7, 4, NULL, NULL),
(223, 5, '14:00:00', '22:00:00', 0, 7, 4, NULL, NULL),
(224, 6, '14:00:00', '22:00:00', 0, 7, 4, NULL, NULL),
(225, 7, '14:00:00', '22:00:00', 0, 7, 4, NULL, NULL),
(226, 1, '00:00:00', '00:00:00', 1, 7, 5, NULL, NULL),
(227, 2, '00:00:00', '00:00:00', 1, 7, 5, NULL, NULL),
(228, 3, '12:00:00', '20:00:00', 0, 7, 5, NULL, NULL),
(229, 4, '12:00:00', '20:00:00', 0, 7, 5, NULL, NULL),
(230, 5, '08:00:00', '16:00:00', 0, 7, 5, NULL, NULL),
(231, 6, '08:00:00', '16:00:00', 0, 7, 5, NULL, NULL),
(232, 7, '08:00:00', '16:00:00', 0, 7, 5, NULL, NULL),
(233, 1, '09:00:00', '17:00:00', 0, 8, 0, NULL, NULL),
(234, 2, '09:00:00', '17:00:00', 0, 8, 0, NULL, NULL),
(235, 3, '09:00:00', '17:00:00', 0, 8, 0, NULL, NULL),
(236, 4, '09:00:00', '17:00:00', 0, 8, 0, NULL, NULL),
(237, 5, '09:00:00', '17:00:00', 0, 8, 0, NULL, NULL),
(238, 6, '00:00:00', '00:00:00', 1, 8, 0, NULL, NULL),
(239, 7, '00:00:00', '00:00:00', 1, 8, 0, NULL, NULL),
(240, 1, '08:00:00', '16:00:00', 0, 8, 1, NULL, NULL),
(241, 2, '08:00:00', '16:00:00', 0, 8, 1, NULL, NULL),
(242, 3, '08:00:00', '16:00:00', 0, 8, 1, NULL, NULL),
(243, 4, '00:00:00', '00:00:00', 1, 8, 1, NULL, NULL),
(244, 5, '00:00:00', '00:00:00', 1, 8, 1, NULL, NULL),
(245, 6, '14:00:00', '22:00:00', 0, 8, 1, NULL, NULL),
(246, 7, '14:00:00', '22:00:00', 0, 8, 1, NULL, NULL),
(247, 1, '14:00:00', '22:00:00', 0, 8, 2, NULL, NULL),
(248, 2, '14:00:00', '22:00:00', 0, 8, 2, NULL, NULL),
(249, 3, '14:00:00', '22:00:00', 0, 8, 2, NULL, NULL),
(250, 4, '14:00:00', '22:00:00', 0, 8, 2, NULL, NULL),
(251, 5, '14:00:00', '22:00:00', 0, 8, 2, NULL, NULL),
(252, 6, '00:00:00', '00:00:00', 1, 8, 2, NULL, NULL),
(253, 7, '00:00:00', '00:00:00', 1, 8, 2, NULL, NULL),
(254, 1, '10:00:00', '18:00:00', 0, 8, 3, NULL, NULL),
(255, 2, '00:00:00', '00:00:00', 1, 8, 3, NULL, NULL),
(256, 3, '00:00:00', '00:00:00', 1, 8, 3, NULL, NULL),
(257, 4, '08:00:00', '16:00:00', 0, 8, 3, NULL, NULL),
(258, 5, '08:00:00', '16:00:00', 0, 8, 3, NULL, NULL),
(259, 6, '08:00:00', '16:00:00', 0, 8, 3, NULL, NULL),
(260, 7, '08:00:00', '16:00:00', 0, 8, 3, NULL, NULL),
(261, 1, '08:00:00', '16:00:00', 0, 8, 4, NULL, NULL),
(262, 2, '08:00:00', '16:00:00', 0, 8, 4, NULL, NULL),
(263, 3, '00:00:00', '00:00:00', 1, 8, 4, NULL, NULL),
(264, 4, '00:00:00', '00:00:00', 1, 8, 4, NULL, NULL),
(265, 5, '10:00:00', '18:00:00', 0, 8, 4, NULL, NULL),
(266, 6, '14:00:00', '22:00:00', 0, 8, 4, NULL, NULL),
(267, 7, '14:00:00', '22:00:00', 0, 8, 4, NULL, NULL),
(268, 1, '14:00:00', '22:00:00', 0, 8, 5, NULL, NULL),
(269, 2, '14:00:00', '22:00:00', 0, 8, 5, NULL, NULL),
(270, 3, '14:00:00', '22:00:00', 0, 8, 5, NULL, NULL),
(271, 4, '14:00:00', '22:00:00', 0, 8, 5, NULL, NULL),
(272, 5, '14:00:00', '22:00:00', 0, 8, 5, NULL, NULL),
(273, 6, '00:00:00', '00:00:00', 1, 8, 5, NULL, NULL),
(274, 7, '00:00:00', '00:00:00', 1, 8, 5, NULL, NULL),
(275, 1, '00:00:00', '00:00:00', 1, 8, 6, NULL, NULL),
(276, 2, '00:00:00', '00:00:00', 1, 8, 6, NULL, NULL),
(277, 3, '08:00:00', '16:00:00', 0, 8, 6, NULL, NULL),
(278, 4, '08:00:00', '16:00:00', 0, 8, 6, NULL, NULL),
(279, 5, '08:00:00', '16:00:00', 0, 8, 6, NULL, NULL),
(280, 6, '08:00:00', '16:00:00', 0, 8, 6, NULL, NULL),
(281, 7, '08:00:00', '16:00:00', 0, 8, 6, NULL, NULL),
(282, 1, '09:00:00', '17:00:00', 0, 9, 0, NULL, NULL),
(283, 2, '09:00:00', '17:00:00', 0, 9, 0, NULL, NULL),
(284, 3, '09:00:00', '17:00:00', 0, 9, 0, NULL, NULL),
(285, 4, '09:00:00', '17:00:00', 0, 9, 0, NULL, NULL),
(286, 5, '09:00:00', '17:00:00', 0, 9, 0, NULL, NULL),
(287, 6, '00:00:00', '00:00:00', 1, 9, 0, NULL, NULL),
(288, 7, '00:00:00', '00:00:00', 1, 9, 0, NULL, NULL),
(289, 1, '08:00:00', '16:00:00', 0, 9, 1, NULL, NULL),
(290, 2, '08:00:00', '16:00:00', 0, 9, 1, NULL, NULL),
(291, 3, '08:00:00', '16:00:00', 0, 9, 1, NULL, NULL),
(292, 4, '00:00:00', '00:00:00', 1, 9, 1, NULL, NULL),
(293, 5, '00:00:00', '00:00:00', 1, 9, 1, NULL, NULL),
(294, 6, '16:00:00', '00:00:00', 0, 9, 1, NULL, NULL),
(295, 7, '16:00:00', '00:00:00', 0, 9, 1, NULL, NULL),
(296, 1, '16:00:00', '00:00:00', 0, 9, 2, NULL, NULL),
(297, 2, '16:00:00', '00:00:00', 0, 9, 2, NULL, NULL),
(298, 3, '16:00:00', '00:00:00', 0, 9, 2, NULL, NULL),
(299, 4, '16:00:00', '00:00:00', 0, 9, 2, NULL, NULL),
(300, 5, '16:00:00', '00:00:00', 0, 9, 2, NULL, NULL),
(301, 6, '00:00:00', '00:00:00', 1, 9, 2, NULL, NULL),
(302, 7, '00:00:00', '00:00:00', 1, 9, 2, NULL, NULL),
(303, 1, '12:00:00', '20:00:00', 0, 9, 3, NULL, NULL),
(304, 2, '00:00:00', '00:00:00', 1, 9, 3, NULL, NULL),
(305, 3, '00:00:00', '00:00:00', 1, 9, 3, NULL, NULL),
(306, 4, '08:00:00', '16:00:00', 0, 9, 3, NULL, NULL),
(307, 5, '08:00:00', '16:00:00', 0, 9, 3, NULL, NULL),
(308, 6, '08:00:00', '16:00:00', 0, 9, 3, NULL, NULL),
(309, 7, '08:00:00', '16:00:00', 0, 9, 3, NULL, NULL),
(310, 1, '08:00:00', '16:00:00', 0, 9, 4, NULL, NULL),
(311, 2, '08:00:00', '16:00:00', 0, 9, 4, NULL, NULL),
(312, 3, '00:00:00', '00:00:00', 1, 9, 4, NULL, NULL),
(313, 4, '00:00:00', '00:00:00', 1, 9, 4, NULL, NULL),
(314, 5, '12:00:00', '20:00:00', 0, 9, 4, NULL, NULL),
(315, 6, '16:00:00', '00:00:00', 0, 9, 4, NULL, NULL),
(316, 7, '16:00:00', '00:00:00', 0, 9, 4, NULL, NULL),
(317, 1, '16:00:00', '00:00:00', 0, 9, 5, NULL, NULL),
(318, 2, '16:00:00', '00:00:00', 0, 9, 5, NULL, NULL),
(319, 3, '16:00:00', '00:00:00', 0, 9, 5, NULL, NULL),
(320, 4, '16:00:00', '00:00:00', 0, 9, 5, NULL, NULL),
(321, 5, '16:00:00', '00:00:00', 0, 9, 5, NULL, NULL),
(322, 6, '00:00:00', '00:00:00', 1, 9, 5, NULL, NULL),
(323, 7, '00:00:00', '00:00:00', 1, 9, 5, NULL, NULL),
(324, 1, '00:00:00', '00:00:00', 1, 9, 6, NULL, NULL),
(325, 2, '00:00:00', '00:00:00', 1, 9, 6, NULL, NULL),
(326, 3, '08:00:00', '16:00:00', 0, 9, 6, NULL, NULL),
(327, 4, '08:00:00', '16:00:00', 0, 9, 6, NULL, NULL),
(328, 5, '08:00:00', '16:00:00', 0, 9, 6, NULL, NULL),
(329, 6, '08:00:00', '16:00:00', 0, 9, 6, NULL, NULL),
(330, 7, '08:00:00', '16:00:00', 0, 9, 6, NULL, NULL),
(331, 1, '00:00:00', '00:00:00', 1, 9, 7, NULL, NULL),
(332, 2, '12:00:00', '20:00:00', 0, 9, 7, NULL, NULL),
(333, 3, '12:00:00', '20:00:00', 0, 9, 7, NULL, NULL),
(334, 4, '12:00:00', '20:00:00', 0, 9, 7, NULL, NULL),
(335, 5, '00:00:00', '00:00:00', 1, 9, 7, NULL, NULL),
(336, 6, '12:00:00', '20:00:00', 0, 9, 7, NULL, NULL),
(337, 7, '12:00:00', '20:00:00', 0, 9, 7, NULL, NULL),
(338, 1, '08:00:00', '16:30:00', 0, 10, 0, NULL, NULL),
(339, 2, '08:00:00', '16:30:00', 0, 10, 0, NULL, NULL),
(340, 3, '08:00:00', '16:30:00', 0, 10, 0, NULL, NULL),
(341, 4, '08:00:00', '16:30:00', 0, 10, 0, NULL, NULL),
(342, 5, '08:00:00', '16:30:00', 0, 10, 0, NULL, NULL),
(343, 6, '00:00:00', '00:00:00', 1, 10, 0, NULL, NULL),
(344, 7, '00:00:00', '00:00:00', 1, 10, 0, NULL, NULL),
(345, 1, '08:00:00', '17:00:00', 0, 10, 1, NULL, NULL),
(346, 2, '08:00:00', '17:00:00', 0, 10, 1, NULL, NULL),
(347, 3, '08:00:00', '17:00:00', 0, 10, 1, NULL, NULL),
(348, 4, '08:00:00', '17:00:00', 0, 10, 1, NULL, NULL),
(349, 5, '08:00:00', '14:00:00', 0, 10, 1, NULL, NULL),
(350, 6, '00:00:00', '00:00:00', 1, 10, 1, NULL, NULL),
(351, 7, '00:00:00', '00:00:00', 1, 10, 1, NULL, NULL),
(352, 1, '08:00:00', '16:30:00', 0, 11, 0, NULL, NULL),
(353, 2, '08:00:00', '16:30:00', 0, 11, 0, NULL, NULL),
(354, 3, '08:00:00', '16:30:00', 0, 11, 0, NULL, NULL),
(355, 4, '08:00:00', '16:30:00', 0, 11, 0, NULL, NULL),
(356, 5, '08:00:00', '16:30:00', 0, 11, 0, NULL, NULL),
(357, 6, '00:00:00', '00:00:00', 1, 11, 0, NULL, NULL),
(358, 7, '00:00:00', '00:00:00', 1, 11, 0, NULL, NULL),
(359, 1, '08:00:00', '17:00:00', 0, 11, 1, NULL, NULL),
(360, 2, '08:00:00', '17:00:00', 0, 11, 1, NULL, NULL),
(361, 3, '08:00:00', '17:00:00', 0, 11, 1, NULL, NULL),
(362, 4, '08:00:00', '17:00:00', 0, 11, 1, NULL, NULL),
(363, 5, '08:00:00', '14:00:00', 0, 11, 1, NULL, NULL),
(364, 6, '00:00:00', '00:00:00', 1, 11, 1, NULL, NULL),
(365, 7, '00:00:00', '00:00:00', 1, 11, 1, NULL, NULL),
(366, 1, '08:00:00', '17:00:00', 0, 11, 2, NULL, NULL),
(367, 2, '08:00:00', '17:00:00', 0, 11, 2, NULL, NULL),
(368, 3, '08:00:00', '17:00:00', 0, 11, 2, NULL, NULL),
(369, 4, '08:00:00', '17:00:00', 0, 11, 2, NULL, NULL),
(370, 5, '08:00:00', '14:00:00', 0, 11, 2, NULL, NULL),
(371, 6, '00:00:00', '00:00:00', 1, 11, 2, NULL, NULL),
(372, 7, '00:00:00', '00:00:00', 1, 11, 2, NULL, NULL),
(373, 1, '08:00:00', '16:30:00', 0, 12, 0, NULL, NULL),
(374, 2, '08:00:00', '16:30:00', 0, 12, 0, NULL, NULL),
(375, 3, '08:00:00', '16:30:00', 0, 12, 0, NULL, NULL),
(376, 4, '08:00:00', '16:30:00', 0, 12, 0, NULL, NULL),
(377, 5, '08:00:00', '16:30:00', 0, 12, 0, NULL, NULL),
(378, 6, '00:00:00', '00:00:00', 1, 12, 0, NULL, NULL),
(379, 7, '00:00:00', '00:00:00', 1, 12, 0, NULL, NULL),
(380, 1, '08:00:00', '17:00:00', 0, 12, 1, NULL, NULL),
(381, 2, '08:00:00', '17:00:00', 0, 12, 1, NULL, NULL),
(382, 3, '08:00:00', '17:00:00', 0, 12, 1, NULL, NULL),
(383, 4, '08:00:00', '17:00:00', 0, 12, 1, NULL, NULL),
(384, 5, '08:00:00', '14:00:00', 0, 12, 1, NULL, NULL),
(385, 6, '00:00:00', '00:00:00', 1, 12, 1, NULL, NULL),
(386, 7, '00:00:00', '00:00:00', 1, 12, 1, NULL, NULL),
(387, 1, '08:00:00', '17:00:00', 0, 12, 2, NULL, NULL),
(388, 2, '08:00:00', '17:00:00', 0, 12, 2, NULL, NULL),
(389, 3, '08:00:00', '17:00:00', 0, 12, 2, NULL, NULL),
(390, 4, '08:00:00', '17:00:00', 0, 12, 2, NULL, NULL),
(391, 5, '08:00:00', '14:00:00', 0, 12, 2, NULL, NULL),
(392, 6, '00:00:00', '00:00:00', 1, 12, 2, NULL, NULL),
(393, 7, '00:00:00', '00:00:00', 1, 12, 2, NULL, NULL),
(394, 1, '08:00:00', '17:00:00', 0, 12, 3, NULL, NULL),
(395, 2, '08:00:00', '17:00:00', 0, 12, 3, NULL, NULL),
(396, 3, '08:00:00', '17:00:00', 0, 12, 3, NULL, NULL),
(397, 4, '08:00:00', '17:00:00', 0, 12, 3, NULL, NULL),
(398, 5, '08:00:00', '14:00:00', 0, 12, 3, NULL, NULL),
(399, 6, '00:00:00', '00:00:00', 1, 12, 3, NULL, NULL),
(400, 7, '00:00:00', '00:00:00', 1, 12, 3, NULL, NULL),
(401, 1, '08:00:00', '16:30:00', 0, 13, 0, NULL, NULL),
(402, 2, '08:00:00', '16:30:00', 0, 13, 0, NULL, NULL),
(403, 3, '08:00:00', '16:30:00', 0, 13, 0, NULL, NULL),
(404, 4, '08:00:00', '16:30:00', 0, 13, 0, NULL, NULL),
(405, 5, '08:00:00', '16:30:00', 0, 13, 0, NULL, NULL),
(406, 6, '00:00:00', '00:00:00', 1, 13, 0, NULL, NULL),
(407, 7, '00:00:00', '00:00:00', 1, 13, 0, NULL, NULL),
(408, 1, '08:00:00', '17:00:00', 0, 13, 1, NULL, NULL),
(409, 2, '08:00:00', '17:00:00', 0, 13, 1, NULL, NULL),
(410, 3, '08:00:00', '17:00:00', 0, 13, 1, NULL, NULL),
(411, 4, '08:00:00', '17:00:00', 0, 13, 1, NULL, NULL),
(412, 5, '08:00:00', '14:00:00', 0, 13, 1, NULL, NULL),
(413, 6, '00:00:00', '00:00:00', 1, 13, 1, NULL, NULL),
(414, 7, '00:00:00', '00:00:00', 1, 13, 1, NULL, NULL),
(415, 1, '08:00:00', '17:00:00', 0, 13, 2, NULL, NULL),
(416, 2, '08:00:00', '17:00:00', 0, 13, 2, NULL, NULL),
(417, 3, '08:00:00', '17:00:00', 0, 13, 2, NULL, NULL),
(418, 4, '08:00:00', '17:00:00', 0, 13, 2, NULL, NULL),
(419, 5, '08:00:00', '14:00:00', 0, 13, 2, NULL, NULL),
(420, 6, '00:00:00', '00:00:00', 1, 13, 2, NULL, NULL),
(421, 7, '00:00:00', '00:00:00', 1, 13, 2, NULL, NULL),
(422, 1, '08:00:00', '17:00:00', 0, 13, 3, NULL, NULL),
(423, 2, '08:00:00', '17:00:00', 0, 13, 3, NULL, NULL),
(424, 3, '08:00:00', '17:00:00', 0, 13, 3, NULL, NULL),
(425, 4, '08:00:00', '17:00:00', 0, 13, 3, NULL, NULL),
(426, 5, '08:00:00', '14:00:00', 0, 13, 3, NULL, NULL),
(427, 6, '00:00:00', '00:00:00', 1, 13, 3, NULL, NULL),
(428, 7, '00:00:00', '00:00:00', 1, 13, 3, NULL, NULL),
(429, 1, '08:00:00', '17:00:00', 0, 13, 4, NULL, NULL),
(430, 2, '08:00:00', '17:00:00', 0, 13, 4, NULL, NULL),
(431, 3, '08:00:00', '17:00:00', 0, 13, 4, NULL, NULL),
(432, 4, '08:00:00', '17:00:00', 0, 13, 4, NULL, NULL),
(433, 5, '08:00:00', '14:00:00', 0, 13, 4, NULL, NULL),
(434, 6, '00:00:00', '00:00:00', 1, 13, 4, NULL, NULL),
(435, 7, '00:00:00', '00:00:00', 1, 13, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `test`
--

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `test`
--

INSERT INTO `test` (`id`, `text`) VALUES
(1, '[value-1]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `department` int(11) DEFAULT NULL,
  `holiday_zone` int(11) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `department`, `holiday_zone`, `remember_token`, `created_at`, `updated_at`, `is_admin`) VALUES
(2, 'Bozhidar Dimitrov', 'bozhidaremilovdimitrov@paucasesnovescifp.cat', '2024-10-15 11:24:24', '$2y$12$42kQ1YvM8K0/3/iRmGQ9m.MMxW6BpSJnXb3dxhB3mt9rm.uMpomNa', 1, 3, '6ldW30rEBm2gEpNyDq4YGg8XPs4l4xBZ8yAW2O3fdmO8V8nfrmSQCBNIzrDp', '2024-10-15 11:23:49', '2024-10-15 11:24:24', 1),
(3, 'Dary', 'dari1992bg@gmail.com', '2024-10-18 05:31:15', '$2y$12$dR3Xm9gu0DcYpx0HfyIa9e.N.jVdFx2mCOvmXD5WzhN0qZYAo5P1q', 1, 1, NULL, '2024-10-18 05:30:17', '2024-10-18 05:31:15', 0),
(5, 'Bozhidar Emilov Dimitrov', 'bozhidar.dimitrov@homerti.com', '2024-10-21 12:24:18', '$2y$12$DLDwz8mNx2oLQWzK1O83WuJgVcWoYcvnuP1SlfdyFCpVOv6JxgXyO', 1, 1, '6NtjcAhLAwD4LcYhgRRz0Cvh9waBntMGJamsoQFx9o7qS6Cn96LQT15ZrD6S', '2024-10-21 12:24:08', '2024-10-21 12:24:18', 0),
(8, 'Damiana', 'damiana@homerti.com', '2024-11-12 13:38:48', 'damiana', 1, 3, NULL, NULL, NULL, 0),
(9, 'VICKY', 'vicky@test-homerti.com', '2024-11-12 13:42:11', '$2y$12$im/PdHbJOppRO8n/KHNi7Or3uUhWFulUTOrGP46QBGHWkmEhy31iS', 2, 3, NULL, '2024-11-12 12:41:42', '2024-11-12 12:41:42', 0),
(10, 'JOANA', 'joana@test-homerti.com', '2024-11-12 13:58:25', '$2y$12$2vJgpGkTLNqwkkt/gp8NL.bF0.H05DMA79Dov1YQ5LUMCipa8Co2K', 2, 3, NULL, '2024-11-12 12:58:04', '2024-11-12 12:58:04', 0),
(11, 'LEO', 'leo@test-homerti.com', '2024-11-12 14:00:58', '$2y$12$Dj8F.DiKpDjESnLKMhYYqukWqiBmuJVzhwNIIOThQf/UMGtzcH/uW', 2, 3, NULL, '2024-11-12 13:00:42', '2024-11-12 13:00:42', 0),
(12, 'ALBA', 'alba@test-homerti.com', '2024-11-12 14:03:04', '$2y$12$bJzE7DQYO5D8qmOsm05iq.TWQ26W8/mqXP1GmdY5.OBthdb5mA2.q', 2, 3, NULL, '2024-11-12 13:02:52', '2024-11-12 13:02:52', 0),
(13, 'XAVI', 'xavi@test-homerti.com', '2024-11-12 14:05:23', '$2y$12$EEIpWux3spQ9Tq1aKjfCBO04Gmj4EAl6MxFtw2bsbzUqYgKdOKxZO', 2, 3, NULL, '2024-11-12 13:05:11', '2024-11-12 13:05:11', 0),
(14, 'MAICA', 'maica@test-homerti.com', '2024-11-12 14:07:17', '$2y$12$DytK6wEeZRzE4EVQNCgbkOUOcLeWNy1GvksBYVSX7GCNln6KnrDUq', 2, 3, NULL, '2024-11-12 13:07:05', '2024-11-12 13:07:05', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_availability`
--

CREATE TABLE `user_availability` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `users_available` tinyint(3) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_availability`
--

INSERT INTO `user_availability` (`id`, `users_available`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 3, 8, NULL, NULL),
(2, 4, 8, NULL, NULL),
(3, 4, 6, NULL, NULL),
(4, 5, 6, NULL, NULL),
(5, 6, 6, NULL, NULL),
(6, 5, 8, NULL, NULL),
(7, 6, 8, NULL, NULL),
(8, 7, 8, NULL, NULL),
(9, 8, 8, NULL, NULL),
(10, 2, 1, NULL, NULL),
(11, 3, 1, NULL, NULL),
(12, 4, 1, NULL, NULL),
(13, 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `weekly_schedules`
--

CREATE TABLE `weekly_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `schedule_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schedule_data`)),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rotation` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Stores rotation values from 0 to 50'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contract_types`
--
ALTER TABLE `contract_types`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `extra_days`
--
ALTER TABLE `extra_days`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `extra_day_types`
--
ALTER TABLE `extra_day_types`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `holidays_paid`
--
ALTER TABLE `holidays_paid`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `holiday_status_types`
--
ALTER TABLE `holiday_status_types`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `holiday_zones`
--
ALTER TABLE `holiday_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `job_ranges`
--
ALTER TABLE `job_ranges`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `night_shifts`
--
ALTER TABLE `night_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `night_shifts_user_id_foreign` (`user_id`),
  ADD KEY `night_shifts_department_id_foreign` (`department_id`);

--
-- Indices de la tabla `non_working_day_zones`
--
ALTER TABLE `non_working_day_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `user_availability`
--
ALTER TABLE `user_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_availability_department_id_foreign` (`department_id`);

--
-- Indices de la tabla `weekly_schedules`
--
ALTER TABLE `weekly_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weekly_schedules_department_id_week_number_unique` (`department_id`,`year`,`week_number`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contract_types`
--
ALTER TABLE `contract_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `extra_days`
--
ALTER TABLE `extra_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extra_day_types`
--
ALTER TABLE `extra_day_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de la tabla `holidays_paid`
--
ALTER TABLE `holidays_paid`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `holiday_status_types`
--
ALTER TABLE `holiday_status_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `job_ranges`
--
ALTER TABLE `job_ranges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `night_shifts`
--
ALTER TABLE `night_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=436;

--
-- AUTO_INCREMENT de la tabla `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `user_availability`
--
ALTER TABLE `user_availability`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `weekly_schedules`
--
ALTER TABLE `weekly_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `night_shifts`
--
ALTER TABLE `night_shifts`
  ADD CONSTRAINT `night_shifts_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `night_shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_availability`
--
ALTER TABLE `user_availability`
  ADD CONSTRAINT `user_availability_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
