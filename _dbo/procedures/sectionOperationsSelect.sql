DELIMITER $$

CREATE OR REPLACE PROCEDURE `sectionOperationsSelect`(
    IN      p_section_id        INT
)
BEGIN

    SELECT
         id,
         section_id,
         label,
         id_key,
         listings_uri,
         ajax_listings_uri,
         details_uri,
         edit_uri,
         upload_uri,
         delete_uri,
         cache_uri,
         sorting_uri,
         keywords_uri,
         is_sortable,
         comments
    FROM section_operations
    WHERE section_id = p_section_id;

END $$
