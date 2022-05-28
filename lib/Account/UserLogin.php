<?php

namespace Littled\Account;


use Exception;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidCredentialsException;
use Littled\Request\StringTextField;
use Littled\Request\StringPasswordField;

/**
 * Class UserLogin
 * User login interface between app and database.
 * @package Littled\Account
 */
class UserLogin extends UserAccount
{
	/** @var string */
	protected static string $login_uri='';

	/** @var StringTextField Pointer to username/login property. */
	public StringTextField $login;
	/** @var StringPasswordField Password confirmation for registration and account updates. */
	public StringPasswordField $new_password;

	function __construct($id = null)
	{
		parent::__construct($id);
		$this->login = &$this->username;
		$this->new_password = new StringPasswordField('New Password', 'newPswd', false, '', 50);
		$this->new_password->is_database_field = false;
	}

	/**
	 * Overrides parent routine to copy email value into username field.
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
		$this->username->value='';
		$this->password->value='';
		$this->access->value=self::DISABLED;
		if (isset($_SESSION[$this->username->key])) {
			$this->username->value=$_SESSION[$this->username->key];
		}
		if (isset($_SESSION[$this->password->key])) {
			$this->password->value=$_SESSION[$this->password->key];
		}
		if (isset($_SESSION[$this->access->key])) {
			$this->access->value=$_SESSION[$this->access->key];
		}
	}

	/**
	 * Login URI getter.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getLoginURI(): string
	{
		if (static::$login_uri==='') {
			throw new ConfigurationUndefinedException('Login URI value not set.');
		}
		return static::$login_uri;
	}

	/**
	 * @inheritDoc
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
	 * Checks the current login state. Throws InvalidCredentialsException if login state is not available or if the
	 * login state does not match the requested access level.
	 * @param int $access_level Access level needed for the login.
	 * @return void
	 * @throws InvalidCredentialsException
	 */
	public function requiresLogin(int $access_level)
	{
		$this->collectFromSession();
		if ($this->username->value==='' || $this->password->value==='') {
			throw new InvalidCredentialsException('User is not logged in.');
		}
		if ($this->access->value===null || $this->access->value<=UserAccount::DISABLED || $this->access->value < $access_level) {
			throw new InvalidCredentialsException('User does not have access.');
		}
	}

	/**
	 * Login URI setter.
	 * @param string $uri
	 * @return void
	 */
	public static function setLoginURI(string $uri)
	{
		static::$login_uri = $uri;
	}

	/**
	 * Validates form data submitted from registration form.
	 * Password is not entered during registration. It is assigned after the person has been approved.
	 * Throws ContentValidationException if the form data is not valid, with the specific errors returned to the Exception's getMessage method.
	 * @param array $exclude_properties Optional array of properties to exclude from validation.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 */
	public function validateInput(array $exclude_properties=[])
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
		} catch (Exception $ex) {
			$this->addValidationError($ex->getMessage());
			/* continue validating other properties */
		}

		if ($this->hasValidationErrors()) {
			throw new ContentValidationException("Error validating registration.");
		}
	}

	/**
	 * Looks up username in database to confirm that it is not already in use.
	 * Throws exception if the username is not valid, with the specific errors returned to the Exception's getMessage method.
	 * @throws ContentValidationException
	 * @throws Exception
	 */
	public function validateUsername(): void
	{
		$query = "SEL"."ECT id FROM `".static::getTableName()."` WHERE (`login` = ?)";
		$types_str = 's';
		$vars = [$this->username->value];
		if ($this->id->value>0) {
			$query .= "AND (id != ?)";
			$types_str .= 'i';
			$vars[]= $this->id->value;
		}
		array_unshift($vars, $query, $types_str);
		$data = call_user_func_array([$this, 'fetchRecords'], $vars);
		if (count($data) > 0) {
			throw new ContentValidationException("User name already exists.");
		}
	}
}