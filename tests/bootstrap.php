<?php
require_once(realpath(dirname(__FILE__)) . "/../keys/littledamien/mysql.php");

define('APP_BASE_DIR', realpath(dirname(__FILE__)).'/../');
const SHARED_CMS_TEMPLATE_DIR = APP_BASE_DIR.'../littled_cms/templates/';
const LITTLED_TEMPLATE_DIR = SHARED_CMS_TEMPLATE_DIR;
const TEST_ASSETS_PATH = APP_BASE_DIR.'tests/assets/';
const TEST_HARNESS_BASE_URI = 'http://localhost';
