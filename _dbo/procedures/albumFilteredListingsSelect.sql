DELIMITER $$

CREATE OR REPLACE PROCEDURE `albumFilteredListingsSelect`(
  IN p_page INT,
  IN p_page_length INT,
  IN p_album_id INT,
  IN p_content_type_id INT,
  IN p_gallery_content_type_id INT,
  IN p_title_filter VARCHAR(50),
  IN p_date_filter VARCHAR(50),
  IN p_release_date_after DATE,
  IN p_release_date_before DATE,
  IN p_access_filter VARCHAR(20),
  IN p_slot_filter INT,
  IN p_keyword_filter VARCHAR(50),
  OUT total_matches INT
)
  BEGIN

    SET @album_id = p_album_id;
    SET @content_type_id = p_content_type_id;
    SET @gallery_content_type_id = p_gallery_content_type_id;
    SET @title_filter = p_title_filter;
    SET @date_filter = p_date_filter;
    SET @released_after_filter = p_release_date_after;
    SET @released_before_filter = p_release_date_before;
    SET @access_filter = p_access_filter;
    SET @slot_filter = p_slot_filter;
    SET @keyword_filter = p_keyword_filter;
    CALL udfCalcPageLimits(p_page, p_page_length, @offset, @limit);

    PREPARE STMT FROM
      'SELECT SQL_CALC_FOUND_ROWS
        a.`id`,
        a.`title`,
        a.`slug`,
        a.`description`,
        a.`date`,
        a.`slot`,
        (
          SELECT COUNT(*)
          FROM `image_link` pub
          WHERE (pub.`parent_id` = a.`id`)
          AND (pub.`type_id` = ?)
        ) AS `private_pages`,
        (
          SELECT COUNT(*)
          FROM `image_link` pub
          WHERE (pub.`parent_id` = a.`id`)
          AND (pub.`type_id` = ?)
          AND (pub.`access` LIKE ''public'')
        ) AS `public_pages`,
        DATE_FORMAT(a.`release_date`, ''%m/%d/%Y'') AS `release_date`,
        a.`access`,
        a.`layout`,
        a.`tn_id`,
        IFNULL(mini.`path`, med.`path`) AS `tn_path`,
        IFNULL(mini.`width`, med.`width`) AS `tn_width`,
        IFNULL(mini.`height`, med.`height`) AS `tn_height`,
        mini.`path` AS `mini_path`,
        mini.`width` AS `mini_width`,
        mini.`height` AS `mini_height`,
        med.`path` AS `med_path`,
        med.`width` AS `med_width`,
        med.`height` AS `med_height`,
        full.`path` AS `full_path`,
        full.`width` AS `full_width`,
        full.`height` AS `full_height`,
        a.tn_id
      FROM `album` a
      LEFT JOIN
        (
          `image_link` il
          INNER JOIN `images` full ON il.`fullres_id` = full.`id`
          LEFT JOIN `images` med ON il.`med_id` = med.`id`
          LEFT JOIN `images` mini ON il.`mini_id` = mini.`id`
        ) ON (a.`tn_id` = il.`id`)
      WHERE (a.`section_id` = ?)
      AND (NULLIF(?, 0) IS NULL OR a.`id` = ?)
      AND (NULLIF(?,'''') IS NULL OR a.`title` LIKE ?)
      AND (NULLIF(?,'''') IS NULL OR a.`date` LIKE ?)
      AND (NULLIF(?,'''') IS NULL OR DATEDIFF(a.`release_date`, ?) >= 0)
      AND (NULLIF(?,'''') IS NULL OR DATEDIFF(a.`release_date`, ?) <= 0)
      AND (NULLIF(?,'''') IS NULL OR a.`access` = ?)
      AND (NULLIF(?, 0) IS NULL OR a.`slot` = ?)
      AND (NULLIF(?,'''') IS NULL OR (MATCH(a.`title`, a.`description`, a.`keywords`) AGAINST (? IN BOOLEAN MODE)))
      ORDER BY IFNULL(a.`slot`,999999), IFNULL(a.`release_date`,''1980-01-01'') DESC, a.`id` DESC
      LIMIT ?, ?';

    EXECUTE STMT USING
      @gallery_content_type_id,
      @gallery_content_type_id,
      @content_type_id,
      @album_id, @album_id,
      @title_filter, @title_filter,
      @date_filter, @date_filter,
      @released_after_filter, @released_after_filter,
      @released_before_filter, @released_before_filter,
      @access_filter, @access_filter,
      @slot_filter, @slot_filter,
      @keyword_filter, @keyword_filter,
      @offset, @limit;

    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

END $$

DELIMITER ;
