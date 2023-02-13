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
     * @inheritDoc
     */
    protected function collectRequestValue(?array $src = null)
    {
        Validation::collectBooleanRequestVar($this->key, Validation::DEFAULT_REQUEST_FILTER, $src);
    }

    /**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mysqli $mysqli
	 * @param bool $include_quotes Optional If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
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
		return ('NULL');
	}

    /**
     * @inheritDoc
     */
    public function formatQueryString(): string
    {
        if ($this->value===true || $this->value===1) {
            return $this->key.'=1';
        }
        if ($this->value===false || $this->value===0) {
            return $this->key.'=0';
        }
        return '';
    }
}
