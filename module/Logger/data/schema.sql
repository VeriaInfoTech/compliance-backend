CREATE TABLE IF NOT EXISTS `logger_system`
(
	`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`path`        VARCHAR(255)     NOT NULL DEFAULT '',
	`message`     TEXT             NOT NULL,
	`priority`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`level`       VARCHAR(255)     NOT NULL DEFAULT '',
	`user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`company_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`timestamp`   VARCHAR(255)     NOT NULL DEFAULT '',
	`time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`information` JSON,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `logger_user`
(
	`id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`operator_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`state`       VARCHAR(32)      NOT NULL DEFAULT '',
	`information` JSON,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `logger_history`
(
	`id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id`          INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`company_id`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`relation_module`  VARCHAR(32)      NOT NULL DEFAULT '',
	`relation_section` VARCHAR(32)      NOT NULL DEFAULT '',
	`relation_item`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`time_create`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`state`            VARCHAR(32)      NOT NULL DEFAULT '',
	`information`      JSON,
	PRIMARY KEY (`id`)
);