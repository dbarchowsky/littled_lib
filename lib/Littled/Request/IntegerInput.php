<?php
namespace Littled\Request;


/**
 * Class IntegerInput
 * @package Littled\Request
 */
class IntegerInput extends RequestInput
{
	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param \mysqli $mysqli
	 * @return string Escaped value.
	 */
	public function escapeSQL($mysqli)
	{
		if ($this->value===null) {
			return ("null");
		}
		return $mysqli->real_escape_string($this->value);
	}
}