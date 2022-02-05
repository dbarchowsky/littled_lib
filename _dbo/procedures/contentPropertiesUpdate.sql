DROP PROCEDURE IF EXISTS `contentPropertiesUpdate`;
DELIMITER $$
CREATE PROCEDURE `contentPropertiesUpdate`(
    INOUT p_record_id       INT,
    IN p_name               VARCHAR(50),
    IN p_slug               VARCHAR(50),
    IN p_root_dir           VARCHAR(256),
    IN p_image_path         VARCHAR(256),
    IN p_sub_dir            VARCHAR(100),
    IN p_image_label        VARCHAR(100),
    IN p_width              INTEGER,
    IN p_height             INTEGER,
    IN p_med_width          INTEGER,
    IN p_med_height         INTEGER,
    IN p_save_mini          BOOLEAN,
    IN p_mini_width         INTEGER,
    IN p_mini_height        INTEGER,
    IN p_format             VARCHAR(8),
    IN p_key_prefix         VARCHAR(8),
    IN p_table              VARCHAR(50),
    IN p_parent_id          INTEGER,
    IN p_is_cached          BOOLEAN,
    IN p_gallery_thumbnail  TINYINT
)
BEGIN

INSERT INTO `site_section` (
    `id`,
    `name`,
    `slug`,
    `root_dir`,
    `image_path`,
    `sub_dir`,
    `image_label`,
    `width`,
    `height`,
    `med_width`,
    `med_height`,
    `save_mini`,
    `mini_width`,
    `mini_height`,
    `format`,
    `param_prefix`,
    `table`,
    `parent_id`,
    `is_cached`,
    `gallery_thumbnail`
) VALUES (
     p_record_id,
     p_name,
     p_slug,
     p_root_dir,
     p_image_path,
     p_sub_dir,
     p_image_label,
     p_width,
     p_height,
     p_med_width,
     p_med_height,
     p_save_mini,
     p_mini_width,
     p_mini_height,
     p_format,
     p_key_prefix,
     p_table,
     p_parent_id,
     p_is_cached,
     p_gallery_thumbnail
)
ON DUPLICATE KEY UPDATE
     `name`                 = p_name,
     `slug`                 = p_slug,
     `root_dir`             = p_root_dir,
     `image_path`           = p_image_path,
     `sub_dir`              = p_sub_dir,
     `image_label`          = p_image_label,
     `width`                = p_width,
     `height`               = p_height,
     `med_width`            = p_med_width,
     `med_height`           = p_med_height,
     `save_mini`            = p_save_mini,
     `mini_width`           = p_mini_width,
     `mini_height`          = p_mini_height,
     `format`               = p_format,
     `param_prefix`         = p_key_prefix,
     `table`                = p_table,
     `parent_id`            = p_parent_id,
     `is_cached`            = p_is_cached,
     `gallery_thumbnail`    = p_gallery_thumbnail;

IF p_record_id IS NULL THEN
    SELECT LAST_INSERT_ID() INTO p_record_id;
END IF;

END$$

DELIMITER ;
