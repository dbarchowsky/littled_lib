DELIMITER $$
CREATE OR REPLACE PROCEDURE `lookupStateByName` (
    IN      p_state         VARCHAR(50)
)
BEGIN

    SELECT
        `id`,
        `name` AS `state_name`,
        `abbrev` AS `state_abbreviation`,
        IF(`charge_tax`, `sales_tax`, 0) AS `sales_tax`
    FROM `states`
    WHERE `name` LIKE p_state
    OR `abbrev` LIKE p_state;

END $$
