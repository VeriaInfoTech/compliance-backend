CREATE TABLE IF NOT EXISTS `form_inventory`
(
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(256)        NOT NULL DEFAULT '',
    `slug`        VARCHAR(255)        NOT NULL DEFAULT '',
    `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
);

CREATE TABLE IF NOT EXISTS `form_element`
(
    `id`            INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(255)        NOT NULL DEFAULT '',
    `title`         VARCHAR(256)        NOT NULL DEFAULT '',
    `description`   TEXT,
    `value`         TEXT,
    `type`          VARCHAR(32)         NOT NULL DEFAULT '',
    `required`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `display_order` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `time_create`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
);

CREATE TABLE IF NOT EXISTS `form_link`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `form_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `element_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`     TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `form_record`
(
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `form_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `form_data`
(
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `value`       TEXT,
    `description` TEXT,
    `record_id`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `form_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `element_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
);