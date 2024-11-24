<?php

namespace Littled\Validation;


class EmailValidation extends PhoneNumberValidation
{
    /**
     * Validates email address.
     * @param string $email Email address to validate
     * @return bool True if the email is in a valid format
     */
    public static function validateEmailAddress(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}