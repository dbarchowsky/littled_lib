DROP TABLE IF EXISTS site_section_link;
CREATE TABLE site_section_link
(
    user_id    int default 0 not null,
    section_id int default 0 not null
)
    charset = latin1;

CREATE INDEX user_id
    ON site_section_link (user_id, section_id);

ALTER TABLE `site_section_link` ADD CONSTRAINT fk_site_section_site_section_link
    FOREIGN KEY (section_id) REFERENCES site_section(id)
        ON DELETE CASCADE;
ALTER TABLE `site_section_link` ADD CONSTRAINT fk_site_section_link_user
    FOREIGN KEY (user_id) REFERENCES site_user(id)
        ON DELETE CASCADE;
