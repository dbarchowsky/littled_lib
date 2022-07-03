DELIMITER $$

CREATE OR REPLACE PROCEDURE `thumbnailUpdateParentWithImageLink`(
    IN p_table VARCHAR(127),
    IN p_parent_id INT,
    IN p_image_link_id INT,
    IN p_content_type_id INT
)
BEGIN

    SET @table_name = p_table;
    SET @parent_id = p_parent_id;
    SET @image_link_id = p_image_link_id;
    SET @content_type_id = p_content_type_id;

    PREPARE STMT FROM
      'UPDATE `?`
      SET `tn_id` =
        (
          SELECT `id`
          FROM `image_link`
          WHERE `parent_id` = ?
          AND `type_id` = ?
          ORDER BY IFNULL(`slot`,999999) ASC, `slot` DESC
          LIMIT 1
        )
      WHERE `id` = ?
      AND `tn_id` = ?';
    EXECUTE STMT USING
      @table_name,
      @parent_id,
      @content_type_id,
      @parent_id,
      @content_type_id;
    DEALLOCATE PREPARE STMT;

END $$
