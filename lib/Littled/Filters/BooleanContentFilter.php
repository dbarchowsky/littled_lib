<?php
namespace Littled\Filters;

use Littled\Validation\Validation;


/**
 * Class BooleanContentFilter
 * @package Littled\Filters
 */
class BooleanContentFilter extends ContentFilter
{
	/**
	 * Collects the filter value from request variables, session variables, or cookie variables, in that order.
	 * Insures that the value is converted to a TRUE, FALSE or NULL value.
	 * @param bool $read_cookies Flag indicating that the cookie collection should included in the search for a
	 * filter value.
	 */
	public function collectValue($read_cookies=true)
	{
		parent::collectValue($read_cookies);
		if ($this->value) {
			$this->value = Validation::parseBoolean($this->value);
		}
	}
}
