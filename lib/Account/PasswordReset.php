<?php

namespace Littled\Account;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageUtils;
use \Exception;
use Littled\Request\StringPasswordField;
use \mail_class;

class PasswordReset extends UserAccount
{
	/** @var string Modify account URI */
	protected static $modifyAccountURI = '';
	/** @var string Path to template for reset password email content. */
	protected static $resetPasswordEmailTemplate = '';

	/** @var StringPasswordField New password. */
	public $new_password;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->new_password = new StringPasswordField("New Password", "sunp", false, "", 256);
		$this->new_password->isDatabaseField = false;
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
	public static function getModifyAccountURI()
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
	public static function getResetPasswordEmailTemplate()
	{
		if (static::$resetPasswordEmailTemplate == '')
		{
			throw new ConfigurationUndefinedException("Reset password email template path not configured.");
		}
		return (static::$resetPasswordEmailTemplate);
	}

	/**
	 * Resets password to string of random characters.
	 * @throws ConfigurationUndefinedException
	 * @throws InvalidQueryException
	 */
	public function resetPassword ( )
	{
		if ($this->id->value===null || $this->id->value<1)
		{
			return;
		}

		$this->password->value = PageUtils::generateRandomFilename(12, false);
		$query = "UPDATE ".self::TABLE_NAME()." SET ".
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
		if ($this->contact_info->firstname->value) {
			$body = str_replace("[[greeting]]", "Dear {$this->contact_info->firstname->value},", $body);
		} else {
			$body = str_replace("[[greeting]]", "Hellow,", $body);
		}
		$body = str_replace("[[username]]", $this->uname->value, $body);
		$body = str_replace("[[password]]", $this->password->value, $body);
		$body = str_replace("[[account url]]", self::getAccountActivationURI(), $body);

		$mail = new mail_class(
			$this->sender_name,
			self::getContactEmail(),
			$this->contact_info->formatContactName(),
			$this->contact_info->email->value,
			$subject,
			$body
		);
		$mail->send();

		/* update the login object with the encrypted password */
		$query = "SEL"."ECT `password` FROM ".self::TABLE_NAME()." WHERE id = {$this->id->value}";
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
	public static function setModifyAccountURI($uri )
	{
		static::$modifyAccountURI = $uri;
	}

	/**
	 * Setter for reset password email template.
	 * @param string $path Path to reset password email template.
	 * @throws ResourceNotFoundException
	 */
	public static function setResetPasswordEmailTemplate( $path )
	{
		if (!file_exists($path))
		{
			throw new ResourceNotFoundException("Reset password email template not found.");
		}
		static::$resetPasswordEmailTemplate = $path;
	}

	/**
	 * Validates form data submitted from reset password form.
	 * @param array[optional] $exclude_properties List of variable names to exclude from validation.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 */
	public function validateInput($exclude_properties=[])
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
			$this->contact_info->firstname->value = $rs[0]->firstname;
			$this->contact_info->lastname->value = $rs[0]->lastname;
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
}