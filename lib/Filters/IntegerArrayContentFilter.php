<?php

namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;


/**
 * Class IntegerArrayContentFilter
 * @package Littled\Filters
 */
class IntegerArrayContentFilter extends IntegerContentFilter
{
    /**
     * @inheritDoc
     */
    protected function collectRequestValue(?array $src = null): void
    {
        $this->value = Validation::collectIntegerArrayRequestVar($this->key, $src);
    }

    /**
     * Returns string containing the list of values stored in the filter delimited with commas by default. The delimiter can be overridden using the $delimiter argument.
     * @param string $delimiter Character or string that will separate the values in the string. Comma by default.
     * @return string
     */
    public function formatValuesString(string $delimiter = ','): string
    {
        return implode($delimiter, $this->value);
    }

    /**
     * Output markup that will preserve the filter's value in an HTML form.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function saveInForm(): void
    {
        if (!is_array($this->value)) {
            return;
        }
        if (!defined('LITTLED_TEMPLATE_DIR')) {
            throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not found in app settings.");
        }
        foreach ($this->value as $value) {
            ContentUtils::renderTemplate(LITTLED_TEMPLATE_DIR . "framework/forms/hidden-input.php", array(
                'key' => $this->key,
                'index' => '[]',
                'value' => $value
            ));
        }
    }
}