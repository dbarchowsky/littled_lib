DELIMITER $$

CREATE OR REPLACE PROCEDURE `keywordDeleteLinked`(
    IN p_parent_id INT,
    IN p_content_type_id INT
)
BEGIN

    DELETE FROM `keyword`
    WHERE `parent_id` = p_parent_id
    AND `type_id` = p_content_type_id;

END $$
