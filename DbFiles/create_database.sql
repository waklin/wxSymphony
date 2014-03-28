SET sql_mode='NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `city` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `name` CHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `user` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `wxAppId` CHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `name` CHAR(255) COLLATE utf8_general_ci DEFAULT '',
  `city` INTEGER(11) NOT NULL DEFAULT '1',
  `joinTime` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `city` (`city`),
  CONSTRAINT `user_fk` FOREIGN KEY (`city`) REFERENCES `city` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `route` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `linename` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `linetime` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `lastupdate` DATE NOT NULL,
  `type` INTEGER(11) NOT NULL,
  `note` VARCHAR(1024) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `fare` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `number` INTEGER(11) NOT NULL,
  `city` INTEGER(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `city` (`city`),
  CONSTRAINT `route_fk` FOREIGN KEY (`city`) REFERENCES `city` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `coordinate` (
  `id` INTEGER(11) NOT NULL,
  `longitude` FLOAT(9,5) NOT NULL,
  `latitude` FLOAT(9,5) NOT NULL,
  `type` INTEGER(11) NOT NULL,
  `remark` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL
)ENGINE=InnoDB
CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `station` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `station` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `city` INTEGER(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `city` (`city`),
  CONSTRAINT `station_fk` FOREIGN KEY (`city`) REFERENCES `city` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `stations` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `pm1` INTEGER(11) NOT NULL,
  `pm2` INTEGER(11) NOT NULL,
  `pm3` INTEGER(11) NOT NULL,
  `lineid` INTEGER(11) NOT NULL,
  `station` INTEGER(11) NOT NULL,
  `other` VARCHAR(255) COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `lineid` (`lineid`),
  KEY `station` (`station`),
  CONSTRAINT `stations_fk` FOREIGN KEY (`lineid`) REFERENCES `route` (`id`),
  CONSTRAINT `stations_fk1` FOREIGN KEY (`station`) REFERENCES `station` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `attention` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `user` INTEGER(11) NOT NULL,
  `route` INTEGER(11) NOT NULL,
  `pm_morning` TINYINT(4) NOT NULL DEFAULT '1',
  `route_opp` INTEGER(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `track` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `city` INTEGER(11) NOT NULL,
  `user` INTEGER(11) NOT NULL,
  `route` INTEGER(11) NOT NULL,
  `pm` TINYINT(4) NOT NULL,
  `station` INTEGER(11) DEFAULT NULL,
  `lastUpdateTime` DATETIME DEFAULT NULL,
  `generateTime` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `city` (`city`),
  KEY `user` (`user`),
  KEY `route` (`route`),
  CONSTRAINT `track_fk1` FOREIGN KEY (`city`) REFERENCES `city` (`id`),
  CONSTRAINT `track_fk2` FOREIGN KEY (`user`) REFERENCES `user` (`id`),
  CONSTRAINT `track_fk3` FOREIGN KEY (`route`) REFERENCES `route` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

insert into city(id, name) VALUES(0, '未知');
insert into city(name) VALUES('北京');
insert into city(name) VALUES('上海');
insert into city(name) VALUES('深圳');

/*insert into attention(user, route) values(1, 299);*/
