<?php
namespace Littled\Database;

use Littled\App\AppBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Exception;
use mysqli;
use mysqli_driver;
use mysqli_sql_exception;
use mysqli_result;

/**
 * Class MySQLConnection
 * @package Littled\Database
 */
class MySQLConnection extends AppBase
{
	const DEFAULT_MYSQL_PORT = '3306';

	/** @var mysqli Connection to database server. */
	protected $mysqli;

    public function __construct()
    {
        parent::__construct();
        $driver = new mysqli_driver();
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
	 * Check if a column exists in a given database table.
	 * @param string $column_name name of the column to check for
	 * @param string $table_name name of the table to look in
	 * @return boolean True/false depending on if the column is found.
	 * @throws InvalidQueryException Error executing query.
     * @throws Exception
	 */
	public function columnExists( string $column_name, string $table_name ): bool
	{
		$data = $this->fetchRecords("SHOW COLUMNS FROM `$table_name` LIKE '$column_name'");
		$has_rows = (count($data) > 0);
		return ($has_rows);
	}

    /**
	 * Returns the latest connection error reported by mysqli.
	 * @return string Internal mysqli connection error string, or null if there are no errors.
	 */
	public function connectionError(): string
	{
		return ($this->mysqli->connect_error);
	}

	/**
	 * Make database connection
	 * @param DBConnectionSettings $c Database connection properties
	 */
	protected function connect(DBConnectionSettings $c)
	{
		if (preg_match('/^\d{1,3}\.\d{1,3}.\d{1,3}\.\d{1,3}$/', $c->host))
		{
			$this->mysqli = new mysqli($c->host, $c->user, $c->password, $c->schema, $c->port);
		}
		else
		{
			if ($c->port)
			{
				$c->host .= ":$c->port";
			}
			$this->mysqli = new mysqli($c->host, $c->user, $c->password, $c->schema);
		}
	}

	/**
	 * Opens MySQLi connection. Stores connection as $mysqli property of the class.
	 * Can be chained with other MySQLConnection methods.
	 * @return $this
	 * @param string $host Name of MySQL host.
	 * @param string $user Username for connecting to MySQL server.
	 * @param string $password Password for connecting to MySQL server.
	 * @param string $schema Name of schema.
	 * @param string $port Port number of MySQL server if not using default.
	 * @throws ConnectionException On connection error.
	 * @throws ConfigurationUndefinedException Database connection properties not set.
	 */
	public function connectToDatabase(string $host='', string $user='', string $password='', string $schema='', string $port=''): MySQLConnection
	{
		if (!is_object($this->mysqli)) {
			try {
				$this->connect(MySQLConnection::getConnectionSettings($host, $user, $password, $schema, $port));
			}
			catch (mysqli_sql_exception $ex) {
				throw new ConnectionException('Connection error: '.$ex->__toString());
			}
			$this->mysqli->set_charset('utf8');
		}
		return $this;
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mixed $value Value to escape.
	 * @return string Escaped value.
	 * @throws ConnectionException On connection error.
	 * @throws ConfigurationUndefinedException Database connection properties not set.
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
		if (is_numeric($value)) {
			return($value);
		}
		return "'".$this->mysqli->real_escape_string($value)."'";
	}

	/**
	 * Returns records from database query. This routine will eat up all result sets returned by
	 * the execution of the query. Use fetchRecordsNonExhaustive() to return only the first result.
	 * @param string $query SQL query to execute
     * @param string $types
     * @param &...$vars
	 * @return array Array of generic objects holding the data returned by the query.
	 * @throws Exception
	 */
	public function fetchRecords(string $query, string $types='', &...$vars): array
	{
        // $result = $this->fetchResult($query, $types, $vars);
        if ($types) {
            array_unshift($vars, $query, $types);
            $result = call_user_func_array([$this, 'fetchResult'], $vars);
        }
        else {
            $result = $this->fetchResult($query);
        }
		$rs = array();
        while($row = $result->fetch_object()) {
            $rs[] = $row;
        }
        $result->free();
		return ($rs);
	}

    /**
     * Returns mysqli_result object containing data matching query.
     * @param string $query
     * @param string $types
     * @param &...$vars
     * @return mysqli_result
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
    public function fetchResult(string $query, string $types='', &...$vars): mysqli_result
    {
        $this->connectToDatabase();
        if ($types) {
            $stmt = $this->mysqli->prepare($query);
            if (!$stmt) {
                throw new Exception('Could not prepare statement: '.$this->mysqli->error);
            }
            array_unshift($vars, $types);
            call_user_func_array([$stmt, 'bind_param'], $vars);
            if(!$stmt->execute()) {
                throw new Exception('Error fetching records: '.$stmt->error);
            }
            $result = $stmt->get_result();
            $stmt->close();
        }
        else {
            $result = $this->mysqli->query($query);
            if(!$result) {
                throw new Exception('Error fetching records: '.$this->mysqli->error);
            }
        }
        return $result;
    }

	/**
	 * @deprecated Use MySQLConnection::fetchRecords() instead.
	 * @param string $query Query to execute.
	 * @return array Data returned from query as an array.
	 * @throws InvalidQueryException|Exception Error executing query.
	 */
	public function fetchRecordsNonExhaustive(string $query): array
	{
		$this->query($query);

		/*
		 * Normally, this would be wrapped in a do...while statement to ensure that all results are retrieved,
		 * but here we only want the first result.
		 */
		$rs = array();
		$result = $this->mysqli->store_result();
		if ($result) {
			while($row = $result->fetch_object()) {
				$rs[] = $row;
			}
			$result->free();
		}
		return($rs);
	}

	/**
	 * Retrieves the value of a constant.
	 * @param string $setting Name of the constant holding the setting value.
	 * @param bool[optional] $required Specify if the setting is required or not. Defaults to TRUE.
	 * @return mixed
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAppSetting(string $setting, $required=true)
	{
		if (!defined($setting)) {
			if ($required===false) {
				return null;
			}
			throw new ConfigurationUndefinedException("$setting not found in app settings.");
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
	 * @param string $port Database port. Empty string to use app settings.
	 * @return DBConnectionSettings Initialized object containing database properties
	 * @throws ConfigurationUndefinedException
	 */
	protected static function getConnectionSettings(string $host='', string $user='', string $password='', string $schema='', string $port=''): object
	{
		return new DBConnectionSettings(
			$host ?: self::getAppSetting('MYSQL_HOST'),
			$user ?: self::getAppSetting('MYSQL_USER'),
			$password ?: self::getAppSetting('MYSQL_PASS'),
			$schema ?: self::getAppSetting('MYSQL_SCHEMA'),
			$port ?: self::getAppSetting('MYSQL_PORT', false)
		);
	}

	/**
	 * Returns a MySQLi object using either default connection settings, or settings passed in.
	 * @param string $host (Optional) database connection host
	 * @param string $user (Optional) database connection user name
	 * @param string $password (Optional) database connection password
	 * @param string $schema (Optional) database name
	 * @param string $port (Optional) database connection port
	 * @return mysqli
	 * @throws ConfigurationUndefinedException
	 */
	public static function getMysqli(string $host='', string $user='', string $password='', string $schema='', string $port=''): mysqli
	{
		$c = MySQLConnection::getConnectionSettings($host, $user, $password, $schema, $port);
		return(new mysqli($c->host, $c->user, $c->password, $c->schema, $c->port));
	}

	/**
	 * Tests if the object currently has a viable database connection.
	 * @return bool Flag indicating if there is a viable database connection or not.
	 */
	public function hasConnection(): bool
	{
		if ($this->mysqli instanceof mysqli === false) {
			return (false);
		}
		return ($this->mysqli->connect_error === null);
	}

	/**
	 * Executes SQL statement
	 * @param string $query SQL statement to execute.
     * @param string $types
     * @param ...$vars
	 * @throws Exception
	 */
	public function query(string $query, string $types='', &...$vars)
	{
        $this->connectToDatabase();
        if ($types) {
            $stmt = $this->mysqli->prepare($query);
            if(!$stmt) {
                throw new Exception('Could not prepare statement: '.$this->mysqli->error);
            }
            array_unshift($vars, $types);
            call_user_func_array([$stmt, 'bind_param'], $vars);
            if(!$stmt->execute()) {
                throw new Exception('Error executing query: '.$this->mysqli->error);
            }
            $stmt->close();
        }
        else {
            $this->mysqli->query($query);
        }
	}

	/**
	 * Alias for MySQLConnection->connectToDatabase() for convenience.
	 * Can be chained with other MySQLConnection methods.
	 * @return $this
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 */
	public function mysqli(): MySQLConnection
	{
		$this->connectToDatabase();
		return $this;
	}

	/**
	 * Retrieves the last insert id created in the database.
	 * @return int Last insert id value.
	 */
	public function retrieveInsertID(): int
	{
		return ($this->mysqli->insert_id);
	}
}