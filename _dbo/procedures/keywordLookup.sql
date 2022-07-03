DELIMITER $$

CREATE OR REPLACE PROCEDURE `keywordLookup`(
    IN p_term VARCHAR(1000),
    IN p_type_id INT,
    IN p_parent_id INT
)
BEGIN
    SELECT COUNT(1) AS `match_count`
    FROM `keyword`
    WHERE (TRIM(`term`) = p_term)
    AND (`type_id` = p_type_id)
    AND (`parent_id` = p_parent_id);
END $$
