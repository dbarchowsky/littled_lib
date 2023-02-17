CREATE OR REPLACE TABLE site_section
(
    `id`                  INT                         AUTO_INCREMENT PRIMARY KEY,
    `name`                VARCHAR(50)                 NOT NULL,
    `slug`                VARCHAR(50)                 NULL,
    `root_dir`            VARCHAR(255)                NULL,
    `image_path`          VARCHAR(255)                NULL,
    `sub_dir`             VARCHAR(100)                NULL,
    `image_label`         VARCHAR(100)                NULL,
    `width`               INT                         NULL,
    `height`              INT                         NULL,
    `med_width`           INT                         NULL,
    `med_height`          INT                         NULL,
    `save_mini`           TINYINT(1)                  NULL,
    `mini_width`          INT                         NULL,
    `mini_height`         INT                         NULL,
    `format`              ENUM ('', 'png', 'jpg')     NULL,
    `param_prefix`        VARCHAR(8)                  NULL,
    `table`             VARCHAR(50)                 NULL,
    `content_class`       VARCHAR(255)                NULL,
    `filters_class`       VARCHAR(255)                NULL,
    `parent_id`           INT                         NULL,
    `is_cached`           TINYINT(1)                  NULL,
    `gallery_thumbnail`   TINYINT(1)                  NULL DEFAULT 0,
    CONSTRAINT uq_site_section_name
        UNIQUE (name)
)
    ENGINE=InnoDB
    CHARSET=latin1;

ALTER TABLE site_section ADD content_class varchar(255) NULL AFTER `table`;
ALTER TABLE site_section ADD filters_class varchar(255) NULL AFTER `content_class`;

ALTER TABLE site_section DROP COLUMN image_label;
ALTER TABLE site_section DROP COLUMN image_path;
ALTER TABLE site_section DROP COLUMN sub_dir;
ALTER TABLE site_section DROP COLUMN width;
ALTER TABLE site_section DROP COLUMN height;
ALTER TABLE site_section DROP COLUMN med_width;
ALTER TABLE site_section DROP COLUMN med_height;
ALTER TABLE site_section DROP COLUMN save_mini;
ALTER TABLE site_section DROP COLUMN mini_width;
ALTER TABLE site_section DROP COLUMN mini_height;
ALTER TABLE site_section DROP COLUMN `format`;
ALTER TABLE site_section DROP COLUMN param_prefix;
