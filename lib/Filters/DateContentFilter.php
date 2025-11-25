<?php

namespace Littled\Filters;

use Littled\Validation\Validation;
use Littled\Exception\ContentValidationException;


class DateContentFilter extends StringContentFilter
{
    function __construct(string $label='', string $key='', $value = null, $size = 0, $cookieKey = '')
    {
        parent::__construct($label, $key, $value, $size, $cookieKey);
        $this->checkEmptyValue();
    }

    /**
     * Converts empty string value to null. Date value passed to an SQL query cannot be an empty string.
     * @return void
     */
    protected function checkEmptyValue(): void
    {
        if ($this->value === '') {
            $this->value = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function collectValue(bool $read_cookies = true, ?array $src = null): void
    {
        parent::collectValue($read_cookies, $src);
        if ($this->value) {
            try {
                $d = Validation::validateDateString($this->value);
                $this->value = $d->format('m/d/Y');
            } catch (ContentValidationException $ex) {
                $this->value = '[' . $ex->getMessage() . ']';
            }
        }
        $this->checkEmptyValue();
    }
}