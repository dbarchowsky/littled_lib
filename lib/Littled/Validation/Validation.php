<?php

namespace Littled\Validation;

/**
 * Class Validation
 * Assorted static validation routines.
 * @package Littled\Validation
 */
class Validation
{
	/**
	 * Returns request variable as explicit integer value, or null if the request variable is not set or does not
	 * represent a float value.
	 * @param int $filter Filter to apply to the variable value, e.g. FILTER_VALIDATE_INT or FILTER_VALIDATE_FLOAT
	 * @param string $key Key in the collection storing the value to look up.
	 * @param int $index Index of the array to look up, if the variable's value is an array.
	 * @param array $src Array to search for $key, e.g. $_GET or $_POST
	 * @return string|null
	 */
	protected static function _parseInput( $filter, $key, $index=null, $src=null )
	{
		$value = null;
		if ($src===null) {
			$src = array_merge($_GET, $_POST);
		}
		if (!isset($src[$key])) {
			return (null);
		}
		if ($index!==null)
		{
			$arr = filter_var($src[$key], $filter, FILTER_REQUIRE_ARRAY);
			if (is_array($arr) && count($arr) >= ($index-1))
			{
				$value = $arr[$index];
			}
		}
		else
		{
			$value = filter_var($src[$key], $filter);
		}
		return ($value);
	}

	/**
	 * Searches POST and GET data in that order, for a property corresponding to
	 * $key.
	 * @param string $key Key of the variable value to collect.
	 * @param string $filter Filter token corresponding to the 3rd parameter of
	 * PHP's built-in filter_input() routine.
	 * @return mixed Value found for the requested key. Returns an empty string
	 * if none of the collections contain the requested key.
	 */
	public static function collectRequestVar( $key, $filter=FILTER_SANITIZE_STRING )
	{
		$value = trim(filter_input(INPUT_POST, $key, $filter));
		if (!$value) {
			$value = trim(filter_input(INPUT_GET, $key, $filter));
		}
		return ($value);
	}

	/**
	 * Searches POST, GET and session data, in that order, for a property
	 * corresponding to $key.
	 * @param string $key Key of the variable value to collect.
	 * @param string $filter Filter token corresponding to the 3rd parameter of
	 * PHP's built-in filter_input() routine.
	 * @return mixed Value found for the requested key. Returns an empty string
	 * if none of the collections contain the requested key.
	 */
	public static function collectStringInput( $key, $filter=FILTER_SANITIZE_STRING )
	{
		$value = Validation::collectRequestVar($key, $filter);
		if (!$value && isset($_SESSION[$key]) && strlen(trim($_SESSION[$key])) > 0) {
			$value = trim($_SESSION[$key]);
		}
		return ($value);
	}
	/**
	 * Tests POST data for the current requested action. Returns a token indicating
	 * the action that can be used in place of testing POST data directly on
	 * a page.
	 * @return string
	 */
	public static function getPageAction()
	{
		if (!defined('P_COMMIT') || !defined('P_CANCEL')) {
			return ('');
		}
		$action = trim(filter_input(INPUT_POST, P_COMMIT, FILTER_SANITIZE_STRING));
		if (strlen($action) > 0) {
			$action = P_COMMIT;
		}
		else {
			$action = trim(filter_input(INPUT_POST, P_CANCEL, FILTER_SANITIZE_STRING));
			if (strlen($action) > 0) {
				$action = P_CANCEL;
			}
		}
		return ($action);
	}

	/**
	 * Tests if string value represents an integer value.
	 * @param mixed $value Value to test.
	 * @return bool
	 */
	public static function isInteger( $value )
	{
		if (is_int($value)) {
			return (true);
		}
		if (is_string($value)) {
			if ($value[0] == '-') {
				return ctype_digit(substr($value, 1));
			}
			else {
				return ctype_digit($value);
			}
		}
		return (false);
	}

	/**
	 * Tests value and returns TRUE if it evaluates to some string that equates with a "true" flag.
	 * Returns FALSE only if the value evaluates to some string that equates with a "false" flag.
	 * Returns NULL if the value doesn't make sense in a TRUE/FALSE context.
	 * @param mixed $value Value to test.
	 * @return TRUE, FALSE, or NULL
	 */
	public static function parseBoolean( $value )
	{
		if (is_bool($value)) {
			return ($value);
		}
		if (is_string($value)) {
			if ($value === "1" || $value === "true" || $value === "on" || $value === true || $value === 1) {
				return (true);
			} elseif ($value == "0" || $value === "false" || $value === "off" || $value === 0 || $value === false) {
				return (false);
			} else {
				return (null);
			}
		}
		return (null);
	}

