DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumNextPublicPageSetSelect`(
    IN p_parent_id INT,
    IN p_content_type_id INT,
    IN p_page_id INT,
    IN p_page_number INT,
    IN p_slot INT,
    IN p_limit INT
)
BEGIN

    SELECT
        il.id,
        il.title,
        il.description,
        il.slot,
        il.page_number,
        f.path full_path,
        f.width full_width,
        f.height full_height
    FROM image_link il
    INNER JOIN images f ON il.fullres_id = f.id
    WHERE (il.parent_id = p_parent_id)
    AND (il.type_id = p_content_type_id)
    AND (il.access = 'public')
    AND (DATEDIFF(il.`release_date`, NOW())<=0)
    AND (
        (il.id = p_page_id)
        OR (IFNULL(il.page_number,0) > p_page_number)
        OR (IFNULL(il.page_number,0) = p_page_number AND il.slot > p_slot)
        OR (IFNULL(il.page_number,0) = p_page_number AND il.slot = p_slot AND il.id > p_page_id)
    )
    ORDER BY IFNULL(il.page_number,999999), il.slot, il.id
    LIMIT p_limit;

END $$
