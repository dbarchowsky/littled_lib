DELIMITER $$

CREATE OR REPLACE PROCEDURE `imageFormatUpdate`(
    INOUT   p_record_id         INT,
    IN      p_site_section_id   INT,
    IN      p_size_id           INT,
    IN      p_label             VARCHAR(50),
    IN      p_width             INT,
    IN      p_height            INT,
    IN      p_format            VARCHAR(8),
    IN      p_key_prefix        VARCHAR(16),
    IN      p_path              VARCHAR(255)
)
BEGIN

    INSERT INTO `image_formats` (
        `id`,
        `site_section_id`,
        `size_id`,
        `label`,
        `width`,
        `height`,
        `format`,
        `key_prefix`,
        `path`
    ) VALUES (
        p_record_id,
        p_site_section_id,
        p_size_id,
        p_label,
        p_width,
        p_height,
        p_format,
        p_key_prefix,
        p_path
    )
    ON DUPLICATE KEY UPDATE
        `site_section_id`       = p_site_section_id,
        `size_id`             = p_size_id,
        `label`                 = p_label,
        `width`             = p_width,
        `height`             = p_height,
        `format`             = p_format,
        `key_prefix`             = p_key_prefix,
        `path`             = p_path;

    IF p_record_id IS NULL THEN
        SELECT LAST_INSERT_ID() INTO p_record_id;
    END IF;

END $$
