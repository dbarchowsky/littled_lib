DROP PROCEDURE IF EXISTS `contentTemplateLookup`;
DELIMITER $$
CREATE PROCEDURE `contentTemplateLookup`(
  IN p_content_type_id INT,
  IN p_template_name VARCHAR(45)
)
  BEGIN

    SELECT 
      t.`id`, 
      t.`name`, 
      s.`root_dir` AS `base_path`, 
      t.`path` AS `template_path`, 
      t.`location` 
    FROM `content_template` t 
    INNER JOIN `site_section` s ON t.`site_section_id` = s.`id` 
    WHERE (t.`site_section_id` = p_content_type_id)
    AND (t.`name` = p_template_name);

  END$$

DELIMITER ;
