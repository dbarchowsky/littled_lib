DELIMITER $$

CREATE OR REPLACE PROCEDURE `imageLinkDelete`(
    IN p_id INT
)
BEGIN

  DELETE FROM `image_link` WHERE `id` = p_id;

END $$
