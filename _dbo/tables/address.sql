# update address table properties
ALTER TABLE `address` CHANGE `firstname` `first_name` VARCHAR(50) NOT NULL;
ALTER TABLE `address` CHANGE `lastname` `last_name` VARCHAR(50) NOT NULL;
ALTER TABLE `address` CHANGE `province` `state` VARCHAR(100) NOT NULL;
ALTER TABLE `address` CHANGE `day_phone` `home_phone` VARCHAR(20) NULL;
ALTER TABLE `address` CHANGE `eve_phone` `work_phone` VARCHAR(20) NULL;
