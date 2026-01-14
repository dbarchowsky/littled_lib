DELIMITER $$
CREATE OR REPLACE PROCEDURE `lookupStateByName` (
    IN      p_state         VARCHAR(50)
)
BEGIN

    SELECT
        `id` AS `state_id`,
        `name` AS `state_name`,
        `abbrev` AS `state_abbrev`,
        sales_tax AS `state_sales_tax`,
        charge_tax AS `state_charge_tax`
    FROM `states`
    WHERE `name` LIKE p_state
    OR `abbrev` LIKE p_state;

END $$
