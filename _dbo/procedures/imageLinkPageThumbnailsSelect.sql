DROP PROCEDURE IF EXISTS `imageLinkPageThumbnailSelect`;
DELIMITER $$
CREATE PROCEDURE `imageLinkPageThumbnailSelect`(
  IN p_parent_id INT,
  IN p_limit INT
)
  BEGIN

  SET @parent_id = p_parent_id;
  SET @limit = p_limit;

  PREPARE STMT FROM
  'SELECT
         p.`id`,
         i.`path`,
         i.`width`,
         i.`height`,
         i.`alt`
  FROM `image_link` p
  INNER JOIN `album` b ON p.`parent_id` = b.`id`
  INNER JOIN `images` i ON p.`med_id` = i.`id`
  WHERE (b.id = ?)
  AND (p.access = ''public'')
  ORDER BY p.slot
  LIMIT ?;';

  EXECUTE STMT USING
    @parent_id,
    @limit;

  DEALLOCATE PREPARE STMT;

  END $$

DELIMITER ;