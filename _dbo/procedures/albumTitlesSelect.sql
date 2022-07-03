DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumTitlesSelect`(
  IN p_page INT,
  IN p_page_length INT,
  IN p_keyword VARCHAR(120),
  IN p_content_type INT,
  OUT total_matches INT
)
BEGIN
  IF p_page > 0 AND p_page_length > 0 THEN
    set @offset = ((p_page -1) * p_page_length);
    set @size = p_page_length;
  ELSE
    set @offset = 0;
    set @size = 2147483647;
  END if;
  set @keyword_filter = null;
  if ifnull(p_keyword,'') <> '' then
    SET @keyword_filter = CONCAT(p_keyword, '*');
  end if;
  set @content_type_id = p_content_type;
  
  PREPARE STMT FROM
    'SELECT SQL_CALC_FOUND_ROWS 
      a.id, 
      a.title 
      FROM `album` a 
      WHERE a.section_id = ? 
      AND ((? IS NULL) OR (MATCH(a.title, a.description, a.keywords) AGAINST (? IN BOOLEAN MODE)))
      ORDER BY a.title ASC 
      LIMIT ?, ?';
  EXECUTE STMT USING 
    @content_type_id, 
    @keyword_filter, 
    @keyword_filter, 
    @offset, 
    @size;
  DEALLOCATE PREPARE STMT;
  SELECT FOUND_ROWS() INTO total_matches;

END $$

DELIMITER ;
