DELIMITER $$

CREATE OR REPLACE PROCEDURE `testStatusOptionsSelect`( )
BEGIN

    SELECT
         id,
         name as `option`
    FROM test_status
    ORDER BY `name`;

END $$
