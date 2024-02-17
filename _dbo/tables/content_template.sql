CREATE OR REPLACE TABLE content_template
(
    id                  INT                                         AUTO_INCREMENT PRIMARY KEY,
    site_section_id     INT                                         NOT NULL,
    name                VARCHAR(45)                                 NOT NULL,
    path                VARCHAR(255)                                NOT NULL,
    location            ENUM ('local', 'shared', 'shared-cms')      NULL,
    CONSTRAINT          idx_content_template_site_section_id_name
        UNIQUE (site_section_id, name),
    CONSTRAINT fk_content_template_site_section
        FOREIGN KEY (site_section_id) REFERENCES site_section(id)
        ON DELETE CASCADE
)
ENGINE = InnoDB
CHARSET = latin1;

ALTER TABLE `content_template` CHANGE `location` `location`
    ENUM('local','shared', 'shared-cms') CHARACTER SET utf8
    COLLATE utf8_general_ci NULL;

ALTER TABLE `content_template` ADD CONSTRAINT fk_content_template_site_section
    FOREIGN KEY (site_section_id) REFERENCES site_section(id)
    ON DELETE CASCADE;

ALTER TABLE `content_template` ADD COLUMN `container_id` VARCHAR(50) DEFAULT '' AFTER `location`;

INSERT INTO content_template
    (site_section_id, name, path, location, container_id)
VALUES
    (6037, 'edit-status', 'forms/ajax/edit_status.php', 'shared', '#inline-cell-[#]');

UPDATE content_template
set ``.content_template.container_id = '#inline-status-[#]'
where site_section_id = 6037 and name = 'edit-status';

