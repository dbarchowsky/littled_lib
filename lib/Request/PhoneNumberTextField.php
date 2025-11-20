<?php

namespace Littled\Request;

use Littled\Exception\InvalidValueException;
use Littled\Validation\Validation;


class PhoneNumberTextField extends StringTextField
{
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