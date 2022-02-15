DROP PROCEDURE IF EXISTS `cardUpdate`;
DELIMITER $$
CREATE PROCEDURE `cardUpdate` (
    INOUT p_card_id INT
    , IN p_type_id INT
    , IN p_holders_name VARCHAR(100)
    , IN p_card_no VARCHAR(32)
    , IN p_sec_code VARCHAR(16)
    , IN p_expr_month INT
    , IN p_expr_year INT
    , IN p_key VARCHAR(256)
)
BEGIN

INSERT INTO `cards` (
    `id`,
    `type_id`,
    `holders_name`,
    `card_no`,
    `sec_code`,
    `expr_mon`,
    `expr_yr`
) VALUES (
    p_card_id,
    p_type_id,
    p_holders_name,
    AES_ENCRYPT(p_card_no, p_key),
    AES_ENCRYPT(p_sec_code, p_key),
    p_expr_month,
    p_expr_year
)
ON DUPLICATE KEY UPDATE
    `type_id` = p_type_id,
    `holders_name` = p_holders_name,
    `card_no` = AES_ENCRYPT(p_card_no, p_key),
    `sec_code` = AES_ENCRYPT(p_sec_code, p_key),
    `expr_mon` = p_expr_month,
    `expr_yr` = p_expr_year
;
SET p_card_id = LAST_INSERT_ID();

END$$

