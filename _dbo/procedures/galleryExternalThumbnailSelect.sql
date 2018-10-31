DROP PROCEDURE IF EXISTS `galleryExternalThumbnailSelect`;
DELIMITER $$
CREATE PROCEDURE `galleryExternalThumbnailSelect`(
  IN p_parent_id INT,
  IN p_content_type_id INT
)
  BEGIN

    SET @thumbnail_id = NULL;

    /**
     * First try retrieving a thumbnail from the image_link records linked to a particular
     * content record. The thumbnail will be the image_link record most recently linked to the parent content record.
     */
    SELECT `id`
        INTO @thumbnail_id
    FROM `image_link`
    WHERE (`parent_id` = p_parent_id)
      AND (`type_id` = p_content_type_id)
    ORDER BY `id` DESC
    LIMIT 1;

    IF @thumbnail_id IS NULL THEN

      /**
       * Thumbnail was not available from image_link records. Check the parent content record to see
       * if it has a link to a thumbnail image embedded in it.
       */
      SET @parent_id = p_parent_id;
      SET @table = NULL;

      /** Get the table name of the parent record from its content type. */
      SELECT `table` INTO @table
      FROM site_section
      WHERE id = p_content_type_id;

      IF @table IS NOT NULL THEN

        SET @matches = 0;

        /** Make sure that a thumbnail field is part of the parent record. */
        SELECT COUNT(1) INTO @matches
        FROM information_schema.columns
        WHERE table_schema = 'chicot_littledcom'
          AND table_name = @table
          AND column_name = 'tn_id';

        IF @matches > 0 THEN

          /** Select the thumbnail id directly from the parent record. */
          set @stmt = CONCAT('SELECT `tn_id` INTO @thumbnail_id FROM `', @table, '` WHERE `id` = ', p_parent_id);
          PREPARE STMT FROM @stmt;
          EXECUTE STMT;
          DEALLOCATE PREPARE STMT;

        END IF;

      END IF;

    END IF;

    SELECT @thumbnail_id as `thumbnail_id`;

  END$$

DELIMITER ;
