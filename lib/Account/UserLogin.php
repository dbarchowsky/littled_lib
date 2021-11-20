<?php

namespace Littled\Account;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Littled\Request\StringPasswordField;

/**
 * Class UserLogin
 * User login interface between app and database.
 * @package Littled\Account
 */
class UserLogin extends UserAccount
{
	/** @var string Name of variable holding user name value for authentication purposes. */
	const USERNAME_PARAM = "sulg";
	/** @var string Name of variable holding password value for authentication purposes. */
	const PASSWORD_PARAM = "supw";
	/** @var string Name of variable holding requested access value. */
	const ACCESS_PARAM = "suac";

	/** @var int Basic credentials token value. */
	const BASIC_AUTHENTICATION = 1;
	/** @var int Admin credentials token value. */
	const ADMIN_AUTHENTICATION = 2;

	/** @var StringTextField Pointer to username/login property. */
	public $login;
	/** @var StringPasswordField Account password. */
	public $password;
	/** @var IntegerSelect Access level of this user account. */
	public $access;
	/** @var StringPasswordField Password confirmation for registration and account updates. */
	public $password_confirm;
	/** @var StringPasswordField $new_password Container for new password form input. */
	public $new_password;
	/* @var StringTextField Alias for $login class property */
	public $uname;
	/* @var StringTextField Alias for $login class property */
	public $username;

	function __construct($id = null)
	{
		parent::__construct($id);
		$this->login = new StringTextField("Username", self::USERNAME_PARAM, true, "", 50);
		$this->password = new StringPasswordField("Password", self::PASSWORD_PARAM, true, "", 256);
		$this->access = new IntegerSelect("Access", self::ACCESS_PARAM, true, self::BASIC_AUTHENTICATION);
		$this->password_confirm = new StringPasswordField("Confirm Password", "pwdConfirm", true, "", 256);
		$this->new_password = new StringPasswordField('New Password', 'newPswd', false, '', 50);

		$this->uname = &$this->login;
		$this->username = &$this->login;

		$this->password_confirm->isDatabaseField = false;
		$this->new_password->isDatabaseField = false;
	}

	/**
	 * Overrides parent routine to copy email value into user name field.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectRequestData(?array $src=null): void
	{
		parent::collectRequestData();
		$this->username->value = $this->contact_info->email->value;
	}

	/**
	 * Collect object property values from session data.
	 * Adds login properties to parent routine.
	 */
	public function collectFromSession()
	{
		parent::collectFromSession();
		if (isset($_SESSION[$this->username->key]))
		{
			$this->username->value=$_SESSION[$this->username->key];
		}
		if (isset($_SESSION[$this->password->key]))
		{
			$this->password->value=$_SESSION[$this->password->key];
		}
		if (isset($_SESSION[$this->access->key]))
		{
			$this->access->value=$_SESSION[$this->access->key];
		}
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool  Returns true if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData(): bool
	{
		return (
			parent::hasData() ||
			strlen($this->username->value)>0 ||
			strlen($this->password->value)>0
		);
	}

	/**
	 * Validates form data submitted from registration form.
	 * Password is not entered during registration. It is assigned after the person has been approved.
	 * Throws ContentValidationException if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @param array[optional] $exclude_properties Optional array of properties to exclude from validation.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 */
	public function validateInput($exclude_properties=[])
	{
		try {
			parent::validateInput();
		} catch (ContentValidationException $ex) {
			/* continue */
		}

		$this->password->required = false;
		$this->password_confirm->required = false;

		try {
			$this->validateUserName();
		} catch (ContentValidationException $ex) {
			$this->addValidationError($ex->getMessage());
			/* continue validating other properties */
		}

		if ($this->hasValidationErrors()) {
			throw new ContentValidationException("Error validating registration.");
		}
	}

	/**
	 * Looks up username in database to confirm that it is not already in use.
	 * Throws exception if the username is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	public function validateUserName()
	{
		$this->connectToDatabase();

		$query = "SELECT id FROM `site_user` ".
			"WHERE (`login` = ".$this->uname->escapeSQL($this->mysqli).") ";
		if ($this->id->value>0)
		{
			$query .= "AND (id != {$this->id->value})";
		}

		$rs = $this->fetchRecords($query);
		if (count($rs) > 0) {
			throw new ContentValidationException("User name already exists.");
		}
	}
}