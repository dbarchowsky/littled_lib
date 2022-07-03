DELIMITER $$
CREATE OR REPLACE PROCEDURE `siteSectionParentIDSelect`(
    IN p_id INT
)
BEGIN
    SELECT `parent_id`
    FROM `site_section`
    WHERE (`id` = p_id);
END $$
