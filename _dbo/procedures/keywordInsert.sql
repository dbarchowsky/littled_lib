DELIMITER $$

CREATE OR REPLACE PROCEDURE `keywordInsert`(
    IN p_term VARCHAR(1000),
    IN p_type_id INT,
    IN p_parent_id INT
)
BEGIN
    INSERT IGNORE INTO `keyword` (`term`,`parent_id`,`type_id`)
    VALUES (
        p_term,
        p_parent_id,
        p_type_id
    );
END $$
