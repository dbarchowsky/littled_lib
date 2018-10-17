DROP PROCEDURE IF EXISTS `siteSectionPropertiesSelect`;
DELIMITER $$
CREATE PROCEDURE `siteSectionPropertiesSelect`(
  IN p_section_id INT
)
BEGIN

SET @section_id = p_section_id;

PREPARE STMT FROM
'SELECT *
FROM `section_operations`
WHERE `section_id` = ?';

EXECUTE STMT USING
@section_id;
DEALLOCATE PREPARE STMT;

END$$
DELIMITER ;
