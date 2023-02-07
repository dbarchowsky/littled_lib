DELIMITER $$

CREATE OR REPLACE PROCEDURE `contentRouteSelect`(
    IN p_id                 INT,
    IN p_site_section_id    INT,
    IN p_operation          VARCHAR(45)
)
BEGIN

    SELECT
        r.`id`,
        r.site_section_id,
        r.`operation`,
        r.`route`,
        r.`url`
    FROM `content_route` r
    WHERE (p_id IS NULL OR r.`id` = p_id)
    AND ((p_site_section_id IS NULL OR r.site_section_id = p_site_section_id))
    AND (NULLIF(p_operation, '') IS NULL OR r.operation = p_operation)
    ORDER BY r.`id`;

END $$
