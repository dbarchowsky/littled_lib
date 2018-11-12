DROP PROCEDURE IF EXISTS `siteSectionClassesSelect`;
DELIMITER $$
CREATE PROCEDURE `siteSectionClassesSelect`(
  IN p_id INT
)
BEGIN
  SELECT 
    content_class,
    filters_class     
  FROM `site_section`
  WHERE (`id` = p_id);
END$$

DELIMITER ;