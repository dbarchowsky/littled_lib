DROP PROCEDURE IF EXISTS `contentRouteUpdate`;
DELIMITER $$
CREATE PROCEDURE `contentRouteUpdate`(
    INOUT   p_record_id         INT,
    IN      p_site_section_id   INT,
    IN      p_operation              VARCHAR(45),
    IN      p_url               VARCHAR(256)
)
BEGIN

INSERT INTO `content_route` (
    `id`,
    `site_section_id`,
    `operation`,
    `url`
) VALUES (
    p_record_id,
    p_site_section_id,
    p_operation,
    p_url
)
ON DUPLICATE KEY UPDATE
    `site_section_id`       = p_site_section_id,
    `operation`                  = p_operation,
    `url`                   = p_url;

IF p_record_id IS NULL THEN
    SELECT LAST_INSERT_ID() INTO p_record_id;
END IF;

END$$

DELIMITER ;
