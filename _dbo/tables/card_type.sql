CREATE OR REPLACE TABLE card_type
(
    id          INT             AUTO_INCREMENT  PRIMARY KEY,
    name        VARCHAR(50)     NOT NULL,
    enabled     TINYINT(1)      NOT NULL
)
    ENGINE=InnoDB
    charset = latin1;

INSERT INTO card_type (id, name, enabled) VALUES (1, 'Visa', 1);
INSERT INTO card_type (id, name, enabled) VALUES (2, 'Mastercard', 1);
INSERT INTO card_type (id, name, enabled) VALUES (3, 'American Express', 1);
INSERT INTO card_type (id, name, enabled) VALUES (4, 'Discover', 1);
