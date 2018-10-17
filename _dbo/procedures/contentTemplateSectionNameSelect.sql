DROP PROCEDURE IF EXISTS `contentTemplateSectionNameSelect`;
DELIMITER $$
CREATE PROCEDURE `contentTemplateSectionNameSelect`(
  IN p_content_type_id INT,
  IN p_template_name VARCHAR(45)
)

BEGIN

  SELECT t.`id`,
         s.`name` as `section`
  FROM `content_template` t
         INNER JOIN `site_section` s ON t.`site_section_id` = s.`id`
  WHERE (t.`site_section_id` = p_content_type_id)
    AND (t.`name` = p_template_name);

END $$

DELIMITER ;
