DELIMITER $$

CREATE OR REPLACE PROCEDURE `siteSectionClassesSelect`(
  IN p_id INT
)
BEGIN
  SELECT 
    content_class,
    filters_class     
  FROM `site_section`
  WHERE (`id` = p_id);
END $$
