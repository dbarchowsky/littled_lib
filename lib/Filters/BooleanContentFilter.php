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
        $this->value = Validation::collectBooleanRequestVar($this->key, null, $src);
    }

    /**
     * @inheritDoc
     */
    public function collectValue( bool $read_cookies=true, ?array $src=null )
    {
        $this->collectRequestValue($src);
        // have to override this test in the parent method because "false" is a valid value in this type of filter
        if ($this->value===true || $this->value===false) {
            return;
        }

        $this->collectValueFromSession();
        if ($this->value===true || $this->value===false) {
            return;
        }

        if ($read_cookies) {
            $this->collectValueFromCookie();
        }
    }

    /**
	 * @inheritDoc
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=false): ?string
	{
		if ($this->value===true || $this->value===1) {
			return('1');
		}
		if ($this->value===false || $this->value===0) {
			return('0');
		}
		return null;
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
