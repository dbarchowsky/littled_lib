create table site_user
(
    id            int auto_increment
        primary key,
    login         varchar(50)  default '' not null,
    password      varchar(256) default '' not null,
    contact_id    int                     not null,
    access        tinyint(1)              not null,
    email_opt_in  tinyint(1)              null,
    postal_opt_in tinyint(1)              null
)
    engine = InnoDB;

create index IX_site_user_access
    on site_user (access);

create index IX_site_user_contact_id
    on site_user (contact_id);

create index IX_site_user_login
    on site_user (login);

create index IX_site_user_password
    on site_user (password);

DROP INDEX `IX_site_user_access` ON `site_user`;

ALTER TABLE site_user CHANGE COLUMN access access_id int NOT NULL;

ALTER TABLE `site_user`
    ADD CONSTRAINT `fk_site_user_access_id__site_user_access_id`
        FOREIGN KEY (`access_id`)
            REFERENCES `site_user_access` (`id`)
            ON UPDATE CASCADE
            ON DELETE RESTRICT;
