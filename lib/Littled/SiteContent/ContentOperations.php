<?php
namespace Littled\SiteContent;

use Littled\Database\MySQLConnection;


/**
 * Class ContentOperations
 * @package Littled\SiteContent
 */
class ContentOperations
{
	/** @var MySQLConnection Database connection. */
	public $connection;

	/**
	 * ContentOperations constructor.
	 */
	function __construct()
	{
		$this->connect();
	}

	/**
	 * Makes database connection.
	 */
	public function connect()
	{
		$this->connection = new MySQLConnection();
	}

	/**
	 * Retrieves record from database and uses it to hydrate object properties.
	 */
	public function read()
	{

	}

	/**
	 * Commits object property values to database record.
	 */
	public function commit()
	{

	}
}