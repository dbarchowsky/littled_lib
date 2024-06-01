<?php
namespace Littled\Log;

use JetBrains\PhpStorm\NoReturn;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use DateTime;
use Exception;
use Littled\Exception\NotInitializedException;


class Debug
{
	/**
	 * Generates log file name based on the current date and time.
	 * @return string Log filename.
	 * @throws Exception
	 */
	public static function generateLogFilename(): string
    {
		$date = new DateTime();
		return ($date->format('Y-m-d-H-i-s').".log");
	}

    /**
     * Returns the current method name with its class without the path.
     * @return string
     * @deprecated Use Log::getShortMethodName() instead.
     */
    public static function getShortMethodName(): string
    {
        $debug = debug_backtrace();
        $class_path = explode('\\', $debug[1]['class']);
        return end($class_path)."::".$debug[1]['function'];
    }

    /**
	 * Prints out json encoded version of a variable.
	 * @param mixed $var Variable to inspect
	 */
	#[NoReturn] public static function inspectAsJSON( mixed $var ): void
    {
		header('Content-Type: application/json');
		print (json_encode($var));
		exit;
	}

	/**
	 * Wrapper for PHP var_dump() routine that places it between preformatted text tags.
	 * @param mixed $var Variable to inspect.
	 */
	public static function inspectInBrowser( mixed $var ): void
	{
		print ("<pre>");
		var_dump($var);
		print ("</pre>");
	}

	/**
	 * Logs a message to a log file.
	 * @param string $status Status of message.
	 * @param string $msg Message to log.
	 * @return int Status code.
     * @throws NotInitializedException
     */
	public static function log (string $status, string $msg ): int
    {
		$fh = fopen(LittledGlobals::getErrorLogPath(), 'a+');
		if (! $fh) {
			return (0);
		}

		$msg = "[".date("m/d/y H:i:s")."] [".$_SERVER["SERVER_NAME"]."] [".$status."] ".basename($_SERVER["PHP_SELF"]).": ".$msg."\n";

		$return = fwrite($fh, $msg);

		fclose($fh);
		return($return);
	}

	/**
	 * Returns path to log directory.
	 * @return string Path to log directory
	 * @throws ConfigurationUndefinedException
	 */
	public static function LOG_DIR(): string
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
	 * @throws Exception
	 */
	public static function logVariable( mixed $var ): void
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
	 * was called. Defaults to true.
	 */
	public static function output(string $msg, bool $display_line_number=true, bool $display_method_name=false ): void
    {
		if (!LittledGlobals::showVerboseErrors()) {
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
	public static function stackTrace(string $msg=""): void
    {
		$backtrace = debug_backtrace();
		for($i=(count($backtrace)-1); $i>=0; $i--) {
			print "<div class=\"debug\">[".basename($backtrace[$i]['file']).", line ".$backtrace[$i]['line']."]</div>\n";
		}
		if ($msg!="") {
			print "<div class=\"debug\"><span class=\"formerror\">*** DEBUG *** </span>$msg</div>\n";
		}
	}

    /**
     * Utility routine that should be easier to spot in code than a simple call
     * to PHP's built-in exit() routine.
     * @param string $debug_msg (Optional) Error message to display before bailing.
     * @param bool $display_line_number (Optional) flag to display/suppress the line number in the debug output.
     * Default value is TRUE.
     * @param bool $display_method_name (Optional) flag to display/suppress the current method name in the debug output.
     * Default value is FALSE.
     */
    #[NoReturn] public static function stopScript(
        string $debug_msg='',
        bool $display_line_number=true,
        bool $display_method_name=false ): void
    {
        if ($debug_msg) {
            Debug::output($debug_msg, $display_line_number, $display_method_name);
        }
        exit;
    }

    /**
	 * Prints the value corresponding to the key in the session collection.
	 * @param string $key Key of the session variable to inspect.
	 */
	public static function testSessionVariable(string $key ): void
    {
		if (!isset($_SESSION[$key])) {
			Debug::output("SESSION[$key] not set.");
			return;
		}
		if (trim($_SESSION[$key]) == '') {
			Debug::output("SESSION[$key] is empty.");
			return;
		}
		Debug::output("\$_SESSION[$key]: ".$_SESSION[$key]);
	}
}