	/**
	 * Returns TRUE/FALSE depending on the value of the requested inptu variable.
	 * @param string $key Input variable name in either GET  or POST data.
	 * @param integer $index (Optional) index of the element to test, if the variable is an array.
	 * @param array $src (Optional) array to use in place of GET or POST data.
	 * @return boolean TRUE/FALSE depending on the value of the input variable.
	 */
	public static function parseBooleanInput( $key, $index=null, $src=null)
	{
		$value = null;
		if ($src===null) {
			$src = array_merge($_GET, $_POST);
		}
		if (!isset($src[$key])) {
			return (null);
		}
		if ($index!==null)
		{
			$arr = filter_var($src[$key], FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
			if (is_array($arr) && count($arr) >= ($index-1))
			{
				$value = $arr[$index];
			}
		}
		else
		{
			$value = trim(filter_var($src[$key], FILTER_SANITIZE_STRING));
		}

		return Validation::parseBoolean($value);
	}

	/**
	 * Returns request variable as explicit float value, or null if the request variable is not set or does not
	 * represent a float value.
	 * @param string $key Key in the collection storing the value to look up.
	 * @param int $index Index of the array to look up, if the variable's value is an array.
	 * @param array $src Array to search for $key, e.g. $_GET or $_POST
	 * @return float|null
	 */
	public static function parseFloatInput( $key, $index=null, $src=null )
	{
		$value = Validation::parseNumericInput( $key, $index, $src);
		if ($value !== null) {
			return ((float)$value);
		}
		return (null);
	}

	/**
	 * Tests a variable and returns its equivalent explicit integer value, or null if the variable value doesn't
	 * represent an integer value.
	 * @param mixed $value Value to test.
	 * @return int|null Value explicitly converted to an integer value, or null if the value does not represent an
	 * integer value.
	 */
	public static function parseInteger( $value )
	{
		if (Validation::isInteger($value)) {
			return ((int)$value);
		}
		return (null);
	}

	/**
	 * Returns request variable as explicit integer value, or null if the request variable is not set or does not
	 * represent a float value.
	 * @param string $key Key in the collection storing the value to look up.
	 * @param int $index Index of the array to look up, if the variable's value is an array.
	 * @param array $src Array to search for $key, e.g. $_GET or $_POST
	 * @return int|null|string
	 */
	public static function parseIntegerInput( $key, $index=null, $src=null )
	{
		$value = Validation::_parseInput(FILTER_VALIDATE_INT, $key, $index, $src);
		return Validation::parseInteger($value);
	}

	/**
	 * Converts a given string value to a numeric equivalent.
	 * @param $value string Value to parse.
	 * @return int|null
	 */
	public static function parseNumeric( $value )
	{
		if (is_numeric($value)) {
			if (strpos($value, ".") !== false) {
				return ((float)$value);
			}
			else {
				return ((int)$value);
			}
		}
		return (null);
	}

	/**
	 * Converts script argument (query string or form data) to array of numeric values.
	 * @param string $key Key containing potential numeric values.
	 * @return mixed Returns an array if values are found for the specified key. Null otherwise.
	 */
	public static function parseNumericArrayInput( $key )
	{
		/** @todo use filter_var() in place of accessing $_REQUEST directly */
		if (isset($_REQUEST[$key]))
		{
			if (is_array($_REQUEST[$key]))
			{
				$arTemp = array();
				foreach($_REQUEST[$key] as $value)
				{
					if (is_numeric($value))
					{
						$arTemp[count($arTemp)] = (int)$value;
					}
					elseif (strpos($value, ",")>0)
					{
						$arVals = explode(",", $value);
						foreach($arVals as $subval)
						{
							if (is_numeric(trim($subval)))
							{
								$arTemp[count($arTemp)] = (int)trim($subval);
							}
						}
					}
				}
				return ($arTemp);
			}
			elseif (is_numeric($_REQUEST[$key]))
			{
				return(array((int)$_REQUEST[$key]));
			}
		}
		return (null);
	}

	/**
	 * - Searches GET and POST data for the variable value matching $key.
	 * - Validates the value & returns an integer value only if the intput
	 * value is numeric.
	 * @param string $key Name of the input parameter holding the value of interest.
	 * @param integer $index (Optional) index within input array of the value of interest.
	 * @return mixed Float or integer value.
	 */
	public static function parseNumericInput( $key, $index=null, $src=null )
	{
		$value = Validation::_parseInput(FILTER_VALIDATE_FLOAT, $key, $index, $src);
		return((is_numeric($value))?($value):(null));
	}

	/**
	 * Validates email address.
	 * @param string $sEmail Email address to validate
	 * @return boolean true/false indicating valid or invalid email address.
	 */
	public static function validateEmailAddress( $sEmail )
	{
		return (preg_match("/\S+\@\S+\.\S+/", $sEmail) && (!preg_match("/,\/\\;/", $sEmail)));
	}
}
