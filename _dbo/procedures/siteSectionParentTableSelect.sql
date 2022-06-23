DELIMITER $$

CREATE OR REPLACE PROCEDURE `siteSectionParentTableSelect`(
    IN p_content_type_id INT
)
BEGIN

    SELECT p.`table`
    FROM `site_section` p
    INNER JOIN `site_section` c ON p.`id` = c.`parent_id`
    WHERE c.`id` = p_content_type_id;

END $$
