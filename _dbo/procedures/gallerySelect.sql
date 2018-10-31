DROP PROCEDURE IF EXISTS `gallerySelect`;
DELIMITER $$
CREATE PROCEDURE `gallerySelect`(
  IN p_parent_id INT,
  IN p_content_type_id INT,
  IN p_public_only BOOL
)
BEGIN

  SELECT
    l.id,
    l.title,
    l.description,
    l.slot,
    l.page_number,
    l.access,
    DATE_FORMAT(l.release_date, '%c/%e/%Y') release_date,
    full.id full_id,
    full.path full_path,
    full.width full_width,
    full.height full_height,
    med.id med_id,
    med.path med_path,
    med.width med_width,
    med.height med_height,
    mini.id mini_id,
    mini.path mini_path,
    mini.width mini_width,
    mini.height mini_height
  FROM image_link l
  INNER JOIN `images` full ON l.fullres_id = full.id
  LEFT JOIN `images` med ON l.med_id = med.id
  LEFT JOIN `images` mini ON l.mini_id = mini.id
  WHERE (l.parent_id = p_parent_id)
  AND (l.type_id = p_content_type_id)
  AND (p_public_only = 0 OR (l.access = 'public' AND DATEDIFF(l.`release_date`, NOW()) <= 0))
  ORDER BY IFNULL(l.page_number,999999) ASC, IFNULL(l.slot,999999) ASC, l.id ASC;

END$$

DELIMITER ;
