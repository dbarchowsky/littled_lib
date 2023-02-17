CREATE OR REPLACE TABLE image_formats
(
    id                  INT                                     AUTO_INCREMENT PRIMARY KEY,
    site_section_id     INT                                     NOT NULL,
    size_id             INT                                     NOT NULL,
    label               VARCHAR(50)                             DEFAULT '',
    width               INT                                     NULL,
    height              INT                                     NULL,
    format              ENUM ('', 'gif', 'jpeg', 'png', 'webp')  NULL,
    key_prefix          VARCHAR(16)                             DEFAULT '',
    path                VARCHAR(255)                            DEFAULT '',
    CONSTRAINT          idx_image_formats_section_label_size
        UNIQUE (site_section_id, label, size_id),
    CONSTRAINT fk_image_formats_site_section
        FOREIGN KEY (site_section_id) REFERENCES site_section(id)
            ON DELETE CASCADE,
    CONSTRAINT fk_image_formats_image_sizes
        FOREIGN KEY (size_id) REFERENCES image_sizes(id)
            ON DELETE CASCADE
);

select id into @size_id from image_sizes where name = 'full';
INSERT INTO image_formats
(
    site_section_id,
    size_id,
    label,
    width,
    height,
    `format`,
    key_prefix,
    `path`
) SELECT
    id,
    @size_id,
    image_label,
    width,
    height,
    REPLACE(`format`, 'jpg', 'jpeg'),
    param_prefix,
    image_path
FROM site_section
WHERE not isnull(width)
   or not isnull(height)
   or ifnull(image_label,'') <> ''
   or ifnull(format,'') <> ''
   or ifnull(param_prefix, '') <> '';

select id into @size_id from image_sizes where name = 'medium';
INSERT INTO image_formats
(
    site_section_id,
    size_id,
    label,
    width,
    height,
    `format`,
    key_prefix,
    `path`
) SELECT
      id,
      @size_id,
      image_label,
      med_width,
      med_height,
      REPLACE(`format`, 'jpg', 'jpeg'),
      param_prefix,
      CONCAT(image_path, 'med/')
FROM site_section
WHERE not isnull(med_width)
   or not isnull(med_height) ;

select id into @size_id from image_sizes where name = 'mini';
INSERT INTO image_formats
(
    site_section_id,
    size_id,
    label,
    width,
    height,
    `format`,
    key_prefix,
    `path`
) SELECT
      id,
      @size_id,
      image_label,
      mini_width,
      mini_height,
      REPLACE(`format`, 'jpg', 'jpeg'),
      param_prefix,
      CONCAT(image_path, 'tn/')
FROM site_section
WHERE not isnull(mini_width)
   or not isnull(mini_height) ;
