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
    protected function collectRequestValue(?array $src = null): void
    {
        $this->value = Validation::collectIntegerRequestVar($this->key, null, $src);
    }

    /**
     * @inheritDoc
     */
    public function escapeSQL($mysqli, $include_quotes = false): ?string
    {
        if ($this->value === null) {
            return null;
        }
        return $mysqli->real_escape_string($this->value);
    }
}