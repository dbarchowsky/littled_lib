DELIMITER $$

CREATE OR REPLACE PROCEDURE `contentTemplateSelectBySectionID`(
    IN p_id INT
)
BEGIN

    SELECT
        t.`id`,
        t.`name`,
        t.`path`,
        t.`location`,
        t.`container_id`
    FROM `content_template` t
    WHERE (t.`site_section_id` = p_id)
    ORDER BY t.`id`;

END $$
