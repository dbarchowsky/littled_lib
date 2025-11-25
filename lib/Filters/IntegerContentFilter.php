<?php

namespace Littled\Filters;

use Littled\Validation\Validation;


class IntegerContentFilter extends ContentFilter
{
    /**
     * Collects the filter value from request variables, session variables, or cookie variables, in that order.
     */
    protected function collectRequestValue(?array $src = null): void
    {
        $this->value = Validation::collectIntegerRequestVar($this->key, null, $src);
    }
}