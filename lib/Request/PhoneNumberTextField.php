<?php

namespace Littled\Request;

use Littled\Exception\InvalidValueException;
use Littled\Validation\Validation;


class PhoneNumberTextField extends StringTextField
{
    public function __construct(
        string $label = 'Phone number',
        string $key = 'phNo',
        bool $required = false,
        ?string $value = null,
        int $size_limit = 26,
        ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * @inheritDoc
     */
    public function validate(): void
    {
        parent::validate();
        if (!Validation::isStringBlank($this->value)) {
            try {
                Validation::isPhoneNumber($this->value);
            }
            catch (InvalidValueException $e) {
                $this->throwValidationError($this->formatErrorLabel() . ' ' . $e->getMessage());
            }
        }
    }
}