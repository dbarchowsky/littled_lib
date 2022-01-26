create table states
(
    id     int auto_increment primary key,
    name   varchar(50) not null,
    abbrev char(2)     null
);

ALTER TABLE `states` ADD sales_tax FLOAT NULL;
ALTER TABLE `states` ADD charge_tax BOOL NULL DEFAULT FALSE;
