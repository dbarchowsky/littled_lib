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
     * {@inheritDoc}
     * @param string $label
     * @param string $key
     * @param ?int $value
     * @param ?int $size
     * @param string $cookieKey
     */
    public function __construct(string $label, string $key, ?int $value = null, ?int $size = 0, string $cookieKey = '')
    {
        parent::__construct($label, $key, $value, $size, $cookieKey);
    }

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
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
	 * @return string Escaped value.
	 */
	public function escapeSQL($mysqli, $include_quotes=false):string
	{
		if ($this->value===null) {
			return ('NULL');
		}
		return $mysqli->real_escape_string($this->value);
	}
}