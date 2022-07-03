DELIMITER $$

CREATE OR REPLACE PROCEDURE `keywordDelete`(
    IN p_term VARCHAR(1000),
    IN p_type_id INT,
    IN p_parent_id INT
)
BEGIN
    DELETE FROM `keyword`
    WHERE `term` = p_term
    AND `parent_id` = p_parent_id
    AND `type_id` = p_type_id;
END $$
