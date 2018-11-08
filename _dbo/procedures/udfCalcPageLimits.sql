DROP PROCEDURE IF EXISTS `udfCalcPageLimits`;
DELIMITER $$
CREATE PROCEDURE `udfCalcPageLimits`(
  IN p_page INT,
  IN p_page_length INT,
  OUT p_offset INT,
  OUT p_limit INT
)
  BEGIN

    IF p_page > 0 AND p_page_length > 0 THEN
      SELECT ((p_page-1) * p_page_length) INTO p_offset;
      SELECT p_page_length INTO p_limit;
    ELSE
      SELECT 0 INTO p_offset;
      SELECT 2147483647 INTO p_limit;
    END if;

  END $$

DELIMITER ;