DELIMITER $$

CREATE OR REPLACE PROCEDURE `keywordSelectLinked`(
    IN p_parent_id INT,
    IN p_content_type_id INT
)
BEGIN

    SELECT
        k1.`term`,
        COUNT(1) AS `count`
    FROM `keyword` k1
    INNER JOIN `keyword` k2 ON (k1.`term` = k2.`term` AND k2.`type_id` = p_content_type_id)
    WHERE k1.`parent_id` = p_parent_id
    AND k1.`type_id` = p_content_type_id
    GROUP BY k1.`term`
    ORDER BY COUNT(*) DESC, `term` ASC;

END $$
