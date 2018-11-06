<?php


namespace Littled\Database;


/**
 * Class AppContentBase
 * @package Littled\Database
 */
class AppContentBase extends MySQLConnection
{
	/**
	 * Returns the current class base name and method name.
	 * @return string Class and method description.
	 */
	public static function getMethodDescriptor()
	{
		return(basename(str_replace('\\', '/', get_called_class()))."::".debug_backtrace()[1]['function']."()");
	}
}