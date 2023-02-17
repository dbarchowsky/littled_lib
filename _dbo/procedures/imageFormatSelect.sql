DELIMITER $$

CREATE OR REPLACE PROCEDURE `imageFormatSelect`(
    IN p_id                 INT,
    IN p_site_section_id    INT
)
BEGIN

    SELECT
        f.`id`,
        f.site_section_id,
        f.size_id,
        f.`label`,
        f.`width`,
        f.`height`,
        f.`format`,
        f.`key_prefix`,
        f.`path`,
        ss.`name` as `section`,
        s.`name` as `size`
    FROM `image_formats` f
    INNER JOIN site_section ss on f.site_section_id = ss.id
    INNER JOIN image_sizes s on f.size_id = s.id
    WHERE (p_id IS NULL OR f.`id` = p_id)
    AND ((p_site_section_id IS NULL OR f.site_section_id = p_site_section_id))
    ORDER BY ss.`name`, s.`name`;

END $$
