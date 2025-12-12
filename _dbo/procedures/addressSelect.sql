DELIMITER $$

CREATE OR REPLACE PROCEDURE `addressSelect`(
    IN p_address_id INT
)
BEGIN

    SELECT
        a.id,
        a.salutation,
        a.first_name,
        a.last_name,
        a.address1,
        a.address2,
        a.city,
        a.state_id,
        s.name as `state_name`,
        s.abbrev as `state_abbrev`,
        a.non_us_state,
        a.zip,
        a.country,
        a.home_phone,
        a.work_phone,
        a.fax,
        a.email,
        a.organization,
        a.title,
        a.location,
        a.url,
        a.latitude,
        a.longitude,
        a.notes
    FROM `address` a
    INNER JOIN `states` s on a.state_id = s.id
    WHERE a.id = p_address_id
    ORDER BY a.last_name, a.first_name, a.id;

END $$
