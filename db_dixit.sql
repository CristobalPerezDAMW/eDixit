-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-04-2020 a las 09:59:38
-- Versión del servidor: 10.1.32-MariaDB
-- Versión de PHP: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cristobal`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `Id` int(11) NOT NULL,
  `Cuentacuentos` varchar(200) DEFAULT NULL,
  `CartasPila` text NOT NULL,
  `Estado` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `partidas`
--

INSERT INTO `partidas` (`Id`, `Cuentacuentos`, `CartasPila`, `Estado`) VALUES
(115, 'cristichi@hotmail.es', '19:20:21:22:23:24:25:26:27', 'Inicio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partida_jugador`
--

CREATE TABLE `partida_jugador` (
  `Partida` int(11) NOT NULL,
  `Jugador` varchar(200) NOT NULL,
  `Posicion` int(11) NOT NULL,
  `Mano` tinytext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `partida_jugador`
--

INSERT INTO `partida_jugador` (`Partida`, `Jugador`, `Posicion`, `Mano`) VALUES
(115, 'admin@admin.ga', 0, '1:2:3:4:5:6'),
(115, 'cristichi@hotmail.es', 0, '7:8:9:10:11:12'),
(115, 'cristichikillerpsn@gmail.com', 0, '13:14:15:16:17:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `Correo` varchar(200) NOT NULL,
  `Nombre` text NOT NULL,
  `Contra` text NOT NULL,
  `Admin` tinyint(1) NOT NULL DEFAULT '0',
  `Verificacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`Correo`, `Nombre`, `Contra`, `Admin`, `Verificacion`) VALUES
('admin@admin.ga', 'Administrador', '21232f297a57a5a743894a0e4a801fc3', 1, NULL),
('cristichi@hotmail.es', 'Cristichi', '6e7bc035c10d6d628e9067ae9b034d41', 0, NULL),
('cristichikillerpsn@gmail.com', 'Cristichi', '6e7bc035c10d6d628e9067ae9b034d41', 0, NULL),
('cristobalperez.dam@gmail.com', 'Cristichi DAM', '3488e28acfe4abe097e1f4d501d4b49a', 0, NULL),
('fdgongora@iesmurgi.org', 'Pepe', '6588291fabc526ef29eef7f5e73a66f6', 0, NULL),
('focusyi@hotmail.com', 'Danikileitor', '25f9e794323b453885f5181f1b624d0b', 0, NULL),
('ramperrub@gmail.com', 'Ramon', '52e95dde8c35e734e92e3cedbfe75b27', 0, 'ABg0Tg5pZg3OuTbFPgprq7EeNAIskO'),
('very_18_8@hotmail.es', 'Verónica ', '36208229a1f277c3c27b69db861be759', 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `partida_cuentacuentos` (`Cuentacuentos`);

--
-- Indices de la tabla `partida_jugador`
--
ALTER TABLE `partida_jugador`
  ADD PRIMARY KEY (`Partida`,`Jugador`),
  ADD KEY `Jugador` (`Jugador`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`Correo`(40)),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD CONSTRAINT `partida_cuentacuentos` FOREIGN KEY (`Cuentacuentos`) REFERENCES `usuarios` (`Correo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `partida_jugador`
--
ALTER TABLE `partida_jugador`
  ADD CONSTRAINT `partida_jugador_ibfk_1` FOREIGN KEY (`Partida`) REFERENCES `partidas` (`Id`),
  ADD CONSTRAINT `partida_jugador_ibfk_2` FOREIGN KEY (`Jugador`) REFERENCES `usuarios` (`Correo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
