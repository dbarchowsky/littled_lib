DELIMITER $$

CREATE OR REPLACE PROCEDURE `siteSectionUpdate`(
    INOUT p_record_id       INT,
    IN p_name               VARCHAR(50),
    IN p_label              VARCHAR(50),
    IN p_id_key             VARCHAR(20),
    IN p_slug               VARCHAR(50),
    IN p_root_dir           VARCHAR(256),
    IN p_table              VARCHAR(50),
    IN p_parent_id          INTEGER,
    IN p_is_cached          BOOLEAN,
    IN p_is_sortable        BOOLEAN,
    IN p_gallery_thumbnail  TINYINT
)
BEGIN

INSERT INTO `site_section` (
    `id`,
    `name`,
    `label`,
    `id_key`,
    `slug`,
    `root_dir`,
    `table`,
    `parent_id`,
    `is_cached`,
    `is_sortable`,
    `gallery_thumbnail`
) VALUES (
     p_record_id,
     p_name,
     p_label,
     p_id_key,
     p_slug,
     p_root_dir,
     p_table,
     p_parent_id,
     p_is_cached,
     p_is_sortable,
     p_gallery_thumbnail
)
ON DUPLICATE KEY UPDATE
     `name`                 = p_name,
     `label`                = p_label,
     `id_key`               = p_id_key,
     `slug`                 = p_slug,
     `root_dir`             = p_root_dir,
     `table`                = p_table,
     `parent_id`            = p_parent_id,
     `is_cached`            = p_is_cached,
     `is_sortable`          = p_is_sortable,
     `gallery_thumbnail`    = p_gallery_thumbnail;

IF p_record_id IS NULL THEN
    SELECT LAST_INSERT_ID() INTO p_record_id;
END IF;

END$$

DELIMITER ;
