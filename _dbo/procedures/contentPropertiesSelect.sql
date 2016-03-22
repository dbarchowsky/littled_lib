DROP PROCEDURE IF EXISTS `contentPropertiesSelect`;
DELIMITER $$
CREATE DEFINER=`chicot`@`localhost` PROCEDURE `contentPropertiesSelect`(
  IN p_id INT
)
  BEGIN
    SELECT
      ss.`name`,
      ss.`slug`,
      ss.`root_dir`,
      ss.`image_path`,
      ss.`sub_dir`,
      ss.`image_label`,
      ss.`width`,
      ss.`height`,
      ss.`med_width`,
      ss.`med_height`,
      ss.`save_mini`,
      ss.`mini_width`,
      ss.`mini_height`,
      ss.`format`,
      ss.`param_prefix`,
      ss.`table`,
      ss.`parent_id`,
      ss.`is_cached`,
      ss.`gallery_thumbnail`,
      so.`id_param`,
      IFNULL(so.`label`, IFNULL(ss.`image_label`, ss.`name`)) `label`,
      p.`name` `parent`
    FROM `site_section` ss
    LEFT JOIN `section_operations` so ON ss.id = so.section_id
    LEFT JOIN `site_section` p ON ss.parent_id = p.id
    WHERE (ss.id = p_id);
  END$$

DELIMITER ;
