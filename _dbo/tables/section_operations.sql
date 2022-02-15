CREATE OR REPLACE TABLE section_operations
(
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    section_id   INT            NOT NULL,
    label        VARCHAR(50)    NULL,
    id_param     VARCHAR(20)    NULL,
    listings_uri VARCHAR(200)   NULL,
    details_uri  VARCHAR(200)   NULL,
    edit_uri     VARCHAR(200)   NULL,
    upload_uri   VARCHAR(200)   NULL,
    delete_uri   VARCHAR(200)   NULL,
    cache_uri    VARCHAR(200)   NULL,
    sorting_uri  VARCHAR(200)   NULL,
    keywords_uri VARCHAR(200)   NULL,
    comments     TEXT           NULL,
    is_sortable  TINYINT(1)     NULL,
    CONSTRAINT fk_section_operations_site_section
        FOREIGN KEY (section_id) REFERENCES site_section (id)
            ON DELETE CASCADE
)
    ENGINE=InnoDB
    CHARSET=latin1;