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
	 * Validation constructor.
	 * All methods are static. This is unused.
	 */
	function __construct ( )
	{
		/* nothing necessary here */
	}

	/**
	 * do all necessary processing to content entered in form textarea fields to save it in XML
	 */
	public static function clean_text_for_xml($sContent)
	{
		/* remove newline characters */
		$sContent = preg_replace("/[\n\r]/", "<br />", $sContent);

		/* convert non-ASCII characters */
		$sContent = htmlentities($sContent, ENT_NOQUOTES, "iso-8859-1");

		/* restore HTML tags */
		$sContent = str_replace("&lt;", "<", $sContent);
		$sContent = str_replace("&gt;", ">", $sContent);
		$sContent = str_replace("&amp;", "&", $sContent);

		return ($sContent);
	}

	/**
	 * Does all necessary processing to content created in TinyMCE editor to save it in XML
	 * @param string $sContent Text to fix.
	 * @return string Fixed text.
	 */
	public static function clean_tinymce_text_for_xml( $sContent )
	{
		/* remove newline characters */
		$sContent = preg_replace("/[\n\r]/", "", $sContent);

		/* convert non-ASCII characters */
		$sContent = htmlentities($sContent, ENT_NOQUOTES, "iso-8859-1");

		/* restore HTML tags */
		$sContent = str_replace("&lt;", "<", $sContent);
		$sContent = str_replace("&gt;", ">", $sContent);
		$sContent = str_replace("&amp;", "&", $sContent);

		/* swap <b> for <strong> and <i> for <em> */
		$sContent = str_replace("<strong>", "<b>", $sContent);
		$sContent = str_replace("</strong>", "</b>", $sContent);
		$sContent = str_replace("<em>", "<i>", $sContent);
		$sContent = str_replace("</em>", "</i>", $sContent);

		$sContent = self::html_p_tags_to_br_tags($sContent);

		return ($sContent);
	}

	/**
	 * Searches POST and GET data in that order, for a property corresponding to
	 * $key.
	 * @param string $key Key of the variable value to collect.
	 * @param token $filter Filter token corresponding to the 3rd parameter of
	 * PHP's built-in filter_input() routine.
	 * @return mixed Value found for the requested key. Returns an empty string
	 * if none of the collections contain the requested key.
	 */
	public static function collect_request_var ( $key, $filter=FILTER_SANITIZE_STRING )
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
	 * @param token $filter Filter token corresponding to the 3rd parameter of
	 * PHP's built-in filter_input() routine.
	 * @return mixed Value found for the requested key. Returns an empty string
	 * if none of the collections contain the requested key.
	 */
	public static function collect_string_input( $key, $filter=FILTER_SANITIZE_STRING )
	{
		$value = validation_class::collect_request_var($key, $filter);
		if (!$value && isset($_SESSION[$key]) && strlen(trim($_SESSION[$key])) > 0) {
			$value = trim($_SESSION[$key]);
		}
		return ($value);
	}

	/**
	 * convert HTML paragraph tags to <br /> tags
	 */
	public static function html_p_tags_to_br_tags ( $sHTML )
	{
		$sHTML = preg_replace('/<p[^>]*>/', '', $sHTML); /* Remove the start <p> or <p attr=""> */
		$sHTML = preg_replace('/<\/p>/', '<br /><br />', $sHTML); /* Replace the end */
		return ($sHTML);
	}

	/**
	 * Tests POST data for the current requested action. Returns a token indicating
	 * the action that can be used in place of testing POST data directly on
	 * a page.
	 * @return string
	 */
	public static function get_page_action()
	{
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
	 * Tests value and returns TRUE if it evaluates to some string that equates with a "true" flag.
	 * Returns FALSE only if the value evaluates to some string that equates with a "false" flag.
	 * Returns NULL if the value doesn't make sense in a TRUE/FALSE context.
	 * @param mixed $value Value to test.
	 * @return TRUE, FALSE, or NULL
	 */
	public static function parse_boolean( $value )
	{
		if ($value==="1" || $value==="true" || $value==="on" || $value===true || $value===1) {
			return (true);
		}
		elseif ($value=="0" || $value==="false" || $value==="off" || $value===0 || $value===false) {
			return (false);
		}
		else {
			return (null);
		}
	}

	/**
	 * Returns TRUE/FALSE depending on the value of the requested inptu variable.
	 * @param string $key Input variable name in either GET  or POST data.
	 * @param integer $index (Optional) index of the element to test, if the variable is an array.
	 * @param array $src (Optional) array to use in place of GET or POST data.
	 * @return boolean TRUE/FALSE depending on the value of the input variable.
	 */
	public static function parse_boolean_input( $key, $index=null, $src=null)
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

		return validation_class::parse_boolean($value);
	}

	/**
	 * Forces GET variables values into decimal format.
	 * @param $key string Variable name.
	 * @param null $index integer Index to parse if the variable is an array.
	 * @return float|null
	 */
	public static function parse_float_input( $key, $index=null )
	{
		/** @todo Use filter_var() instead of accessing $_REQUEST directly. */
		if (!isset($_REQUEST[$key])) return (null);
		$value = (($index!==null)?($_REQUEST[$key][(int)$index]):($_REQUEST[$key]));
		return ((is_numeric($value))?((float)$value):(null));
	}

	/**
	 * Converts a given string value to a numeric equivalent.
	 * @param $value string Value to parse.
	 * @return int|null
	 */
	public static function parse_numeric( $value )
	{
		return ((is_int($value))?((int)$value):(null));
	}

	/**
	 * Converts script argument (query string or form data) to array of numeric values.
	 * @param string $key Key containing potential numeric values.
	 * @return mixed Returns an array if values are found for the specified key. Null otherwise.
	 */
	public static function parse_numeric_array_input( $key )
	{
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
	public static function parse_numeric_input( $key, $index=null, $src=null )
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
			$arr = filter_var($src[$key], FILTER_VALIDATE_FLOAT,FILTER_REQUIRE_ARRAY);
			if (is_array($arr) && count($arr) >= ($index-1))
			{
				$value = $arr[$index];
			}
		}
		else
		{
			$value = filter_var($src[$key], FILTER_VALIDATE_FLOAT);
		}
		return((is_numeric($value))?($value):(null));
	}

	/**
	 * - Remove all newline characters (don't replace them with <br> tags)
	 * - Strip out all problematic html characters
	 * - Do NOT preserve any html tags
	 */
	public static function strip_newlines_for_xml($sContent)
	{
		/* remove newline characters */
		$sContent = preg_replace("/[\n\r]/", "", $sContent);

		/* convert non-ASCII characters */
		$sContent = htmlentities($sContent, ENT_NOQUOTES, "iso-8859-1");

		return ($sContent);
	}

	/**
	 * Validates email address.
	 * @param string $sEmail Email address to validate
	 * @return boolean true/false indicating valid or invalid email address.
	 */
	public static function validate_email_address ( $sEmail )
	{
		return (preg_match("/\S+\@\S+\.\S+/", $sEmail) && (!preg_match("/,\/\\;/", $sEmail)));
	}
}
