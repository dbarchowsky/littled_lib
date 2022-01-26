DROP PROCEDURE IF EXISTS `lookupStateByName`;
DELIMITER $$
CREATE PROCEDURE `lookupStateByName` (
    IN p_state VARCHAR(50)
)
BEGIN

    SELECt `id`, IF(`charge_tax`, `sales_tax`, 0) AS `sales_tax` FROM `states`
    WHERE `name` LIKE p_state
       OR `abbrev` LIKE p_state;

END
$$
