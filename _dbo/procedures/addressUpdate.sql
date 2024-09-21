DELIMITER $$
CREATE OR REPLACE PROCEDURE `addressUpdate`(
    INOUT p_address_id INT,
    IN p_salutation VARCHAR(6),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_address1 VARCHAR(200),
    IN p_address2 VARCHAR(200),
    IN p_city VARCHAR(100),
    IN p_state_id INT,
    IN p_non_us_state VARCHAR(100),
    IN p_zip VARCHAR(20),
    IN p_country VARCHAR(100),
    IN p_home_phone VARCHAR(20),
    IN p_work_phone VARCHAR(20),
    IN p_fax VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_company VARCHAR(100),
    IN p_title VARCHAR(50),
    IN p_location VARCHAR(200),
    IN p_url VARCHAR(255),
    IN p_latitude FLOAT,
    IN p_longitude FLOAT
)
BEGIN

INSERT INTO `address` (
    `id`,
    `salutation`,
    `first_name`,
    `last_name`,
    `address1`,
    `address2`,
    `city`,
    `state_id`,
    `non_us_state`,
    `zip`,
    `country`,
    `home_phone`,
    `work_phone`,
    `fax`,
    `email`,
    `organization`,
    `title`,
    `location`,
    `url`,
    `latitude`,
    `longitude`
) VALUES (
     p_address_id,
     p_salutation,
     p_first_name,
     p_last_name,
     p_address1,
     p_address2,
     p_city,
     p_state_id,
     p_non_us_state,
     p_zip,
     p_country,
     p_home_phone,
     p_work_phone,
     p_fax,
     p_email,
     p_company,
     p_title,
     p_location,
     p_url,
     p_latitude,
     p_longitude
)
ON DUPLICATE KEY UPDATE
    id = VALUE(id),
    salutation = VALUE(salutation),
    first_name = VALUE(first_name),
    last_name = VALUE(last_name),
    address1 = VALUE(address1),
    address2 = VALUE(address2),
    city = VALUE(city),
    state_id = VALUE(state_id),
    non_us_state = VALUE(non_us_state),
    zip = VALUE(zip),
    country = VALUE(country),
    home_phone = VALUE(home_phone),
    work_phone = VALUE(work_phone),
    fax = VALUE(fax),
    email = VALUE(email),
    organization = VALUE(organization),
    title = VALUE(title),
    location = VALUE(location),
    url = VALUE(url),
    latitude = VALUE(latitude),
    longitude = VALUE(longitude);

IF p_address_id IS NULL THEN
    SELECT LAST_INSERT_ID() INTO p_address_id;
END IF;

END $$
