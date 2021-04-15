<?php
namespace Littled\Account;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidCredentialsException;
use Littled\PageContent\PageUtils;
use Exception;


/**
 * Class LoginAuthenticator
 * Interface between app login form data and database.
 * @package Littled\Account
 */
class LoginAuthenticator extends UserLogin
{
	/** @var boolean Flag to allow overrides of login situations. */
	public $bypass_login;
	/** @var boolean Flag indicating if the user is currently logged in on the site. */
	public $logged_in;
	/** @var string URI of page containing login authentication form. */
	protected static $login_uri;

	/**
	 * LoginAuthenticator constructor.
	 * @param null[optional] $id Optional user account record id value.
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->bypass_login = false;
		$this->logged_in = false;
	}

	/**
	 * Collects form data from login form.
	 * @param array[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput( $src=null )
	{
		$this->uname->collectPostData();
		$this->password->collectPostData();
	}

	/**
	 * Login page URI getter.
	 * @return ?string Current login page URI value.
	 */
	public function getLoginURI(): ?string
	{
		return static::$login_uri;
	}

	/**
	 * Sets object and session state to indicate that the user is currently logged in.
	 * @return void
	 */
	public function login()
	{
		$_SESSION[$this->id->key] = $this->id->value;
		$_SESSION[$this->uname->key] = $this->uname->value;
		$_SESSION[$this->password->key] = $this->password->value;
		$_SESSION[$this->access->key] = $this->access->value;
		$_SESSION[$this->contact_info->email->key] = $this->contact_info->email->value;
		$_SESSION[$this->contact_info->firstname->key] = $this->contact_info->firstname->value;
		$_SESSION[$this->contact_info->lastname->key] = $this->contact_info->lastname->value;
		$this->logged_in = true;
	}

	/**
	 * Sets object and session state to indicate that the user is currently logged out.
	 * @return void
	 */
	public function logout()
	{
		/* load session variables */
		$this->validateOnSession(0);

		/* clear session variables */
		unset($_SESSION[$this->id->key]);
		unset($_SESSION[$this->uname->key]);
		unset($_SESSION[$this->password->key]);
		unset($_SESSION[$this->access->key]);
		unset($_SESSION[$this->contact_info->email->key]);
		unset($_SESSION[$this->contact_info->firstname->key]);
		unset($_SESSION[$this->contact_info->lastname->key]);
		$this->clearValues();
		$this->logged_in = false;
	}

	/**
	 * Checks to see if the user is currently signed in. If not, redirects to a login page.
	 * @param int $access_level (Optional) Token representing the level of access required to view the current page.
	 * @param string $msg (Optional) Message to be displayed with the login form.
	 * @throws ConfigurationUndefinedException
	 */
	public function requireLogin($access_level=100, $msg="" )
	{
		$login_uri = $this->getLoginURI();
		if ($login_uri === null | strlen($login_uri) < 1) {
			throw new ConfigurationUndefinedException("Login page URI not set.");
		}

		$this->validateOnSession($access_level);
		if (!$this->logged_in)
		{
			/* NB INPUT_SERVER is unreliable with filter_input() */
			$_SESSION[LittledGlobals::P_REFERER] = $_SERVER['PHP_SELF'].PageUtils::serializePageData();
			if ($msg) {
				$_SESSION[LittledGlobals::P_MESSAGE] = $msg;
			}
			header("Location: ".$this->getLoginURI()."\n\n");
			exit;
		}
	}

	/**
	 * Login page URI setter.
	 * @param string $uri Login page URI.
	 */
	public function setLoginURI( string $uri )
	{
		static::$login_uri = $uri;
	}

	/**
	 * Verifies login credentials using interal values of the login object.
	 * @param int $access_level (Optional) Token representing the level of access required to view the current page.
	 * @throws ContentValidationException
	 * @throws Exception
	 */
	public function tryLogin($access_level=100)
	{
		if (!$this->uname->value || !$this->password->value || $this->access->value>$access_level)
		{
			$this->logged_in = false;
			throw new ContentValidationException("Invalid login.");
		}

		$this->uname->collectPostData();
		$this->password->collectPostData();
		$this->validateOnDatabase($access_level);
	}

	/**
	 * Validates form data submitted from login form.
	 * Throws exception if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @param array $exclude_properties (Optional) List of property names to exclude from validation.
	 * @throws ContentValidationException
	 */
	public function validateInput($exclude_properties=[])
	{
		try
		{
			$this->uname->validate();
		}
		catch(ContentValidationException $e)
		{
			/* continue */
		}
		try
		{
			$this->password->validate();
		}
		catch(ContentValidationException $e)
		{
			/* continue */
		}
		if ($this->hasValidationErrors())
		{
			throw new ContentValidationException("Login failed.");
		}
	}

	/**
	 * Looks up user in database to confirm that the login and password match an existing and valid login record.
	 * Sets the values of the object's logged_in property to indicate if valid login settings were detected.
	 * Logs the user in if a valid database record is found.
	 * @param int[optional] $accessLevel Token representing the level of access required to view the current page.
	 * @throws Exception
	 */
	public function validateOnDatabase( $accessLevel=100 )
	{
		$this->connectToDatabase();
		$query = "SELECT l.id, c.firstname, c.lastname, c.email, l.access ".
			"FROM `site_user` l ".
			"INNER JOIN `address` c ON l.contact_id = c.id ".
			"WHERE (l.`login`=".$this->uname->escapeSQL($this->mysqli).") ".
			"AND (l.`password` = PASSWORD(".$this->password->escapeSQL($this->mysqli).")) ".
			"AND (l.access >= $accessLevel) ";
		try
		{
			$rs = $this->fetchRecords($query);
		}
		catch (Exception $ex)
		{
			$this->logged_in = false;
			throw new InvalidCredentialsException("Login error.");
		}

		if (count($rs) < 1)
		{
			/* invalid login */
			$this->logged_in = false;
			throw new InvalidCredentialsException("Invalid login.");
		}

		/* store account properties that are saved in session variables */
		$this->id->value = $rs[0]->id;
		$this->contact_info->firstname->value = $rs[0]->firstname;
		$this->contact_info->lastname->value = $rs[0]->lastname;
		$this->contact_info->email->value = $rs[0]->email;
		$this->access->value = $rs[0]->access;

		$this->login();
	}

	/**
	 * Checks session variables to see if the user is currently logged in.
	 * Sets the values of the object's logged_in property to indicate if valid login settings were detected.
	 * @param int $accessLevel (Optional) Token representing the level of access required to view the current page.
	 * @return void
	 */
	public function validateOnSession( $accessLevel=100 )
	{
		if (isset($_SESSION[$this->id->key]) && ($_SESSION[$this->id->key]>0) &&
			isset($_SESSION[$this->uname->key]) && (strlen($_SESSION[$this->uname->key])>0) &&
			isset($_SESSION[$this->password->key]) && (strlen($_SESSION[$this->password->key])>0) &&
			((isset($_SESSION[$this->access->key])) && ($_SESSION[$this->access->key])>=$accessLevel)) {
			/* user is logged in for this session */
			$this->collectFromSession();
			$this->logged_in = true;
		}
	}

	/**
	 * Validate password when creating a new login to meet minimum security requirements.
	 * @throws ContentValidationException
	 */
	public function validatePassword()
	{
		$this->password->error = "Invalid password.";
		$e = get_class($this)."::validatePassword() not implemented.";
		$this->addValidationError($e);
		throw new ContentValidationException($e);
	}
}
