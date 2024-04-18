CREATE OR REPLACE TABLE `address`
(
    `id`            INT                             AUTO_INCREMENT PRIMARY KEY,
    `salutation`    ENUM ('','Mr.','Ms.','Dr.')     NULL,
    `first_name`    VARCHAR(50)                     NOT NULL,
    `last_name`     VARCHAR(50)                     NOT NULL,
    `address1`      VARCHAR(100)                    NOT NULL,
    `address2`      VARCHAR(100)                    NULL,
    `city`          VARCHAR(100)                    NOT NULL,
    `state_id`      INT                             NULL,
    `state`         VARCHAR(100)                    NOT NULL,
    `zip`           VARCHAR(20)                     NOT NULL,
    `country`       VARCHAR(50)                     NULL,
    `home_phone`    VARCHAR(20)                     NULL,
    `work_phone`    VARCHAR(20)                     NULL,
    `mobile_phone`  VARCHAR(20)                     NULL,
    `fax`           VARCHAR(20)                     NULL,
    `email`         VARCHAR(100)                    NULL,
    `url`           VARCHAR(255)                    NULL,
    `company`       VARCHAR(100)                    NULL,
    `title`         VARCHAR(50)                     NULL,
    `location`      VARCHAR(200)                    NULL,
    `latitude`      FLOAT                           NULL,
    `longitude`     FLOAT                           NULL,
    CONSTRAINT `fk_address_states`
        FOREIGN KEY (`state_id`) REFERENCES `states` (id)
            ON DELETE SET NULL
            ON UPDATE RESTRICT
)
ENGINE=InnoDB
CHARSET=Latin1;


ALTER TABLE `address` ADD CONSTRAINT `address_states_fk`
    FOREIGN KEY (`state_id`)
        REFERENCES `states` (`id`) ON DELETE SET NULL;
ALTER TABLE `address` ADD `mobile_phone` VARCHAR(20) NULL;
ALTER TABLE `address` ADD `company` VARCHAR(100) NULL;
ALTER TABLE `address` ADD `location` VARCHAR(200) NULL;
ALTER TABLE `address` ADD `latitude` FLOAT NULL;
ALTER TABLE `address` ADD `longitude` FLOAT NULL;
ALTER TABLE `address` ADD `url` VARCHAR(255) NULL;
ALTER TABLE `address` ADD `fax` VARCHAR(20) NULL;
ALTER TABLE `address` ADD `title` VARCHAR(50) NULL;
ALTER TABLE `address` CHANGE `state` `non_us_state` VARCHAR(100);
ALTER TABLE `address` CHANGE `company` `organization` VARCHAR(100) NULL;
