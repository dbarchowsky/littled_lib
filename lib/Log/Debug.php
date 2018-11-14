<?php
namespace Littled\Log;

use Littled\Exception\ConfigurationUndefinedException;

class Debug
{
	/**
	 * Utility routine that should be easier to spot in code than a simple call
	 * to PHP's built-in exit() routine. 
	 * @param string $debug_msg (Optional) Error message to display before bailing.
	 * @param boolean $display_line_number (Optional) flag to display/supress the line number in the debug output. Default value is TRUE.
	 * @param boolean $display_method_name (Optional) flag to display/suppress the current method name in the debug output. Default value is FALSE.
	 */
	public static function stopScript( $debug_msg='', $display_line_number=true, $display_method_name=false )
	{
		if ($debug_msg) {
			Debug::output($debug_msg, $display_line_number, $display_method_name);
		}
		exit;
	}

	/**
	 * Generates log file name based on the current date and time.
	 * @return string Log filename.
	 */
	public static function generateLogFilename()
	{
		$date = new \DateTime();
		return ($date->format('Y-m-d-H-i-s').".log");
	}

	/**
	 * Prints out json encoded version of a variable.
	 * @param mixed $var Variable to inspect
	 */
	public static function inspectAsJSON( &$var )
	{
		header('Content-Type: application/json');
		print (json_encode($var));
		exit;
	}

	/**
	 * Wrapper for PHP var_dump() routine that places it between preformatted text tags.
	 * @param mixed $var Variable to inspect.
	 */
	public static function inspectInBrowser( &$var )
	{
		print ("<pre>");
		print (var_dump($var));
		print ("</pre>");
	}

	/**
	 * Logs a message to a log file.
	 * @param string $status Status of message.
	 * @param string $msg Message to log.
	 * @return int Status code.
	 * @throws ConfigurationUndefinedException If ERROR_LOG is not defined.
	 */
	public static function log ( $status, $msg )
	{
		if (!defined('ERROR_LOG')) {
			throw new ConfigurationUndefinedException("ERROR_LOG not defined in app settings.");
		}
		$fh = fopen(ERROR_LOG, 'a+');
		if (! $fh) {
			return (0);
		}

		$msg = "[".date("m/d/y H:i:s")."] [".$_SERVER["SERVER_NAME"]."] [".$status."] ".basename($_SERVER["PHP_SELF"]).": ".$msg."\n";

		$rval = fwrite($fh, $msg);

		fclose($fh);
		return($rval);
	}

	/**
	 * Returns path to log directory.
	 * @return string Path to log directory
	 * @throws ConfigurationUndefinedException
	 */
	public static function LOG_DIR()
	{
		if (!defined('APP_BASE_DIR')) {
			throw new ConfigurationUndefinedException("APP_BASE_DIR not defined in app settings.");
		}
		return (APP_BASE_DIR."logs".DIRECTORY_SEPARATOR);
	}

	/**
	 * Logs a variable value to a log file.
	 * @param mixed $var Variable to inspect and log
	 * @throws ConfigurationUndefinedException
	 */
	public static function logVariable( &$var )
	{
		$f = fopen(self::LOG_DIR().self::generateLogFilename(), 'a');
		if ($f) {
			fwrite($f, json_encode($var));
		}
		fclose($f);
	}

	/**
	 * Prints formatted debugging message to the browser.
	 * @param string $msg Message to display
	 * @param bool $display_line_number Flag to control inclusion of the line number where the output() method
	 * was called. Defaults to true.
	 * @param bool $display_method_name Flag to control inclusion of the method name where the output() method
	 * was colled. Defaults to true.
	 */
	public static function output( $msg, $display_line_number=true, $display_method_name=false )
	{
		if (defined('SUPPRESS_DEBUG') && SUPPRESS_DEBUG==true) {
			return;
		}
		print "<div class=\"debug\"><span class=\"formerror\">*** DEBUG *** </span>";
		if ($display_line_number || $display_method_name) {
			$backtrace = debug_backtrace();
		}
		if ($display_line_number) {
			print ("<span class=\"dimtext\">[".basename($backtrace[0]['file']).", line ".$backtrace[0]['line']."]</span> ");
		}
		if ($display_method_name) {
			print ("<span class=\"dimtext\">".$backtrace[1]['class']."::".$backtrace[1]['function']."()</span> ");
		}
		print htmlentities($msg)."</div>\n";
	}

	/**
	 * Prints the current stack in the browser.
	 * @param string $msg Optional message to include in the output.
	 */
	public static function stackTrace($msg="")
	{
		$backtrace = debug_backtrace();
		for($i=(count($backtrace)-1); $i>=0; $i--) {
			print "<div class=\"debug\">[".basename($backtrace[$i]['file']).", line ".$backtrace[$i]['line']."]</div>\n";
		}
		if ($msg!="") {
			print "<div class=\"debug\"><span class=\"formerror\">*** DEBUG *** </span>{$msg}</div>\n";
		}
	}

	/**
	 * Prints the value corresponding to the key in the session collection.
	 * @param string $key Key of the session variable to inspect.
	 */
	public static function testSessionVariable( $key )
	{
		if (!isset($_SESSION[$key])) {
			Debug::output("SESSION[{$key}] not set.");
			return;
		}
		if (trim($_SESSION[$key]) == '') {
			Debug::output("SESSION[{$key}] is empty.");
			return;
		}
		Debug::output("\$_SESSION[{$key}]: ".$_SESSION[$key]);
	}
}
