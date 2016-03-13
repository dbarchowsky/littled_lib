<?php
namespace Littled\Tests\Base;


class DatabaseTestCase extends \PHPUnit_Framework_TestCase
{
	protected $db_host;
	protected $db_schema;
	protected $db_user;
	protected $db_password;

	protected function loadConnectionVars()
	{
		/** @var string $phpunit_db_host */
		/** @var string $phpunit_db_schema */
		/** @var string $phpunit_db_user */
		/** @var string $phpunit_db_password */
		$home_dir = getenv('HOME');
		if ($home_dir===false) {
			throw new \Exception("Environment variable 'HOME' not found.");
		}
		$db_settings_path = $home_dir.".devconfig".DIRECTORY_SEPARATOR."phpunit_db_connection.php";
		if (!file_exists($db_settings_path)) {
			throw new \Exception("Database connection settings not found at {$db_settings_path}");
		}
		include ($db_settings_path);
		$this->db_host = $phpunit_db_host;
		$this->db_schema = $phpunit_db_schema;
		$this->db_user = $phpunit_db_user;
		$this->db_password = $phpunit_db_password;
		unset($php_db_host);
		unset($php_db_schema);
		unset($php_db_user);
		unset($php_db_password);
	}

	protected function unloadConnectionVars()
	{
		$this->db_host = '';
		$this->db_schema = '';
		$this->db_user = '';
		$this->db_password = '';
	}
}