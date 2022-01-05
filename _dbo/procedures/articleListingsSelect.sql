DROP PROCEDURE IF EXISTS `articleListingsSelect`;
DELIMITER $$
CREATE PROCEDURE `articleListingsSelect`(
    IN p_page INT,
    IN p_page_length INT,
    IN p_title_filter VARCHAR(50),
    IN p_text_filter VARCHAR(50),
    IN p_source_filter VARCHAR(50),
    IN p_published_after_date DATE,
    IN p_published_before_date DATE,
    IN p_keyword_filter VARCHAR(50),
    OUT total_matches INT
)
BEGIN

    SET @title_filter = p_title_filter;
    SET @text_filter = p_text_filter;
    SET @source_filter = p_source_filter;
    SET @published_after_filter = p_published_after_date;
    SET @published_before_filter = p_published_before_date;
    SET @keyword_filter = p_keyword_filter;
    CALL udfCalcPageLimits(p_page, p_page_length, @offset, @limit);

    PREPARE STMT FROM
        'SELECT SQL_CALC_FOUND_ROWS
             a.`id`,
             a.`title`,
             a.`text`,
             a.`author`,
             a.`source`,
             a.`source_url`,
             a.`date`,
             a.`caption`,
             a.`slot`,
             a.`enabled`,
             a.`keywords`
         FROM `article` a
         WHERE (NULLIF(?, '''') IS NULL OR a.`title` LIKE ?)
           AND (NULLIF(?,'''') IS NULL OR a.`text` LIKE ?)
           AND (NULLIF(?,'''') IS NULL OR a.`source` LIKE ?)
           AND (NULLIF(?,'''') IS NULL OR DATEDIFF(a.`date`, ?) >= 0)
           AND (NULLIF(?,'''') IS NULL OR DATEDIFF(a.`date`, ?) <= 0)
           AND (NULLIF(?,'''') IS NULL OR (MATCH(a.`title`,a.`text`,a.`keywords`) AGAINST (? IN BOOLEAN MODE)))
         ORDER BY IFNULL(a.`slot`,999999), IFNULL(a.`date`,''1980-01-01'') DESC, a.`id` DESC
         LIMIT ?, ?';

    EXECUTE STMT USING
        @title_filter, @title_filter,
        @text_filter, @text_filter,
        @source_filter, @source_filter,
        @published_after_filter, @published_after_filter,
        @published_before_filter, @published_before_filter,
        @keyword_filter, @keyword_filter,
        @offset, @limit;

    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

END $$

DELIMITER ;
