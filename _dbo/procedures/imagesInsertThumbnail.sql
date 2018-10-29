DROP PROCEDURE IF EXISTS `imagesInsertThumbnail`;
DELIMITER $$
CREATE PROCEDURE `imagesInsertThumbnail`(
  IN p_path VARCHAR(255),
  IN p_width INT,
  IN p_height INT,
  IN p_alt VARCHAR(255)
)
  BEGIN

    INSERT INTO `images` (
        `path`,
        `width`,
        `height`,
        `alt`
    ) VALUES (
        p_path,
        p_width,
        p_height,
        p_alt
    );

  END$$

DELIMITER ;
