DELIMITER $$
CREATE OR REPLACE PROCEDURE `udfValidatePageOffsets`(
    IN      p_src_offset    INT,
    IN      p_src_limit     INT,
    OUT     p_offset        INT,
    OUT     p_limit         INT
)
BEGIN

    SELECT IFNULL(p_src_offset, 0) INTO p_offset;
    SELECT IFNULL(NULLIF(p_src_limit, 0), 2147483647) INTO p_limit;

END $$

DELIMITER ;
