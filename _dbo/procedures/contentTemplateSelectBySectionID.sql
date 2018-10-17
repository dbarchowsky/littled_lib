DROP PROCEDURE IF EXISTS `contentTemplateSelectBySectionID`;
DELIMITER $$
CREATE PROCEDURE `contentTemplateSelectBySectionID`(
  IN p_id INT
)
BEGIN

SELECT
  t.`id`,
  t.`name`,
  t.`path`,
  t.`location`
FROM `content_template` t
WHERE (t.`site_section_id` = p_id);

END$$

DELIMITER ;
