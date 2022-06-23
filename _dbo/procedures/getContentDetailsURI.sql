DELIMITER $$

CREATE OR REPLACE PROCEDURE `getContentDetailsURI`(
    IN p_section_id INT
)
BEGIN

SET @section_id = p_section_id;

    PREPARE STMT FROM
        'SELECT details_uri
        FROM section_operations
        WHERE section_id = ?';

    EXECUTE STMT USING
        @section_id;
    DEALLOCATE PREPARE STMT;

END $$
