alter table section_operations
  add ajax_listings_uri varchar(255) default null after listings_uri;

update section_operations set ajax_listings_uri = listings_uri;
update section_operations set listings_uri = null; 