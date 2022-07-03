DELIMITER $$
CREATE OR REPLACE PROCEDURE `siteSectionThumbnailTableSelect`(
    IN p_content_type_id INT
)
BEGIN

    SELECT s.`table`
    FROM `site_section` s
    WHERE s.`id` = p_content_type_id
    AND IFNULL(s.`gallery_thumbnail`,0) = 0;

END $$
