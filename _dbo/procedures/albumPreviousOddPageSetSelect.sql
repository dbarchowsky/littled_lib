DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumPreviousOddPageSetSelect`(
    IN p_parent_id INT,
    IN p_content_type_id INT,
    IN p_adjacent_page INT,
    IN p_adjacent_slot INT
)
BEGIN

    SELECT
        il.id,
        il.page_number,
        il.slot
    FROM image_link il
    WHERE (il.parent_id = p_parent_id)
    AND (il.type_id = p_content_type_id)
    AND (il.access = 'public')
    AND (DATEDIFF(il.release_date, NOW())<=0)
    AND (p_adjacent_slot IS NULL OR il.slot = p_adjacent_slot)
    AND (p_adjacent_page IS NULL OR il.page_number = p_adjacent_page)
    ORDER BY il.slot DESC, il.id DESC
    LIMIT 1;

END $$
