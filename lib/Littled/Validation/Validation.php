<?php

namespace Littled\Validation;
use Littled\Exception\ContentValidationException;

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
	 * Tests a date string against a specified date format string.
	 * @param string $date Date string to test.
	 * @param string $format Date format string to use to evaluate the date string.
	 * @return \DateTime|null Returns DateTime object representing the date if the date string matches the date format
	 * string. Returns null otherwise.
	 */
	protected static function _testDateFormat($date, $format)
	{
		$d = \DateTime::createFromFormat($format, $date);
		if ($d && $d->format($format) == $date) {
			return ($d);
		}
		return (null);
	}

	/**
	 * Converts script argument (query string or form data) to array of numeric values.
	 * @param string $key Key containing potential numeric values.
	 * @return mixed Returns an array if values are found for the specified key. Null otherwise.
	 */
	public static function collectIntegerArrayRequestVar($key)
	{
		$result = filter_input_array(INPUT_GET, array($key => FILTER_VALIDATE_INT), FILTER_NULL_ON_FAILURE);
		if ($result===null || $result===false) {
			$result = filter_input_array(INPUT_POST, array($key => FILTER_VALIDATE_INT), FILTER_NULL_ON_FAILURE);
		}
		if ($result===null || $result===false) {
			return (array());
		}
		return ($result);
	}

	/**
	 * Returns request variable as explicit integer value, or null if the request variable is not set or does not
	 * represent a float value.
	 * @param string $key Key in the collection storing the value to look up.
	 * @param int $index Index of the array to look up, if the variable's value is an array.
	 * @param array $src Array to search for $key, e.g. $_GET or $_POST
	 * @return int|null|string
	 */
	public static function collectIntegerRequestVar($key, $index=null, $src=null)
	{
		$value = Validation::_parseInput(FILTER_VALIDATE_INT, $key, $index, $src);
		return Validation::parseInteger($value);
	}

	/**
	 * Converts script argument (query string or form data) to array of numeric values.
	 * @param string $key Key containing potential numeric values.
	 * @return mixed Returns an array if values are found for the specified key. Null otherwise.
	 */
	public static function collectNumericArrayRequestVar($key)
	{
		$result = filter_input_array(INPUT_GET, array($key => FILTER_VALIDATE_FLOAT), FILTER_NULL_ON_FAILURE);
		if ($result===null || $result===false) {
			$result = filter_input_array(INPUT_POST, array($key => FILTER_VALIDATE_FLOAT), FILTER_NULL_ON_FAILURE);
		}
		if ($result===null || $result===false) {
			return (array());
		}
		return ($result);
	}

	/**
	 * Searches POST and GET data in that order, for a property corresponding to
	 * $key.
	 * @param string $key Key of the variable value to collect.
	 * @param int $filter Filter token corresponding to the 3rd parameter of
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
	 * @param string $key
	 * @param int $index
	 * @param array $src
	 * @return int|null
	 * @deprecated Use collectIntegerRequestVar() instead.
	 */
	public static function parseIntegerInput( $key, $index=null, $src=null )
	{
		return Validation::parseIntegerInput($key,$index,$src);
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
			elseif($value > PHP_INT_MAX) {
				return ((float)$value);
			}
			else {
				return ((int)$value);
			}
		}
		return (null);
	}

	/**
	 * Deprecated. Use collectNumericArrayRequestVar() instead.
	 * @param string $key
	 * @return mixed
	 * @deprecated
	 */
	public static function parseNumericArrayInput($key)
	{
		return (Validation::collectNumericArrayRequestVar($key));
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
	 * Tests date string to see if it is in a recognized format.
	 * @param string $date Date string to test.
	 * @param array|null $formats Data formats to test.
	 * @returns \DateTime
	 * @throws ContentValidationException
	 */
	public static function validateDateString($date, $formats=null)
	{
		if ($formats==null) {
			$formats = array(
				'Y-m-d',
				'd/m/y',
				'd/m/Y',
				'j/n/y',
				'j/n/Y',
				'F d, Y',
				'F j, Y',
				'M d, Y',
				'M j, Y'
			);
		}
		elseif (!is_array($formats)) {
			$formats = array($formats);
		}

		foreach($formats as $format) {
			$d = Validation::_testDateFormat($date, $format);
			if ($d instanceof \DateTime) {
				return ($d);
			}
		}
		throw new ContentValidationException("Unrecognized date value.");
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
