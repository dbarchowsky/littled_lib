<?php

namespace Littled\Filters;

use Littled\Validation\RequestValidation;
use Littled\Validation\Validation;


class StringContentFilter extends ContentFilter
{
    /**
     * @inheritDoc
     */
    protected function collectRequestValue(?array $src=null): void
    {
        $this->value = Validation::collectStringRequestVar(
            $this->key,
            RequestValidation::DEFAULT_REQUEST_FILTER,
            null,
            $src);
    }
}