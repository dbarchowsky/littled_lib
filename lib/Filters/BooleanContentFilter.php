<?php
namespace Littled\Filters;

use Littled\Validation\Validation;
use mysqli;

/**
 * Class BooleanContentFilter
 * @package Littled\Filters
 */
class BooleanContentFilter extends ContentFilter
{
    /**
	 * Collects the filter value from request variables, session variables, or cookie variables, in that order.
	 * Insures that the value is converted to a TRUE, FALSE or NULL value.
	 * @param bool $read_cookies Flag indicating that the cookie collection should be included in the search for a
	 * filter value.
	 */
	public function collectValue(bool $read_cookies=true)
	{
		parent::collectValue($read_cookies);
		if ($this->value) {
			$this->value = Validation::parseBoolean($this->value);
		}
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
	 * @return string Escaped value.
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=false): string
	{
		if ($this->value===true || $this->value===1) {
			return('1');
		}
		if ($this->value===false || $this->value===0) {
			return('0');
		}
		return ('null');
	}
}
