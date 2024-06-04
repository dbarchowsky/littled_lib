<?php

namespace Littled\Request;

use Littled\Validation\Validation;

class PhoneNumberTextField extends StringTextField
{
    protected const US_REGEX = '/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/';
    protected const E164_REGEX = '/^\+[1-9]\d{1,14}$/';

    /**
     * @inheritDoc
     */
    public function validate(): void
    {
        parent::validate();
        if (!Validation::isStringBlank($this->value)) {
            if (!preg_match(self::US_REGEX, $this->value) && !$this->validateInternationalNumber()) {
                $this->throwValidationError($this->formatErrorLabel() . ' is not in a recognized format.');
            }
        }
    }

    /**
     * Validates the current value stored in the object as an international phone number in E.164 format. Returns TRUE
     * or FALSE depending on whether the value is in a recognized format.
     * @return bool
     */
    protected function validateInternationalNumber(): bool
    {
        $value = str_replace([' ', '-'], '', $this->value);
        return preg_match(self::E164_REGEX, $value);
    }
}