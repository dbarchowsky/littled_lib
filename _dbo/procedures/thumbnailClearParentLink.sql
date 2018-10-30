DROP PROCEDURE IF EXISTS `thumbnailUnsetParentLink`;
DELIMITER $$
CREATE PROCEDURE `thumbnailUnsetParentLink`(
  IN p_table VARCHAR(127),
  IN p_parent_id INT
)
  BEGIN

    SET @table_name = p_table;
    SET @parent_id = p_parent_id;

    PREPARE STMT FROM
      'UPDATE `?` SET `tn_id` = NULL WHERE `id` = ?';
    EXECUTE STMT USING
      @table_name,
      @parent_id;
    DEALLOCATE PREPARE STMT;

  END$$
DELIMITER ;
