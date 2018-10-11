DROP PROCEDURE IF EXISTS `socialGalleryFilteredSelect`;
DELIMITER $$
CREATE PROCEDURE `socialGalleryFilteredSelect`(
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
  IN p_on_wordpress BOOL,
  IN p_on_twitter BOOL,
  IN p_has_short_url BOOL,
  OUT total_matches INT
)
  BEGIN

    IF p_page > 0 AND p_page_length > 0 THEN
      set @offset = ((p_page-1) * p_page_length);
      set @size = p_page_length;
    ELSE
      set @offset = 0;
      set @size = 2147483647;
    END if;

    SET @type_filter = p_type_id;
    SET @parent_filter = p_parent_id;
    SET @title_filter = CASE IFNULL(p_title,'') WHEN '' THEN '' ELSE CONCAT('%',p_title,'%') END;
    SET @release_date_after = p_release_date_after;
    SET @release_date_before = p_release_date_before;
    SET @access_filter = p_access;
    SET @slot_filter = p_slot;
    SET @keyword_filter = p_keyword;
    SET @on_wordpress = p_on_wordpress;
    SET @on_twitter = p_on_twitter;
    SET @has_short_url = p_has_short_url;

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
         AND ((? IS NULL)
                OR ((?=1) AND (il.wp_id>0))
                OR ((?=0) AND (il.wp_id IS NULL OR il.wp_id = 0)))
         AND ((? IS NULL)
                OR ((?=1) AND (il.twitter_id>0))
                OR ((?=0) AND (il.twitter_id IS NULL OR il.twitter_id = 0)))
         AND ((? IS NULL)
                OR ((?=1) AND (LENGTH(il.short_url)>0))
                OR ((?=0) AND (il.short_url IS NULL OR il.short_url = '''')))
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
      @on_wordpress,
      @on_wordpress,
      @on_wordpress,
      @on_twitter,
      @on_twitter,
      @on_twitter,
      @has_short_url,
      @has_short_url,
      @has_short_url,
      @keyword_filter,
      @keyword_filter,
      @offset,
      @size;
    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

  END$$
DELIMITER ;
