DELIMITER $$

CREATE OR REPLACE PROCEDURE `imagesUpdate`(
    IN p_id INT,
    IN p_path VARCHAR(255),
    IN p_width INT,
    IN p_height INT,
    IN p_alt VARCHAR(255),
    IN p_url VARCHAR(255),
    IN p_target VARCHAR(16),
    IN p_caption TEXT,
    IN p_save_path BOOL
)
BEGIN

    IF p_save_path = 1 THEN

      INSERT IGNORE INTO `images` (
        `id`,
        `path`,
        `width`,
        `height`,
        `alt`,
        `url`,
        `target`,
        `caption`
      ) VALUES (
        p_id,
        p_path,
        p_width,
        p_height,
        p_alt,
        p_url,
        p_target,
        p_caption
      )
      ON DUPLICATE KEY UPDATE
        `path` = p_path,
        `width` = p_width,
        `height` = p_height,
        `alt` = p_alt,
        `url` = p_url,
        `target` = p_target,
        `caption` = p_caption;

    ELSE

      INSERT IGNORE INTO `images` (
        `id`,
        `alt`,
        `url`,
        `target`,
        `caption`
      ) VALUES (
         p_id,
         p_alt,
         p_url,
         p_target,
         p_caption
      )
      ON DUPLICATE KEY UPDATE
        `alt` = p_alt,
        `url` = p_url,
        `target` = p_target,
        `caption` = p_caption;

    END IF;

END $$
