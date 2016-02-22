<?php
namespace Littled\Database;

require_once ("../App/AppBase.php");

use Littled\App\AppBase;


class MySQLConnection extends AppBase
{
	/** @var object MySQLi connection to server. */
	protected $mysqli;

	function __construct()
	{

	}

	public static function get_mysqli( $host=MYSQL_HOST, $user=MYSQL_USER, $password=MYSQL_PASS, $schema=MYSQL_SCHEMA)
	{
		return(new mysqli($host, $user, $password, $schema));
	}

	/**
	 * Opens MySQLi connection. Stores connection as $mysqli property of the class.
	 * @throws \Exception On database connection properties not defined.
	 */
	public function open_mysqli()
	{
		if (!defined('MYSQL_HOST')) {
			throw new \Exception("DB connection not defined.");
		}
		if (!is_object($this->mysqli))
		{
			$this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA);
		}
	}

	/**
	 * Closes mysqli connection.
	 */
	public function close_mysqli()
	{
		if (is_object($this->mysqli))
		{
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}
}