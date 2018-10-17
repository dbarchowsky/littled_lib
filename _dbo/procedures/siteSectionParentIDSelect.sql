DROP PROCEDURE IF EXISTS `siteSectionParentIDSelect`;
DELIMITER $$
CREATE PROCEDURE `siteSectionParentIDSelect`(
  IN p_id INT
)
BEGIN
  SELECT `parent_id`
  FROM `site_section`
  WHERE (`id` = p_id);
END$$

DELIMITER ;