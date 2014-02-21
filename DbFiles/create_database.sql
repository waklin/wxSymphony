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
  `city_id` INTEGER(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `user_fk` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `category` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `catname` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=22 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `company` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `company` VARCHAR(100) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `route` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `linename` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `linetime` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `lastupdate` DATE NOT NULL,
  `category` INTEGER(11) NOT NULL,
  `company` INTEGER(11) NOT NULL,
  `type` INTEGER(11) NOT NULL,
  `note` VARCHAR(1024) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `fare` VARCHAR(200) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `number` INTEGER(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `company` (`company`),
  CONSTRAINT `route_fk1` FOREIGN KEY (`company`) REFERENCES `company` (`id`),
  CONSTRAINT `route_fk` FOREIGN KEY (`category`) REFERENCES `category` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `coordinate` (
  `id` INTEGER(11) NOT NULL,
  `longitude` FLOAT(9,5) NOT NULL,
  `latitude` FLOAT(9,5) NOT NULL,
  `type` INTEGER(11) NOT NULL
)ENGINE=InnoDB
CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `station` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `station` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `astation` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `astation` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `estation` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `estation` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `stations` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `pm1` INTEGER(11) NOT NULL,
  `pm2` INTEGER(11) NOT NULL,
  `pm3` INTEGER(11) NOT NULL,
  `routeid` INTEGER(11) NOT NULL,
  `station` INTEGER(11) NOT NULL,
  `astation` INTEGER(11) NOT NULL,
  `estation` INTEGER(11) NOT NULL,
  `other` VARCHAR(255) COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `routeid` (`routeid`),
  KEY `station` (`station`),
  CONSTRAINT `stations_fk` FOREIGN KEY (`routeid`) REFERENCES `route` (`id`),
  CONSTRAINT `stations_fk1` FOREIGN KEY (`station`) REFERENCES `station` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

insert company values(0, '未知');

insert into city(id, name) VALUES(0, '未知');
insert into city(name) VALUES('北京');
insert into city(name) VALUES('上海');
insert into city(name) VALUES('深圳');
