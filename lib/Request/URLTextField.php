<?php

namespace Littled\Request;


use Littled\Exception\ContentValidationException;

/**
 * Class URLTextFieldInput
 * @package Littled\Request
 */
class URLTextField extends StringTextField
{
    /**
     * Override parent to set a default field length of 255 characters. This can be overridden if needed.
     * @@inheritDoc
     */
    function __construct(
        string $label,
        string $key,
        bool $required = false,
        ?string $value = null,
        int $size_limit = 255,
        int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * @inheritDoc
     */
    public function render(string $label = '', string $css_class = '', array $context = []): void
    {
        /** TODO mark the form input as having type="url" */
        parent::render($label, $css_class);
    }

    /**
     * @inheritDoc
     */
    public function setInputValue(mixed $value): URLTextField
    {
        $this->value = filter_var(strip_tags('' . $value), FILTER_SANITIZE_URL);
        return $this;
    }

    /**
     * Validates the value entered into the form to ensure that it is a valid URL. If the value is accepted, the
     * $value property of the object is updated with the filtered URL value.
     * @throws ContentValidationException
     */
    public function validate(): void
    {
        $value = filter_var($this->value, FILTER_VALIDATE_URL);
        if ($value === false) {
            $this->throwValidationError(ucfirst($this->label) . ' does not appear to be a valid URL.');
        }
        $this->value = filter_var($value, FILTER_SANITIZE_URL);
    }
}