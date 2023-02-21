DELIMITER $$

CREATE OR REPLACE PROCEDURE `testTableUpdate`(
    INOUT p_record_id       INT,
    IN p_name               VARCHAR(50),
    IN p_int_col            INT,
    IN p_bool_col           BOOL,
    IN p_date               DATETIME,
    IN p_slot               INT
)
BEGIN

INSERT INTO `test_table` (
    `id`,
    `name`,
    `int_col`,
    `bool_col`,
    `date`,
    `slot`
) VALUES (
     p_record_id,
     p_name,
     p_int_col,
     p_bool_col,
     p_date,
     p_slot
)
ON DUPLICATE KEY UPDATE
     `name`                 = p_name,
     `int_col`              = p_int_col,
     `bool_col`             = p_bool_col,
     `date`                 = p_date,
     `slot`                 = p_slot;

IF p_record_id IS NULL THEN
    SELECT LAST_INSERT_ID() INTO p_record_id;
END IF;

END$$

DELIMITER ;
