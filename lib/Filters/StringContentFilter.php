<?php
namespace Littled\Filters;

use Littled\Validation\Validation;
use mysqli;


/**
 * Class StringContentFilter
 * @package BFHHand\Filters
 */
class StringContentFilter extends ContentFilter
{
    /**
     * @inheritDoc
     */
    protected function collectRequestValue()
    {
        $this->value = Validation::collectStringRequestVar($this->key);
    }

    /**
     * @inheritDoc
     */
    public function escapeSQL(mysqli $mysqli, bool $include_quotes=true, bool $include_wildcards=true): string
    {
        $quote = ($include_quotes)?("'"):('');
        $wildcard = ($include_wildcards)?("%"):('');
        if ($this->value) {
            return $quote.$wildcard.$mysqli->real_escape_string($this->value).$wildcard.$quote;
        }
        else {
            return $quote.$quote;
        }
    }
}