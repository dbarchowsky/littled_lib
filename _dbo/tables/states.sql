CREATE OR REPLACE TABLE `states`
(
    id          INT             AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)     NOT NULL,
    abbrev      CHAR(2)         NULL,
    sales_tax   FLOAT           NULL,
    charge_tax  BOOL            NULL DEFAULT FALSE
)
ENGINE=InnoDB
CHARSET=Latin1;

ALTER TABLE `states` ADD sales_tax FLOAT NULL;
ALTER TABLE `states` ADD charge_tax BOOL NULL DEFAULT FALSE;
