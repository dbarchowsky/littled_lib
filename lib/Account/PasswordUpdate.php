<?php

namespace Littled\Account;

use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Request\StringPasswordField;

class PasswordUpdate extends UserAccount
{
    public StringPasswordField $new_password;

    /**
     * Validates new passwords using thees criteria:
     * - The password is different from the current password.
     * - The password matches the confirmation password.
     * - Both the password and confirmation password were entered in the form if they are required.
     * Throws exception if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @return void
     * @throws ContentValidationException
     * @throws FailedQueryException
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        if ($this->id->value > 0 && $this->password->value) {
            $query = 'SELECT id FROM ' . static::getTableName() . ' WHERE `password` = PASSWORD(?) AND id = ?';
            $rs = $this->fetchRecords($query, 'si', $this->password->value, $this->id->value);
            $found_match = (count($rs) > 0);

            if ($found_match === false) {
                throw new ContentValidationException('Invalid password.');
            }

            if ($this->new_password->value || $this->password_confirm->value) {
                $this->new_password->required = true;
                $this->password_confirm->required = true;

                try {
                    $this->new_password->validate();
                } catch (ContentValidationException) {
                    /* continue */
                }
                try {
                    $this->password_confirm->validate();
                } catch (ContentValidationException) {
                    /* continue */
                }

                if ($this->new_password->error || $this->password_confirm->error) {
                    $this->new_password->error = true;
                    $this->password_confirm->error = true;
                    $this->addValidationError('The new password must be confirmed by entering it twice');
                } else {
                    if ($this->password_confirm->value != $this->new_password->value) {
                        $this->new_password->error = true;
                        $this->password_confirm->error = true;
                        $this->addValidationError('The new passwords do not match.');
                    }
                }
            }
        } else {
            if ($this->password_confirm->value != $this->password->value) {
                $this->password->error = true;
                $this->password_confirm->error = true;
                $this->addValidationError('The passwords do not match.');
            }
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException('Password update errors found.');
        }
    }

    function getContentLabel(): string
    {
        // TODO: Implement getLabel() method.
        return '';
    }

    public function validateUsername(): void
    {
        // TODO: Implement validateUsername() method.
    }
}