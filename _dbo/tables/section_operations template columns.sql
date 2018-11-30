alter table section_operations
  add listings_template varchar(255) default null after keywords_uri;

alter table section_operations
  add keywords_template varchar(255) default null after listings_template;
