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
    protected function collectRequestValue(?array $src=null)
    {
        $this->value = Validation::collectStringRequestVar(
            $this->key,
            Validation::DEFAULT_REQUEST_FILTER,
            null,
            $src);
    }

    /**
     * @inheritDoc
     */
    public function escapeSQL(mysqli $mysqli, bool $include_quotes=true, bool $include_wildcards=true): ?string
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