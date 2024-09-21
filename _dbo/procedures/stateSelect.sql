DELIMITER $$
CREATE OR REPLACE PROCEDURE `stateSelect` (
    IN p_id INT
)
BEGIN

    SELECT `name` AS `state_name`,
           `abbrev` AS `state_abbreviation`,
           IF(`charge_tax`, `sales_tax`, 0) AS `active_sales_tax`,
           `sales_tax`,
           `charge_tax`
    FROM `states`
    WHERE `id` = p_id;

END $$
