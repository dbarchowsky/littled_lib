DELIMITER $$

CREATE OR REPLACE PROCEDURE `contentRouteListingsSelect`(
    IN      p_page              INT,
    IN      p_page_length       INT,
    IN      p_site_section_id   INT,
    IN      p_operation         VARCHAR(45),
    IN      p_route             VARCHAR(200),
    IN      p_api_route         VARCHAR(200),
    OUT     total_matches       INT
)
BEGIN

    SET @section_filter = p_site_section_id;
    SET @operation_filter = p_operation;
    SET @route_filter = p_route;
    SET @api_route_filter = p_api_route;
    CALL udfCalcPageLimits(p_page, p_page_length, @offset, @limit);

    PREPARE STMT FROM
        'SELECT SQL_CALC_FOUND_ROWS
            ss.`name` as content_label,
            r.`operation`,
            r.`route`,
            r.`api_route`,
            r.`wildcard`,
            r.site_section_id,
            r.`id`
        FROM `content_route` r
        INNER JOIN site_section ss on r.site_section_id = ss.id
        WHERE ((? IS NULL OR r.site_section_id = ?))
        AND (NULLIF(?, '''') IS NULL OR r.`operation` LIKE ?)
        AND (NULLIF(?, '''') IS NULL OR r.`route` LIKE ?)
        AND (NULLIF(?, '''') IS NULL OR r.`api_route` LIKE ?)
        ORDER BY ss.`name`, r.`operation`
        LIMIT ?, ?';

    EXECUTE STMT USING
        @section_filter, @section_filter,
        @operation_filter, @operation_filter,
        @route_filter, @route_filter,
        @api_route_filter, @api_route_filter,
        @offset, @limit;

    DEALLOCATE PREPARE STMT;

    SELECT FOUND_ROWS() INTO total_matches;

END $$
