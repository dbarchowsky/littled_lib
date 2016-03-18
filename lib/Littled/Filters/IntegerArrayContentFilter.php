<?php
namespace Littled\Filters;

use Littled\Validation\Validation;


/**
 * Class IntegerArrayContentFilter
 * @package Littled\Filters
 */
class IntegerArrayContentFilter extends IntegerContentFilter
{
	protected function collectRequestValue()
	{
		$this->value = Validation::collectIntegerArrayRequestVar($this->key);
	}
}