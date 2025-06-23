-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-06-2025 a las 06:17:55
-- Versión del servidor: 10.11.13-MariaDB
-- Versión de PHP: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cevicherias_amoung_sos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id_alerta` int(11) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp(),
  `hora` time NOT NULL DEFAULT current_timestamp(),
  `tipo_alerta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `alertas`
--

INSERT INTO `alertas` (`id_alerta`, `id_dispositivo`, `fecha`, `hora`, `tipo_alerta`) VALUES
(1, 1, '2025-06-19', '14:35:00', 'Disparo'),
(6, 1, '2025-06-14', '13:29:00', 'Disparo'),
(7, 1, '2025-06-14', '12:21:00', 'Disparo'),
(8, 1, '2025-06-08', '12:20:00', 'Disparo'),
(9, 2, '2025-06-07', '12:30:00', 'Disparo'),
(12, 1, '2025-06-19', '09:11:00', 'Disparo'),
(13, 2, '2025-05-18', '07:12:00', 'Disparo'),
(14, 1, '2025-06-20', '03:38:04', 'disparo'),
(21, 1, '2025-06-20', '03:46:21', 'disparo'),
(22, 1, '2025-06-20', '03:51:59', 'disparo'),
(23, 1, '2025-06-20', '03:53:22', 'disparo'),
(24, 1, '2025-06-20', '03:54:25', 'disparo'),
(25, 1, '2025-06-20', '03:56:04', 'disparo'),
(26, 1, '2025-06-20', '04:04:39', 'disparo'),
(27, 1, '2025-06-20', '04:05:38', 'disparo'),
(28, 1, '2025-06-20', '04:06:52', 'disparo'),
(29, 1, '2025-06-20', '04:14:30', 'disparo'),
(30, 1, '2025-06-20', '04:18:18', 'disparo'),
(31, 1, '2025-06-20', '04:22:54', 'disparo'),
(32, 1, '2025-06-20', '04:25:43', 'disparo'),
(33, 1, '2025-06-20', '04:26:47', 'disparo'),
(34, 1, '2025-06-20', '04:36:53', 'disparo'),
(35, 1, '2025-06-20', '04:39:43', 'disparo'),
(36, 1, '2025-06-20', '04:41:25', 'disparo'),
(37, 1, '2025-06-20', '04:42:35', 'disparo'),
(38, 1, '2025-06-20', '05:01:32', 'disparo'),
(39, 1, '2025-06-20', '05:03:57', 'disparo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `camaras`
--

CREATE TABLE `camaras` (
  `id_camara` int(11) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `ubicacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `camaras`
--

INSERT INTO `camaras` (`id_camara`, `codigo`, `ubicacion`) VALUES
(1, 'BAB', 'jr. circumbalacion tomate'),
(2, 'BB2', 'jr. lima cumbaza'),
(3, 'BA4', 'Jr. Progreso Morales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivosCamaras`
--

CREATE TABLE `dispositivosCamaras` (
  `id_dispositivo` int(11) NOT NULL,
  `id_camara` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `dispositivosCamaras`
--

INSERT INTO `dispositivosCamaras` (`id_dispositivo`, `id_camara`) VALUES
(1, 1),
(1, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos_iot`
--

CREATE TABLE `dispositivos_iot` (
  `id_dispositivo` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ubicacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `dispositivos_iot`
--

INSERT INTO `dispositivos_iot` (`id_dispositivo`, `nombre`, `ubicacion`) VALUES
(1, 'Grabador Uno', 'jr. Cumbaza 745'),
(2, 'Grabador 2', 'Jr. Los Proceres');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `username`, `password`, `nombre`, `estado`) VALUES
(1, 'Ricardiño', '$2b$12$fJASB2YY1HKzDdybT0KaauGaBxOUbHUmIWxBnuTQypm3wCxhQB6X.', 'Richard Adan', 1),
(2, 'Bebitaland', '$2b$12$21p1H5vLxB9fwUICJ8rw6OupISbBOM8eDUkxwhjyLpsS8BQVtdqfi', 'Bebita', 1),
(3, 'kent', '$2b$12$YZYXw5SxY5.xe6qzk1T8uu2x6vrw2aOLAmbylIuStd/XpNa8yrzbe', 'Kent', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `camaras`
--
ALTER TABLE `camaras`
  ADD PRIMARY KEY (`id_camara`);

--
-- Indices de la tabla `dispositivosCamaras`
--
ALTER TABLE `dispositivosCamaras`
  ADD PRIMARY KEY (`id_dispositivo`,`id_camara`),
  ADD KEY `id_camara` (`id_camara`);

--
-- Indices de la tabla `dispositivos_iot`
--
ALTER TABLE `dispositivos_iot`
  ADD PRIMARY KEY (`id_dispositivo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `camaras`
--
ALTER TABLE `camaras`
  MODIFY `id_camara` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `dispositivos_iot`
--
ALTER TABLE `dispositivos_iot`
  MODIFY `id_dispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos_iot` (`id_dispositivo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `dispositivosCamaras`
--
ALTER TABLE `dispositivosCamaras`
  ADD CONSTRAINT `dispositivosCamaras_ibfk_1` FOREIGN KEY (`id_camara`) REFERENCES `camaras` (`id_camara`) ON UPDATE CASCADE,
  ADD CONSTRAINT `dispositivosCamaras_ibfk_2` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos_iot` (`id_dispositivo`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
