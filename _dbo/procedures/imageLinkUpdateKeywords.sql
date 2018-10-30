DROP PROCEDURE IF EXISTS `imageLinkUpdateKeywords`;
DELIMITER $$
CREATE PROCEDURE `imageLinkUpdateKeywords`(
  IN p_id INT
)
BEGIN

  UPDATE `image_link` il
  SET il.`keywords` =
    (
    SELECT GROUP_CONCAT(DISTINCT k.term ORDER BY k.term SEPARATOR ' ')
    FROM `keyword` k
    WHERE k.parent_id = il.id and k.type_id = il.type_id
    )
  WHERE (il.id = p_id);

END$$

DELIMITER ;
