DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumNextPageSetSelect`(
    IN p_parent_id INT,
    IN p_content_type_id INT,
    IN p_page_id INT,
    IN p_page INT,
    IN p_slot INT
)
BEGIN

    SELECT
        il.id,
        il.page_number,
        il.slot
    FROM image_link il
    INNER JOIN images f ON il.fullres_id = f.id
    WHERE (il.parent_id = p_parent_id)
    AND (il.type_id = p_content_type_id)
    AND (il.access = 'public')
    AND (DATEDIFF(il.release_date, NOW())<=0)
    AND ((IFNULL(il.page_number,0) > p_page)
        OR (IFNULL(il.page_number,0) = p_page AND il.slot > p_slot)
        OR (IFNULL(il.page_number,0) = p_page
            AND il.slot = p_slot
            AND il.id > p_page_id)
        )
    ORDER BY IFNULL(il.page_number,999999), il.slot, il.id
    LIMIT 1;

END $$
