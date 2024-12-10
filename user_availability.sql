-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2024 a las 11:55:44
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `user_availability`
--
ALTER TABLE `user_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_availability_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `user_availability`
--
ALTER TABLE `user_availability`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `user_availability`
--
ALTER TABLE `user_availability`
  ADD CONSTRAINT `user_availability_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
