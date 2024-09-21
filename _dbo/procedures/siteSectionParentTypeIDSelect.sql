DELIMITER $$
CREATE OR REPLACE PROCEDURE `siteSectionParentTypeIDSelect`(
  IN p_content_type_id INT
)
BEGIN
  SELECT `parent_id`
  FROM `site_section`
  WHERE (`id` = p_content_type_id);
END$$
