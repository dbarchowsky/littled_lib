<?php

namespace Littled\Filters;

use Littled\Validation\Validation;


class BooleanContentFilter extends ContentFilter
{
    /**
     * @inheritDoc
     */
    protected function collectRequestValue(?array $src = null): void
    {
        $this->value = Validation::collectBooleanRequestVar($this->key, null, $src);
    }

    /**
     * @inheritDoc
     */
    public function collectValue(bool $read_cookies = true, ?array $src = null): void
    {
        $this->collectRequestValue($src);
        // have to override this test in the parent method because "false" is a valid value in this type of filter
        if ($this->value === true || $this->value === false) {
            return;
        }

        $this->collectValueFromSession();
        if ($this->value === true || $this->value === false) {
            return;
        }

        if ($read_cookies) {
            $this->collectValueFromCookie();
        }
    }

    /**
     * @inheritDoc
     */
    public function formatQueryString(): string
    {
        if ($this->value === true || $this->value === 1) {
            return $this->key . '=1';
        }
        if ($this->value === false || $this->value === 0) {
            return $this->key . '=0';
        }
        return '';
    }
}
