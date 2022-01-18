ALTER TABLE `content_template` CHANGE `location` `location`
    ENUM('local','shared', 'shared-cms') CHARACTER SET utf8
    COLLATE utf8_general_ci NULL;
