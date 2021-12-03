<?php
namespace Littled\Database;

use Littled\Exception\InvalidQueryException;
use Exception;

/**
 * Class DBUtils
 * @package Littled\Database
 */
class DBUtils
{
	/**
	 * @param $timestamp
	 * @return string
	 */
    public static function formatSqlDate($timestamp=null): string
    {
        if ($timestamp && !is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        return (date('Y-m-d H:i:s', $timestamp));
    }

	/**
	 * Fills $arOptions array with name/value pairs retrieved using the supplied SQL SELECT query.
	 * @param string $query SQL SELECT query used to retrieve name/value array.
	 * @param array $options Function will fill this array with name/value pairs to be used in option list.
	 * @throws InvalidQueryException
	 */
	public static function retrieveOptionsList (string $query, array &$options )
	{
		$conn = new MySQLConnection();

		$data = $conn->fetchRecords($query);
		foreach($data as $row) {
			$options[$row[0]] = $row[1];
		}
	}

	/**
	 * Retrieve all possible values for a given ENUM column in a table in the database.
	 * @param string $table_name Name of table containing the ENUM column.
	 * @param string $column Name of the ENUM column.
	 * @return array Array containing all the possible values as name/value pairs.
	 * @throws InvalidQueryException
	 */
	public static function getEnumOptions(string $table_name, string $column ): array
	{
		$conn = new MySQLConnection();
		$query = "SHOW COLUMNS FROM `$table_name` LIKE '$column'";
		$data = $conn->fetchRecords($query);
		if (count($data) < 1) {
			return(array());
		}
		$values = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $data[0]->Type));
		$options = array();
		for ($i=0; $i<count($values); $i++) {
			if (trim($values[$i])!="") {
				$options[$values[$i]] = $values[$i];
			}
		}
		return ($options);
	}


	/**
	 * Uses supplied SQL SELECT statement to retrieve name/value pairs from database. These name/value pairs are then written out at HTML option tags.
	 * @param string $query SQL SELECT statement
	 * @param array $selected_options Array containing the values of any selected options.
	 */
	public static function displayQueryOptions(string $query, array $selected_options )
	{
		try
		{
			$conn = new MySQLConnection();
			$data = $conn->fetchRecords($query);
			/** TODO referencing the row elements by index might not work below. */
			foreach($data as $row): ?>
				<option value="<?=$row[0] ?>"<?=((in_array($row[0], $selected_options))?(" selected=\"selected\""):("")) ?>><?=$row[1] ?></option>
<?php endforeach;
		}
		catch(Exception $ex) {
?>
			<option value="" disabled="disabled" style="background-color:#ff0000;color:#ffffff;font-weight:bold;">Error retrieving options: <?=$ex->getMessage()?></option>
<?php
		}
	}


	/**
	 * Takes an array containing name/value pairs and writes them out as a series of HTML option tags.
	 * @param array $options Array containing name/value pairs.
	 * @param array $selected_options Array containing the values of any selected options.
	 */
	public static function displayCachedOptions(array $options, array $selected_options )
	{
		?>
		<?php foreach($options as $key => $val): ?>
		<option value="<?=$key?>"<?=((in_array($key,$selected_options))?(" selected=\"selected\""):(""))?>><?=$val?></option>
	<?php endforeach; ?>
		<?php
	}


	/**
	 * Prints out all the possible values from an ENUM column in the database as a series of HTML option tags.
	 * @param string $table_name Name of the table containing the ENUM column.
	 * @param string $column Name of the ENUM column.
	 * @param array $selected_options Array containing the values of any selected options.
	 */
	public static function displayEnumOptions(string $table_name, string $column, array $selected_options )
	{
		try {
			$arOptions = DBUtils::getEnumOptions($table_name, $column);
			DBUtils::displayCachedOptions($arOptions, $selected_options);
		}
		catch(InvalidQueryException $ex) {
			?><option value="" disabled="disabled" style="background-color:#ff0000;color:#ffffff;font-weight:bold;">Error retrieving options: <?=$ex->getMessage()?></option><?php
		}
	}


	/**
	 * Runs supplied SQL SELECT statement to retrieve recordset. Fills supplied array with the first value in each row of the recordset (all other values in the row are ignored).
	 * @param string $query SQL SELECT query.
	 * @param array $buffer Array where the results will be stored.
	 * @throws InvalidQueryException
	 */
	public static function fillArrayFromQuery (string $query, array &$buffer)
	{
		$conn = new MySQLConnection();
		$data = $conn->fetchRecords($query);
		foreach($data as $row) {
			/** TODO Referencing row elements by index might not work below. */
			array_push($buffer, $row[0]);
		}
	}


	/**
	 * returns string containing values returned by database query formatted as a javascript array
	 * @param string $query MySQL query to run to retrieve values
	 * @return string database values formatted as a javascript array
	 * @throws InvalidQueryException
	 */
	public static function formatQueryJavascriptArray(string $query ): string
	{
		$conn = new MySQLConnection();
		$tmp = array();
		$data = $conn->fetchRecords($query);
		foreach($data as $row) {
			array_push($tmp, "'".preg_replace("/'/", "\\'", $row[0])."'");
		}
		return (implode(",",$tmp));
	}
}