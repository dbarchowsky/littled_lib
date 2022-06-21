DROP PROCEDURE IF EXISTS `testTableTitlesSelect`;
DELIMITER $$
CREATE PROCEDURE `testTableTitlesSelect`(
    IN p_page INT,
    IN p_page_length INT,
    IN p_name_filter VARCHAR(50),
    OUT total_matches INT
)
BEGIN

    SET @name_filter = p_name_filter;
    CALL udfCalcPageLimits(p_page, p_page_length, @offset, @limit);

    PREPARE STMT FROM
        'SELECT SQL_CALC_FOUND_ROWS
             t.`id`,
             t.`name` as `title`
         FROM `test_table` t, (SELECT @row:=-1) r
         WHERE (NULLIF(?, '''') IS NULL OR t.`name` LIKE CONCAT(''%'',?,''%''))
         ORDER BY IFNULL(t.`slot`,999999), IFNULL(t.`date`,''1980-01-01'') DESC, t.`id` DESC
         LIMIT ?, ?';

    EXECUTE STMT USING
        @name_filter, @name_filter,
        @offset, @limit;

    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

END $$
