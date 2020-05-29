DELIMITER €
CREATE EVENT evento_fin_partida
    ON SCHEDULE
      EVERY 10 SECOND
    COMMENT 'Cada 10 segundos, comprueba si alguna partida tiene que borrarse y la borra'
    DO
      BEGIN
        DELETE `salas`, `partidas` FROM `salas` INNER JOIN `partidas` ON `partidas`.`Id`=`salas`.`Id` WHERE `Estado`='Final' AND `UltActivo` <  NOW() + INTERVAL -5 SECOND;
      END€

DELIMITER ;