<?php
namespace Littled\Filters;

use Littled\Validation\Validation;


/**
 * Class IntegerContentFilter
 * @package Littled\Filters
 */
class IntegerContentFilter extends ContentFilter
{
	/**
	 * Collects the filter value from request variables, session variables, or cookie variables, in that order.
	 */
	protected function collectRequestValue()
	{
		$this->value = Validation::collectIntegerRequestVar($this->key);
	}

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