DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`tester`@`localhost` PROCEDURE `contentPropertiesSelect`(
  IN p_id INT
)
  BEGIN
    SELECT
      c.name,
      c.slug,
      c.root_dir,
      c.image_path,
      c.sub_dir,
      c.image_label,
      c.width,
      c.height,
      c.med_width,
      c.med_height,
      c.save_mini,
      c.mini_width,
      c.mini_height,
      c.format,
      c.param_prefix,
      c.`table`,
      c.parent_id,
      c.is_cached,
      c.gallery_thumbnail
    FROM `site_section` c
    WHERE (c.id = p_id);
  END$$

DELIMITER ;
