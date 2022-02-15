DROP PROCEDURE IF EXISTS `cardSelect`;
DELIMITER $$
CREATE PROCEDURE `cardSelect` (
	IN p_card_id INT,
	IN p_key VARCHAR(256)
)
BEGIN

SELECT
    t.name as card_type,
    c.holders_name,
    AES_DECRYPT(c.card_no, p_key) as card_number,
    AES_DECRYPT(c.sec_code, p_key) as security_code,
    c.expr_mon as expiration_month,
    c.expr_yr as expiration_year,
    c.type_id
FROM `cards` c
         INNER JOIN card_type t on c.type_id = t.id
WHERE c.id = p_card_id;

END
$$


