DROP PROCEDURE IF EXISTS `userAccountUpdate`;
DELIMITER $$
CREATE PROCEDURE `userAccountUpdate` (
    INOUT   p_record_id         INT,
    IN      p_login             VARCHAR(50),
    IN      p_password          VARCHAR(100),
    IN      p_contact_id        INT,
    IN      p_access            TINYINT(1),
    IN      p_email_opt_in      TINYINT(1),
    IN      p_postal_opt_int    TINYINT(1),
    IN      p_key               VARCHAR(256)
)
BEGIN

INSERT INTO `site_user` (
    `id`,
    `login`,
    `password`,
    `contact_id`,
    `access`,
    `email_opt_in`,
    `postal_opt_in`
) VALUES (
    p_record_id,
    p_login,
    AES_ENCRYPT(p_password, p_key),
    p_contact_id,
    p_access,
    p_email_opt_in,
    p_postal_opt_int
)
ON DUPLICATE KEY UPDATE
    `login` = p_login,
    `password` = AES_ENCRYPT(p_password, p_key),
    `contact_id` = p_contact_id,
    `access` = p_access,
    `email_opt_in` = p_email_opt_in,
    `postal_opt_in` = p_postal_opt_int
;
SET p_record_id = LAST_INSERT_ID();

END $$
