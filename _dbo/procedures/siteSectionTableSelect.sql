DROP PROCEDURE IF EXISTS `siteSectionTableSelect`;
DELIMITER $$
CREATE PROCEDURE `siteSectionTableSelect`(
  IN p_content_type_id INT
)
  BEGIN

    SELECT `table`
    FROM `site_section`
    WHERE `id` = p_content_type_id;

  END$$
DELIMITER ;
