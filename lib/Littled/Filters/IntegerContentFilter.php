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
}