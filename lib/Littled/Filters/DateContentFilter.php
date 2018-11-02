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
				$d = Validation::validateDateString($this->value);
				$this->value = $d->format("m/d/Y");
			}
			catch (ContentValidationException $ex) {
				$this->value = "[".$ex->getMessage()."]";
			}
		}
	}

	/**
	 * Escapes date string to format expected in SQL statements.
	 * @param \mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
	 * @return string
	 */
	public function escapeSQL($mysqli, $include_quotes=true)
	{
		if ($this->value===null) {
			return ('null');
		}
		if ($this->value=='') {
			return ('null');
		}
		try
		{
			$dt = new \DateTime($this->value);
		}
		catch(\Exception $e)
		{
			return ('null');
		}
		$value = $dt->format('Y-m-d');
		if ($value===false) {
			return ('null');
		}
		return ((($include_quotes)?("'"):("")).$value.(($include_quotes)?("'"):("")));
	}
}