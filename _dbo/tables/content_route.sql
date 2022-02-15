CREATE OR REPLACE TABLE content_route
(
    id                  INT             AUTO_INCREMENT PRIMARY KEY,
    site_section_id     INT             NOT NULL,
    operation           VARCHAR(45)     NOT NULL,
    url                 VARCHAR(255)    NOT NULL,
    CONSTRAINT idx_content_route_site_section_id_name
        UNIQUE (site_section_id, operation),
    CONSTRAINT fk_content_route_site_section
        FOREIGN KEY (site_section_id) REFERENCES site_section(id) ON DELETE CASCADE
);
