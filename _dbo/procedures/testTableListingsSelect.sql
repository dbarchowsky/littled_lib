DROP PROCEDURE IF EXISTS `testTableListingsSelect`;
DELIMITER $$
CREATE PROCEDURE `testTableListingsSelect`(
    IN p_offset INT,
    IN p_page_length INT,
    IN p_name_filter VARCHAR(50),
    IN p_int_filter INT,
    IN p_bool_filter BOOL,
    IN p_after_date DATE,
    IN p_before_date DATE,
    OUT total_matches INT
)
BEGIN

    SET @name_filter = p_name_filter;
    SET @int_filter = p_int_filter;
    SET @bool_filter = p_bool_filter;
    SET @after_filter = p_after_date;
    SET @before_filter = p_before_date;
    CALL udfValidatePageOffsets(p_offset, p_page_length, @offset, @limit);

    PREPARE STMT FROM
        'SELECT SQL_CALC_FOUND_ROWS
             t.`id`,
             t.`name`,
             t.`int_col`,
             t.`bool_col`,
             t.`date`,
             t.`slot`,
             (@row:=@row+1) as `index`
         FROM `test_table` t, (SELECT @row:=-1) r
         WHERE (NULLIF(?, '''') IS NULL OR t.`name` LIKE ?)
           AND (? IS NULL OR t.`int_col`=?)
           AND (? IS NULL OR t.`bool_col`=?)
           AND (NULLIF(?,'''') IS NULL OR DATEDIFF(t.`date`, ?) >= 0)
           AND (NULLIF(?,'''') IS NULL OR DATEDIFF(t.`date`, ?) <= 0)
         ORDER BY IFNULL(t.`slot`,999999), IFNULL(t.`date`,''1980-01-01'') DESC, t.`id` DESC
         LIMIT ?, ?';

    EXECUTE STMT USING
        @name_filter, @name_filter,
        @int_filter, @int_filter,
        @bool_filter, @bool_filter,
        @after_filter, @after_filter,
        @before_filter, @before_filter,
        @offset, @limit;

    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

END $$
