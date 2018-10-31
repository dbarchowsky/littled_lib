DROP PROCEDURE IF EXISTS `albumLastPageSelect`;
DELIMITER $$
CREATE PROCEDURE `albumLastPageSelect`(
  IN p_parent_id INT,
  IN p_content_type_id INT
)
  BEGIN

    SELECT
           il.`id`
    FROM `image_link` il
    INNER JOIN `images` f ON il.`fullres_id` = f.`id`
    WHERE (il.`parent_id` = p_parent_id)
      AND (il.`type_id` = p_content_type_id)
      AND (il.`access` = 'public')
      AND (DATEDIFF(il.`release_date`, NOW())<=0)
    ORDER BY IFNULL(il.`page_number`,999999) DESC, il.`slot` DESC, il.`id` desc
    LIMIT 1;

  END$$

DELIMITER ;
