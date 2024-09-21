CREATE OR REPLACE TABLE cards
(
    id              INT                    AUTO_INCREMENT PRIMARY KEY,
    holders_name    VARCHAR(100)           NULL,
    type_id         INT                    NOT NULL,
    card_no         VARCHAR(128)           NOT NULL,
    sec_code        VARCHAR(128)           NULL,
    expr_mon        INT                    NOT NULL,
    expr_yr         INT                    NOT NULL,
    CONSTRAINT `fk_cards_card_type`
        FOREIGN KEY (`type_id`) REFERENCES `card_type` (id)
            ON DELETE RESTRICT
)
    CHARSET = latin1;
