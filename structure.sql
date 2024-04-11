-- this table is mandatory to create. It manages the scheduling of messages
-- to dispatch
CREATE TABLE `prog_msg` (
      `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `fecha_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `fecha_inicio` timestamp NULL DEFAULT NULL,
      `fecha_fin` timestamp NULL DEFAULT NULL,
      `activo` tinyint(1) NOT NULL DEFAULT '1',
      `servicio_id` int DEFAULT NULL,
      `periodo` int NOT NULL,
      `dia_semana` tinyint DEFAULT NULL,
      `hora` time NOT NULL,
      `texto` varchar(500) DEFAULT NULL,
      `msg_id` int DEFAULT NULL,
      `cat_msg_id` int DEFAULT NULL,
      `ult_msg_id` int DEFAULT NULL,
      `ult_fecha_entrega` timestamp NULL DEFAULT NULL,
      `result` varchar(200) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT ;

/*
-- optionally table mensajes general
CREATE TABLE `mensajes_general` (
        `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `activo` tinyint(1) NOT NULL DEFAULT '1',
        `categoria_mensaje_id` int NOT NULL,
        `texto` varchar(400) NOT NULL,
        `servicio_id` int NOT NULL,
        `url` varchar(200) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT     
  */