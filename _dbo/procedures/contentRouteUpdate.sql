DELIMITER $$

CREATE OR REPLACE PROCEDURE `contentRouteUpdate`(
    INOUT   p_record_id         INT,
    IN      p_site_section_id   INT,
    IN      p_operation         VARCHAR(45),
    IN      p_route             VARCHAR(255),
    IN      p_api_route         VARCHAR(256)
)
BEGIN

    INSERT INTO `content_route` (
        `id`,
        `site_section_id`,
        `operation`,
        `route`,
        `api_route`
    ) VALUES (
        p_record_id,
        p_site_section_id,
        p_operation,
        p_route,
        p_api_route
    )
    ON DUPLICATE KEY UPDATE
        `site_section_id`       = p_site_section_id,
        `operation`             = p_operation,
        `route`                 = p_route,
        `api_route`             = p_api_route;

    IF p_record_id IS NULL THEN
        SELECT LAST_INSERT_ID() INTO p_record_id;
    END IF;

END $$
