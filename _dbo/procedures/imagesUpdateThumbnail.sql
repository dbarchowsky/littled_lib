DROP PROCEDURE IF EXISTS `imagesUpdateThumbnail`;
DELIMITER $$
CREATE PROCEDURE `imagesUpdateThumbnail`(
  IN p_id INT,
  IN p_path VARCHAR(255),
  IN p_width INT,
  IN p_height INT,
  IN p_alt VARCHAR(255)
)
  BEGIN

  UPDATE `images` SET
    `path` = p_path,
    `width` = p_width,
    `height` = p_height,
    `alt` = p_alt
  WHERE `id` = p_id;

  END$$

DELIMITER ;
