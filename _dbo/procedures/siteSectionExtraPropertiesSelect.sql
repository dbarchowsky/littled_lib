DELIMITER $$

CREATE OR REPLACE PROCEDURE `siteSectionExtraPropertiesSelect`(
    IN      p_id        INT
)
BEGIN
    SELECT
        so.`id_param`,
        IFNULL(so.`label`, IFNULL(ss.`image_label`, ss.`name`)) AS `label`,
        p.`name` AS `parent`
    FROM `site_section` ss
    LEFT JOIN `section_operations` so ON ss.id = so.section_id
    LEFT JOIN `site_section` p ON ss.parent_id = p.id
    WHERE (ss.id = p_id);
END $$
