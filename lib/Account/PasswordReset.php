<?php

namespace Littled\Account;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageUtils;
use Exception;
use Littled\Request\StringPasswordField;
use Littled\Utility\Mailer;

class PasswordReset extends UserAccount
{
	/** @var string Modify account URI */
	protected static string $modifyAccountURI = '';
	/** @var string Path to template for reset password email content. */
	protected static string $resetPasswordEmailTemplate = '';

	/** @var StringPasswordField New password. */
	public StringPasswordField $new_password;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->new_password = new StringPasswordField("New Password", "sunp", false, "", 256);
		$this->new_password->is_database_field = false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function collectRequestData ($src=null)
	{
		$this->contact_info->email->collectRequestData();
	}

	/**
	 * Getter for modify account uri.
	 * @return string Modify account uri.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getModifyAccountURI(): string
	{
		if (static::$modifyAccountURI == '')
		{
			throw new ConfigurationUndefinedException("Modify account URI not configured.");
		}
		return (static::$modifyAccountURI);
	}

	/**
	 * Getter for reset password email template path.
	 * @return string Reset password email template path.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getResetPasswordEmailTemplate(): string
	{
		if (static::$resetPasswordEmailTemplate == '')
		{
			throw new ConfigurationUndefinedException("Reset password email template path not configured.");
		}
		return (static::$resetPasswordEmailTemplate);
	}

    /**
     * Resets password to string of random characters.
     * @throws ConfigurationUndefinedException|NotImplementedException
     * @throws Exception
     */
	public function resetPassword ( )
	{
		if ($this->id->value===null || $this->id->value<1)
		{
			return;
		}

		$this->password->value = PageUtils::generateRandomFilename(12, false);
		$query = "UPDATE ".self::getTableName()." SET ".
			"`password` = PASSWORD('{$this->password->value}') ".
			"WHERE id = {$this->id->value}";
		$this->query($query);

		$this->sendPasswordResetNotificationEmail();
	}

	/**
	 * Sends email to user with new password.
	 * @throws ConfigurationUndefinedException
	 * @throws Exception
	 */
	public function sendPasswordResetNotificationEmail()
	{
		if (!$this->sender_name) {
			throw new ConfigurationUndefinedException("Password reset sender name is not specified.");
		}

		/* retrieve email template */
		$path = $this->getResetPasswordEmailTemplate();
		$f = fopen($path, "r");

		/* email subject line. first line of email template */
		$subject = fgets($f);
		$subject = preg_replace("/\[\[subject:(.*)]]/i", "$1", $subject);

		$body = fread($f, filesize($path));
		fclose($f);

		/* update email template with login data */
		if ($this->contact_info->first_name->value) {
			$body = str_replace("[[greeting]]", "Dear {$this->contact_info->first_name->value},", $body);
		} else {
			$body = str_replace("[[greeting]]", "Hellow,", $body);
		}
		$body = str_replace("[[username]]", $this->uname->value, $body);
		$body = str_replace("[[password]]", $this->password->value, $body);
		$body = str_replace("[[account url]]", self::getAccountActivationuri(), $body);

		$mail = new Mailer(
			$this->sender_name,
			self::getContactEmail(),
			$this->contact_info->formatContactName(),
			$this->contact_info->email->value,
			$subject,
			$body
		);
		$mail->send();

		/* update the login object with the encrypted password */
		$query = "SEL"."ECT `password` FROM ".self::getTableName()." WHERE id = {$this->id->value}";
		$rs = $this->fetchRecords($query);
		if (count($rs) > 0)
		{
			list($this->password->value) = $rs[0];
		}
		else
		{
			throw new Exception("Temporary password could not be retrieved.");
		}
	}

	/**
	 * Setter for modify account uri
	 * @param string $uri Modify account uri
	 */
	public static function setModifyAccountURI(string $uri)
	{
		static::$modifyAccountURI = $uri;
	}

	/**
	 * Setter for reset password email template.
	 * @param string $path Path to reset password email template.
	 * @throws ResourceNotFoundException
	 */
	public static function setResetPasswordEmailTemplate(string $path)
	{
		if (!file_exists($path))
		{
			throw new ResourceNotFoundException("Reset password email template not found.");
		}
		static::$resetPasswordEmailTemplate = $path;
	}

	/**
	 * Validates form data submitted from reset password form.
	 * @param string[] $exclude_properties List of variable names to exclude from validation.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws Exception
	 */
	public function validateInput(array $exclude_properties=[])
	{
		$this->connectToDatabase();

		try
		{
			$this->contact_info->email->validate();
		}
		catch(ContentValidationException $e)
		{
			$this->addValidationError($e->getMessage());
			throw new ContentValidationException("Errors found in password reset information.");
		}

		$query = "SELECT l.id, l.`login`, c.firstname, c.lastname ".
			"FROM `site_user` l ".
			"INNER JOIN `address` c ON l.contact_id = c.id ".
			"WHERE (c.email = ".$this->contact_info->email->escapeSQL($this->mysqli).") ";
		$rs = $this->fetchRecords($query);
		if (count($rs) > 0)
		{
			$this->id->value = $rs[0]->id;
			$this->uname->value = $rs[0]->login;
			$this->contact_info->first_name->value = $rs[0]->firstname;
			$this->contact_info->last_name->value = $rs[0]->lastname;
			$this->contact_info->fullname = $rs[0]->firstname." ".$rs[0]->lastname;
		}
		else
		{
			$this->contact_info->email->error = true;
			$this->addValidationError("The mail address does not match an existing account");
		}

		if ($this->hasValidationErrors()) {
			throw new ContentValidationException("Errors found in password reset information.");
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