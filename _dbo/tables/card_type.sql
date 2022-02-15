CREATE OR REPLACE TABLE card_type
(
    id          INT             AUTO_INCREMENT  PRIMARY KEY,
    name        VARCHAR(50)     NOT NULL,
    enabled     TINYINT(1)      NOT NULL
)
    ENGINE=InnoDB
    charset = latin1;
