<?php
namespace Littled\Filters;

use Littled\Validation\Validation;


use Littled\Exception\ContentValidationException;

/**
 * Class DateContentFilter
 * @package Littled\Filters
 */
class DateContentFilter extends StringContentFilter
{
	public function collectValue($read_cookies = true)
	{
		parent::collectValue($read_cookies);
		if ($this->value) {
			try {
				Validation::validateDateString($this->value);
				$this->value = date("m/d/Y", strtotime($this->value));
			}
			catch (ContentValidationException $ex) {
				$this->value = "[".$ex->getMessage()."]";
			}
		}
	}
}