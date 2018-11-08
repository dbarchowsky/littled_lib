DROP PROCEDURE IF EXISTS `galleryFilteredSelect`;
DELIMITER $$
CREATE PROCEDURE `galleryFilteredSelect`(
  IN p_page INT,
  IN p_page_length INT,
  IN p_type_id INT,
  IN p_parent_id INT,
  IN p_title VARCHAR(50),
  IN p_release_date_after DATE,
  IN p_release_date_before DATE,
  IN p_access VARCHAR(20),
  IN p_slot INT,
  IN p_keyword VARCHAR(50),
  OUT total_matches INT
)
BEGIN

CALL udfCalcPageLimits(p_page, p_page_length, @offset, @size);

SET @type_filter = p_type_id;
SET @parent_filter = p_parent_id;
SET @title_filter = CASE IFNULL(p_title,'') WHEN '' THEN '' ELSE CONCAT('%',p_title,'%') END;
SET @release_date_after = p_release_date_after;
SET @release_date_before = p_release_date_before;
SET @access_filter = p_access;
SET @slot_filter = p_slot;
SET @keyword_filter = p_keyword;

  PREPARE STMT FROM
  'SELECT SQL_CALC_FOUND_ROWS
    il.id,
    il.title,
    il.description,
    full.path full_path,
    full.width full_width,
    full.height full_height,
    med.path med_path,
    med.width med_width,
    med.height med_height,
    mini.path mini_path,
    mini.width mini_width,
    mini.height mini_height,
    il.page_number,
    il.access,
    il.release_date,
    DATE_FORMAT(il.release_date, ''%c/%d/%Y'') display_date,
    il.id tn_id
  FROM image_link il
  INNER JOIN images full ON il.fullres_id = full.id
  LEFT JOIN images med ON il.med_id = med.id
  LEFT JOIN images mini ON il.mini_id = mini.id
  WHERE ((NULLIF(?,0) IS NULL) OR (il.type_id = ?))
  AND ((NULLIF(?,0) IS NULL) OR (il.parent_id = ?))
  AND ((NULLIF(?,'''') IS NULL) OR ((DATEDIFF(il.release_date,?)>=0)))
  AND ((NULLIF(?,'''') IS NULL) OR ((DATEDIFF(il.release_date,?)<=0)))
  AND ((NULLIF(?,'''') IS NULL) OR (il.title LIKE ?))
  AND ((NULLIF(?,'''') IS NULL) OR (il.access = ?))
  AND ((NULLIF(?,0) IS NULL) OR (il.slot = ?))
  AND (
    (NULLIF(?,'''') IS NULL)
    OR (MATCH(il.title,il.description,il.keywords) AGAINST (? IN BOOLEAN MODE))
  )
  ORDER BY il.slot, il.id
  LIMIT ?,?';
  EXECUTE STMT USING
    @type_filter,
    @type_filter,
    @parent_filter,
    @parent_filter,
    @release_date_after,
    @release_date_after,
    @release_date_before,
    @release_date_before,
    @title_filter,
    @title_filter,
    @access_filter,
    @access_filter,
    @slot_filter,
    @slot_filter,
    @keyword_filter,
    @keyword_filter,
    @offset,
    @size;
  DEALLOCATE PREPARE STMT;

  SELECT FOUND_ROWS() INTO total_matches;

END$$
DELIMITER ;
