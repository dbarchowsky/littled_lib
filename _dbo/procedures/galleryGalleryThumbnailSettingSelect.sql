DROP PROCEDURE IF EXISTS `galleryGalleryThumbnailSettingSelect`;
DELIMITER $$
CREATE PROCEDURE `galleryGalleryThumbnailSettingSelect`(
  IN p_content_type_id INT
)
  BEGIN

    SELECT p.`gallery_thumbnail`, c.`parent_id` 
    FROM `site_section` c 
    INNER JOIN `site_section` p ON c.`parent_id` = p.`id` 
    WHERE c.`id` = p_content_type_id;

    END $$
DELIMITER ;