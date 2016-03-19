<?php
namespace Littled\Database;

use Littled\App\AppBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;


/**
 * Class MySQLConnection
 * @package Littled\Database
 */
class MySQLConnection extends AppBase
{
	/** @var \mysqli MySQLi connection to server. */
	protected $mysqli;

	/**
	 * MySQLConnection constructor.
	 */
	function __construct()
	{

	}

	/**
	 * Closes mysqli connection.
	 */
	public function closeDatabaseConnection()
	{
		if (is_object($this->mysqli))
		{
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}

	/**
	 * Opens MySQLi connection. Stores connection as $mysqli property of the class.
	 * @throws \Exception On database connection properties not defined.
	 */
	public function connectToDatabase()
	{
		if (!is_object($this->mysqli)) {
			$c = MySQLConnection::getConnectionSettings();
			$this->mysqli = new \mysqli($c->host, $c->user, $c->password, $c->schema);
		}
	}

	/**
	 * Returns records from database query.
	 * @param string $query SQL query to execute
	 * @return array Array of generic objects holding the data returned by the query.
	 * @throws InvalidQueryException
	 */
	public function fetchRecords($query)
	{
		$this->query($query);
		$rs = array();
		do {
			$result = $this->mysqli->store_result();
			if ($result) {
				while($row = $result->fetch_object()) {
					array_push($rs, $row);
				}
				$result->free();
			}
		} while($this->mysqli->more_results() && $this->mysqli->next_result());
		return ($rs);
	}

	/**
	 * Retrieves the value of a constant.
	 * @param string $setting Name of the constant holding the setting value.
	 * @return mixed
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAppSetting($setting)
	{
		if (!defined($setting)) {
			throw new ConfigurationUndefinedException("{$setting} not found in app settings.");
		}
		return (constant($setting));
	}

	/**
	 * Returns a generic object with database settings. If no settings are passed in
	 * it will use default app settings.
	 * @param string $host Database host. Empty string to use app settings.
	 * @param string $user Database user. Empty string to use app settings.
	 * @param string $password Database password. Empty string to use app settings.
	 * @param string $schema Database schema. Empty string to use app settings.
	 * @return object Generic object with database settings as its properties.
	 * @throws ConfigurationUndefinedException
	 */
	protected static function getConnectionSettings($host='', $user='', $password='', $schema='')
	{
		/** @todo Read database connection properties from file outside of web root. */

		if (!$host) {
			$host = self::getAppSetting('MYSQL_HOST');
		}
		if (!$user) {
			$user = self::getAppSetting('MYSQL_USER');
		}
		if (!$password) {
			$password = self::getAppSetting('MYSQL_PASS');
		}
		if (!$schema) {
			$schema = self::getAppSetting('MYSQL_SCHEMA');
		}
		return ((object)array(
			'host' => $host,
			'user' => $user,
			'password' => $password,
			'schema' => $schema
		));
	}

	/**
	 * Returns a MySQLi object using either default connection settings, or settings passed in.
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $schema
	 * @return \mysqli
	 * @throws ConfigurationUndefinedException
	 */
	public static function getMysqli($host='', $user='', $password='', $schema='')
	{
		$c = MySQLConnection::getConnectionSettings($host, $user, $password, $schema);
		return(new \mysqli($c->host, $c->user, $c->password, $c->schema));
	}

	/**
	 * Executes SQL statement
	 * @param string $query SQL statement to execute.
	 * @throws InvalidQueryException
	 */
	public function query($query)
	{
		if (!$this->mysqli instanceof \mysqli) {
			$this->connectToDatabase();
		}
		if (!$this->mysqli->multi_query($query)) {
			throw new InvalidQueryException($this->mysqli->error);
		}
	}
}