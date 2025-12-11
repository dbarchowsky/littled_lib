CREATE TABLE `site_user_access`
(
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `name`      VARCHAR(50) NOT NULL,
    `enabled`   BOOL DEFAULT TRUE
);

INSERT INTO `site_user_access` (`id`, `name`) VALUES
(1, 'unrestricted'),
(2, 'basic authentication'),
(3, 'admin authentication');