CREATE TABLE `test_table`
(
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `name`      VARCHAR(50) NOT NULL,
    `int_col`   INT NULL,
    `bool_col`  BOOL NULL,
    `date`      DATETIME NULL,
    `slot`      INT NULL
);

CREATE TABLE `test_status`
(
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `name`      VARCHAR(50) NOT NULL UNIQUE
);

ALTER TABLE `test_table`
ADD `status_id` INT NULL AFTER `date`;

ALTER TABLE `test_table`
ADD CONSTRAINT `fk_test_table_status` FOREIGN KEY (status_id) REFERENCES test_status(id) ON DELETE SET NULL;

INSERT INTO test_status
    (id, name)
values
(1, 'new'),
(2, 'pending'),
(3, 'updated'),
(4, 'staged'),
(5, 'approved'),
(6, 'disabled'),
(7, 'archived');

# select * from test_table order by id;
update test_table set status_id = 1 where id > 2210 and id < 2217;
update test_table set status_id = 3 where id =  2624;
update test_table set status_id = 5 where id in (2583, 15883, 3025);