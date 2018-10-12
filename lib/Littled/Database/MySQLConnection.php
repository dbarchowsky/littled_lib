<?php
namespace Littled\Database;

use Littled\App\AppBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;


/**
 * Class MySQLConnection
 * @package Littled\Database
 */
class MySQLConnection extends AppBase
{
	const DEFAULT_MYSQL_PORT = '3306';

	/** @var \mysqli MySQLi connection to server. */
	protected $mysqli;

	/**
	 * MySQLConnection constructor.
	 */
	function __construct()
	{
		$driver = new \mysqli_driver();
		$driver->report_mode = MYSQLI_REPORT_STRICT;
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
	 * Returns the latest connection error reported by mysqli.
	 * @return string Internal mysqli connection error string, or null if there are no errors.
	 */
	public function connectionError()
	{
		return ($this->mysqli->connect_error);
	}

	/**
	 * Form the array like this:
	 * <code>
	 * $c = array(
	 *     'host' => '',      // host address
	 *     'user' => '',      // user name
	 *     'password' => '',  // password
	 *     'schema' => '',    // schema
	 *     'port' => null     // port number as an int, if using non-default port number
	 * );
	 * </code>
	 * @param array[string]string $c Array of connection properties: {host, user}
	 */
	protected function connect($c)
	{
		if (preg_match('/^\d{1,3}\.\d{1,3}.\d{1,3}\.\d{1,3}$/', $c->host))
		{
			$this->mysqli = new \mysqli($c->host, $c->user, $c->password, $c->schema, $c->port);
		}
		else
		{
			if ($c->port)
			{
				$c->host .= ":{$c->port}";
			}
			$this->mysqli = new \mysqli($c->host, $c->user, $c->password, $c->schema);
		}
	}

	/**
	 * Opens MySQLi connection. Stores connection as $mysqli property of the class.
	 * @param string[optional] $host Name of MySQL host.
	 * @param string[optional] $user User name for connecting to MySQL server.
	 * @param string[optional] $password Password for connecting to MySQL server.
	 * @param string[optional] $schema Name of schema.
	 * @param string[optional] $port Port number of MySQL server if not using default.
	 * @throws \Exception On database connection properties not defined.
	 * @throws ConnectionException On connection error.
	 * @throws ConfigurationUndefinedException Database connection properties not set.
	 */
	public function connectToDatabase($host='', $user='', $password='', $schema='', $port='')
	{
		if (!is_object($this->mysqli))
		{
			try
			{
				$this->connect(MySQLConnection::getConnectionSettings($host, $user, $password, $schema, $port));
			}
			catch (\mysqli_sql_exception $ex)
			{
				throw new ConnectionException('Connection error: '.$ex->__toString());
			}
			$this->mysqli->set_charset('utf8');
		}
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mixed $value Value to escape.
	 * @return string Escaped value.
	 * @throws \Exception Error establishing database connection.
	 */
	public function escapeSQLValue($value)
	{
		$this->connectToDatabase();
		if ($value===null) {
			return ('null');
		}
		if ($value===true) {
			return ('1');
		}
		if ($value===false) {
			return ('0');
		}
		return "'".$this->mysqli->real_escape_string($value)."'";
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
		// do {
			$result = $this->mysqli->store_result();
			if ($result) {
				while($row = $result->fetch_object()) {
					array_push($rs, $row);
				}
				$result->free();
			}
		// } while($this->mysqli->more_results() && $this->mysqli->next_result());
		/** 
		 * Note that without the do...while loop there might be more results available
		 * in the mysqli object. Leave it to the calling routine to handle those results.  
		 */
		return ($rs);
	}

	/**
	 * Retrieves the value of a constant.
	 * @param string $setting Name of the constant holding the setting value.
	 * @param bool[optional] $required Specify if the setting is required or not. Defaults to TRUE.
	 * @return mixed
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAppSetting($setting, $required=true)
	{
		if (!defined($setting)) {
			if ($required===false) {
				return (null);
			}
			throw new ConfigurationUndefinedException("{$setting} not found in app settings.");
		}
		return (constant($setting));
	}

	/**
	 * Returns a generic object with database settings. If no settings are passed in
	 * it will use default app settings.
	 * @param string[optional] $host Database host. Empty string to use app settings.
	 * @param string[optional] $user Database user. Empty string to use app settings.
	 * @param string[optional] $password Database password. Empty string to use app settings.
	 * @param string[optional] $schema Database schema. Empty string to use app settings.
	 * @param string[optional] $port Database port. Empty string to use app settings.
	 * @return object Generic object with database settings as its properties.
	 * @throws ConfigurationUndefinedException
	 */
	protected static function getConnectionSettings($host='', $user='', $password='', $schema='', $port='')
	{
		return ((object)array(
			'host' => (($host)?($host):(self::getAppSetting('MYSQL_HOST'))),
			'user' => (($user)?($user):(self::getAppSetting('MYSQL_USER'))),
			'password' => (($password)?($password):(self::getAppSetting('MYSQL_PASS'))),
			'schema' => (($schema)?($schema):(self::getAppSetting('MYSQL_SCHEMA'))),
			'port' => (($port)?($port):(self::getAppSetting('MYSQL_PORT', false)))
		));
	}

	/**
	 * Returns a MySQLi object using either default connection settings, or settings passed in.
	 * @param string[optional] $host
	 * @param string[optional] $user
	 * @param string[optional] $password
	 * @param string[optional] $schema
	 * @param string[optional] $port
	 * @return \mysqli
	 * @throws ConfigurationUndefinedException
	 */
	public static function getMysqli($host='', $user='', $password='', $schema='', $port='')
	{
		$c = MySQLConnection::getConnectionSettings($host, $user, $password, $schema, $port);
		return(new \mysqli($c->host, $c->user, $c->password, $c->schema, $c->port));
	}

	/**
	 * Tests if the object currently has a viable database connection.
	 * @return bool Flag indicating if there is a viable database connection or not.
	 */
	public function hasConnection()
	{
		if ($this->mysqli instanceof \mysqli === false) {
			return (false);
		}
		return ($this->mysqli->connect_error === null);
	}

	/**
	 * Executes SQL statement
	 * @param string $query SQL statement to execute.
	 * @throws InvalidQueryException
	 * @throws \Exception Error connecting to database
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