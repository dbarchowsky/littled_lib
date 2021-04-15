<?php

namespace Littled\Account;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;

class PasswordUpdate extends UserAccount
{
	/**
	 * Validates new passwords to make sure that the password is different from the current password, that it matches the confirmation password, and that both the password and confirmation password were entered in the form in situations where they are both required.
	 * Throws exception if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @param array[optional] $exclude_properties Associative list of properties to exclude from validation.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 */
	public function validateInput ($exclude_properties=[])
	{
		if ($this->id->value>0 && $this->password->value)
		{
			$this->connectToDatabase();
			$query = "SELECT id FROM ".self::TABLE_NAME()." WHERE (`password` = PASSWORD(".$this->password->escapeSQL($this->mysqli).")) AND (id = {$this->id->value}) ";
			$rs = $this->fetchRecords($query);
			$found_match = (count($rs) > 0);

			if ($found_match===false)
			{
				throw new ContentValidationException("Invalid password.");
			}

			if ($this->new_password->value || $this->password_confirm->value)
			{
				$this->new_password->required = true;
				$this->password_confirm->required = true;

				try {
					$this->new_password->validate();
				} catch (ContentValidationException $e) {
					/* continue */
				}
				try {
					$this->password_confirm->validate();
				} catch (ContentValidationException $e) {
					/* continue */
				}

				if ($this->new_password->error || $this->password_confirm->error)
				{
					$this->new_password->error = true;
					$this->password_confirm->error = true;
					$this->addValidationError("The new password must be confirmed by entering it twice");
				}

				else
				{
					if ($this->password_confirm->value != $this->new_password->value)
					{
						$this->new_password->error = true;
						$this->password_confirm->error = true;
						$this->addValidationError("The new passwords do not match.");
					}
				}
			}
		}
		else
		{
			if ($this->password_confirm->value != $this->password->value)
			{
				$this->password->error = true;
				$this->password_confirm->error = true;
				$this->addValidationError("The passwords do not match.");
			}
		}
		if ($this->hasValidationErrors())
		{
			throw new ContentValidationException("Password update errors found.");
		}
	}
}