DROP PROCEDURE IF EXISTS `imageLinkSelect`;
DELIMITER $$
CREATE PROCEDURE `imageLinkSelect`(
  IN p_id INT,
  IN p_parent_id INT,
  IN p_type_id INT
)
BEGIN

  SELECT
     l.fullres_id,
     l.parent_id,
     l.type_id,
     l.title,
     l.description,
     s.name type_name,
     full.path,
     full.width,
     full.height,
     full.url,
     full.target,
     l.med_id,
     med.path med_path,
     med.width med_width,
     med.height med_height,
     l.mini_id,
     mini.path mini_path,
     mini.width mini_width,
     mini.height mini_height,
     l.slot,
     l.page_number,
     l.access,
     l.release_date
  FROM `image_link` l
  LEFT JOIN images full ON l.fullres_id = full.id
  LEFT JOIN images med ON l.med_id = med.id
  LEFT JOIN images mini ON l.mini_id = mini.id
  INNER JOIN site_section s ON l.type_id = s.id
  WHERE (p_id > 0 AND l.id = p_id)
  OR (l.parent_id = p_parent_id AND l.type_id = p_type_id)
  ORDER BY l.id DESC
  LIMIT 1;

END$$

DELIMITER ;
