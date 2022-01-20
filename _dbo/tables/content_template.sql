create table content_template
(
    id              int auto_increment
        primary key,
    site_section_id int                                                 not null,
    name            varchar(45)                                         not null,
    path            varchar(255)                                        not null,
    location        enum ('local', 'shared', 'shared-cms') charset utf8 null,
    constraint idx_content_template_site_section_id_name
        unique (site_section_id, name)
)
    engine = MyISAM
    charset = latin1;

ALTER TABLE `content_template` CHANGE `location` `location`
    ENUM('local','shared', 'shared-cms') CHARACTER SET utf8
    COLLATE utf8_general_ci NULL;

ALTER TABLE `content_template` ADD CONSTRAINT fk_content_template_site_section
    FOREIGN KEY (site_section_id) REFERENCES site_section(id)
    ON DELETE CASCADE;