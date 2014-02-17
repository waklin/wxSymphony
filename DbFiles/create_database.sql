CREATE TABLE `city` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `name` CHAR(20) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=0 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

CREATE TABLE `user` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `wxAppId` CHAR(50) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `name` CHAR(50) COLLATE utf8_general_ci DEFAULT '',
  `city_id` INTEGER(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `user_fk` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=0 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

insert into city(name) VALUES('未知');
insert into city(name) VALUES('北京');
insert into city(name) VALUES('上海');
insert into city(name) VALUES('广州');
