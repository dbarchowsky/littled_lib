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

ALTER TABLE section_operations ADD ajax_listings_uri varchar(255) NULL AFTER `listings_uri`;

SELECT CONCAT(
               'UPDATE `section_operations` SET `ajax_listings_uri` = ''',
               ajax_listings_uri,
               ''' WHERE `id` = ',
               CAST(id AS INTEGER),
               ';'
           ) as  `query`
from section_operations
where nullif(ajax_listings_uri,'') is not null;

ALTER TABLE section_operations ADD listings_template varchar(255) NULL AFTER `keywords_uri`;
ALTER TABLE section_operations ADD keywords_template varchar(255) NULL AFTER `listings_template`;

ALTER TABLE section_operations DROP COLUMN IF EXISTS listings_template;
ALTER TABLE section_operations DROP COLUMN IF EXISTS keywords_template;

ALTER TABLE section_operations CHANGE COLUMN id_param id_key VARCHAR(20) NOT NULL DEFAULT '';
