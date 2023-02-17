CREATE OR REPLACE table image_sizes
(
    id                  INT                 AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(50)         NOT NULL,
    CONSTRAINT          idx_image_sizes_name
        UNIQUE (name)
);

INSERT INTO image_sizes (name) VALUES ('full');
INSERT INTO image_sizes (name) VALUES ('medium');
INSERT INTO image_sizes (name) VALUES ('mini');