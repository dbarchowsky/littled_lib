DELIMITER $$
CREATE OR REPLACE PROCEDURE `lookupUserEmail` (
    IN      p_email         VARCHAR(100),
    IN      p_user_id       INT
)
BEGIN

    SELECT COUNT(1) AS `count`
    FROM `address` a
    INNER JOIN site_user u on a.id = u.contact_id
    WHERE a.email LIKE p_email
    AND (p_user_id IS NULL OR u.id = p_user_id);

END $$
