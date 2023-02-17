DELIMITER $$

CREATE OR REPLACE PROCEDURE `siteSectionSelect`(
    IN p_id INT
)
BEGIN
    SELECT
        ss.`name`,
        ss.`label`,
        ss.`id_key`,
        ss.`slug`,
        ss.`root_dir`,
        ss.`table`,
        ss.`parent_id`,
        ss.`is_cached`,
        ss.`is_sortable`,
        ss.`gallery_thumbnail`,
        so.`id_key`,
        IFNULL(so.`label`, ss.`name`) AS `label`,
        p.`name` `parent`
    FROM `site_section` ss
    LEFT JOIN `section_operations` so ON ss.id = so.section_id
    LEFT JOIN `site_section` p ON ss.parent_id = p.id
    WHERE (ss.id = p_id);
END $$
