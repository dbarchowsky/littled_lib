DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumFulltextKeywordsUpdate`(
    IN p_table VARCHAR(128),
    IN p_id INT,
    IN p_content_type_id INT,
    IN p_gallery_content_type_id INT
)
BEGIN

    /** Select the thumbnail id directly from the parent record. */
    set @stmt = CONCAT('UPDATE `', p_table, '` SET a.`keywords` = ',
        '(',
        'SELECT GROUP_CONCAT(DISTINCT k.`term` ORDER BY k.`term` SEPARATOR '' '') ',
        'FROM `keyword` k ',
        'LEFT JOIN `image_link` il ON (k.`parent_id` = il.`id` AND k.`type_id` = ', p_gallery_content_type_id ,') ',
        'WHERE (k.`parent_id`=', p_id, ' AND k.`type_id`=', p_content_type_id ,') ',
        'OR (il.`parent_id`=', p_id ,' AND il.`type_id`=', p_gallery_content_type_id, ') ',
        ')',
        'WHERE a.id = ', p_id);
    PREPARE STMT FROM @stmt;
    EXECUTE STMT;
    DEALLOCATE PREPARE STMT;

END $$
