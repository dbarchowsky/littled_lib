<?php
namespace Littled\Model;

require_once ("../Database/MySQLConnection.php");
require_once ("../Forms/IntegerInput.php");
require_once ("../Exception/InvalidQueryException.php");
require_once ("../Exception/RecordNotFoundException.php.php");

use \Littled\Database\MySQLConnection;
use Littled\Exception\InvalidQueryException;
use \Littled\Exception\RecordNotFoundException;
use \Littled\Forms\IntegerInput;

class Operations extends MySQLConnection
{
	/** @var  IntegerInput Id of the record. */
	public $id;

	//<editor-fold> class constants
	public static function TABLE_NAME() { return ""; }
	//</editor-fold>

	function __construct()
	{
		parent::__construct();
		$this->id = new IntegerInput("id", "ID", null, true);
	}

	/**
	 * Retrieves data from the database based on the internal properties of the
	 * class instance. Sets the values of the internal properties of the class
	 * instance using the database data.
	 */
	public function read()
	{
		if ($this->TABLE_NAME()=="") {
			throw new \Exception("[".__METHOD__."] Table name not set. ");
		}

		$this->openMysqli();

		$fields = $this->collect_table_columns();

		/* create query to select record from database */
		$query = "SELECT ".
			implode(',', $fields)." ".
			"FROM `".$this->TABLE_NAME()."` ".
			"WHERE id = {$this->id->value}";
		// $this->debugmsg("query: \"{$query}\"");

		/* run query */
		$result = $this->mysqli->query($query);

		/* validate query result */
		if ($result == false) {
			throw new InvalidQueryException($this->mysqli->error);
		}

		/* fetch and validate data */
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if (!is_array($row)) {
			throw new RecordNotFoundException("Requested record (#{$this->id->value}) not found in \"".$this->TABLE_NAME()."\" table.");
		}

		$this->assign_database_values($row);
	}

	protected function assign_database_values( $row )
	{
		throw new \BadMethodCallException(__METHOD__." not implemented.");
	}

	protected function collect_table_columns()
	{
		throw new \BadMethodCallException(__METHOD__." not implemented.");
	}
}