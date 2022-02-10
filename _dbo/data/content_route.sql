INSERT INTO site_section
(id, name, slug, root_dir, param_prefix, `table`, is_cached, gallery_thumbnail) VALUES
    (34, 'Content Route', 'content-route', '/sections/', 'cr', 'content_route', 0, 0);
INSERT INTO section_operations
(section_id, label, id_param, comments, is_sortable) VALUES
    (34, 'Content Route', 'crID', 'Content route properties.', 0);
