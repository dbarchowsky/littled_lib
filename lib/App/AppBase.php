<?php
namespace Littled\App;


class AppBase
{
	function __construct()
	{

	}

	/**
	 * Print debug message including information about the location of the call to debugmsg() and the type of the object being worked on.
	 * @param string $error_msg Message to print.
	 */
	public function debugmsg( $error_msg )
	{
		if (defined('SUPPRESS_DEBUG') && SUPPRESS_DEBUG==true) {
			return;
		}
		print "<div class=\"debug\"><span class=\"formerror\">*** DEBUG *** </span>";
		$arDbg = debug_backtrace();
		print ("<span class=\"dimtext\">[".$arDbg[1]['class']."::".$arDbg[1]['function']."() line ".$arDbg[0]["line"]."] [".get_class($this)."]</span> ");
		print htmlentities($error_msg, ENT_QUOTES)."</div>\n";
	}
}