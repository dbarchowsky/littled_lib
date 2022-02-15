DROP PROCEDURE IF EXISTS `siteSectionPropertiesSelect`;
DELIMITER $$
CREATE PROCEDURE `siteSectionPropertiesSelect`(
    IN      p_section_id        INT
)
BEGIN

    SELECT
         id,
         section_id,
         label,
         id_param,
         listings_uri,
         ajax_listings_uri,
         details_uri,
         edit_uri,
         upload_uri,
         delete_uri,
         cache_uri,
         sorting_uri,
         keywords_uri,
         listings_template,
         keywords_template,
         is_sortable,
         comments
    FROM section_operations
    WHERE section_id = p_section_id;

END $$
