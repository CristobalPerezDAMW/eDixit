-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 03-06-2020 a las 10:52:01
-- Versión del servidor: 10.1.44-MariaDB-0ubuntu0.18.04.1
-- Versión de PHP: 7.2.24-0ubuntu0.18.04.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: ``
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `Id` int(11) NOT NULL,
  `Cuentacuentos` varchar(100) DEFAULT NULL,
  `Pista` text,
  `CartasPila` text NOT NULL,
  `CartasDescartadas` text NOT NULL,
  `Estado` varchar(20) NOT NULL DEFAULT 'Inicio',
  `UltActivo` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partida_jugador`
--

CREATE TABLE `partida_jugador` (
  `Partida` int(11) NOT NULL,
  `Jugador` varchar(100) NOT NULL,
  `Posicion` int(11) NOT NULL DEFAULT '0',
  `Mano` tinytext,
  `CartaElegida` tinyint(3) UNSIGNED DEFAULT NULL,
  `CartaVotada` int(11) DEFAULT NULL,
  `PuntuacionRonda` int(11) DEFAULT NULL,
  `FinalVisto` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `Correo` varchar(100) NOT NULL,
  `Nombre` text NOT NULL,
  `Contra` text NOT NULL,
  `Admin` tinyint(1) NOT NULL DEFAULT '0',
  `Verificacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuarios`
--

-- INSERT INTO `usuarios` (`Correo`, `Nombre`, `Contra`, `Admin`, `Verificacion`) VALUES
-- ('admin', 'Administrador', '21232f297a57a5a743894a0e4a801fc3', 1, NULL),
-- ('admin@admin.ga', 'Administrador', '21232f297a57a5a743894a0e4a801fc3', 1, NULL),
-- ('cristichi@hotmail.es', 'Cristichi', '6e7bc035c10d6d628e9067ae9b034d41', 0, NULL),
-- ('cristichiedixit@gmail.com', 'eDixit Oficial', '6e7bc035c10d6d628e9067ae9b034d41', 1, NULL),
-- ('cristichikillerpsn@gmail.com', 'Cristichi', '6e7bc035c10d6d628e9067ae9b034d41', 0, NULL),
-- ('cristobalperez.dam@gmail.com', 'Cristichi', '6e7bc035c10d6d628e9067ae9b034d41', 0, NULL),
-- ('fdgongora@iesmurgi.org', 'Pepe', '6588291fabc526ef29eef7f5e73a66f6', 0, NULL),
-- ('focusyi@hotmail.com', 'Danikileitor', '25f9e794323b453885f5181f1b624d0b', 0, NULL),
-- ('ramperrub@gmail.com', 'Ramon', '52e95dde8c35e734e92e3cedbfe75b27', 0, 'ABg0Tg5pZg3OuTbFPgprq7EeNAIskO'),
-- ('usuario', 'usuario', 'usuario', 0, NULL),
-- ('very_18_8@hotmail.es', 'Verónica ', '36208229a1f277c3c27b69db861be759', 0, NULL);

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
  ADD KEY `Correo` (`Correo`) USING BTREE;

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
  ADD CONSTRAINT `partida_cuentacuentos` FOREIGN KEY (`Cuentacuentos`) REFERENCES `usuarios` (`Correo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `partida_sala` FOREIGN KEY (`Id`) REFERENCES `salas` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `partida_jugador`
--
ALTER TABLE `partida_jugador`
  ADD CONSTRAINT `partida_jugador_ibfk_1` FOREIGN KEY (`Partida`) REFERENCES `partidas` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `partida_jugador_ibfk_2` FOREIGN KEY (`Jugador`) REFERENCES `usuarios` (`Correo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Eventos
--
DELIMITER $$
CREATE DEFINER=`cristobal`@`%` EVENT `evento_fin_partida` ON SCHEDULE EVERY 10 SECOND STARTS '2020-04-04 00:00:00' ON COMPLETION NOT PRESERVE ENABLE COMMENT 'Cada 10 segundos, comprueba si alguna partida tiene que borrarse' DO BEGIN
  DELETE `salas`, `partidas` FROM `salas` INNER JOIN `partidas` ON `partidas`.`Id`=`salas`.`Id` WHERE `Estado`='Final' AND `UltActivo` <  NOW() + INTERVAL -5 SECOND;
END$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
