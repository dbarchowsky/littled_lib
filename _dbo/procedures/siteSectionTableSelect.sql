DELIMITER $$
CREATE OR REPLACE PROCEDURE `siteSectionTableSelect`(
    IN p_content_type_id INT
)
BEGIN

    SELECT `table`
    FROM `site_section`
    WHERE `id` = p_content_type_id;

END $$
