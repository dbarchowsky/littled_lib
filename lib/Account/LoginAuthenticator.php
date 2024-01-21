<?php
namespace Littled\Account;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidCredentialsException;
use Littled\PageContent\PageUtils;
use Exception;
use Littled\Request\StringInput;


/**
 * Class LoginAuthenticator
 * Interface between app login form data and database.
 * @package Littled\Account
 */
class LoginAuthenticator extends UserLogin
{
    /** @var string Value to insert in login form */
    const LOGIN_ACTION = 'login';

    /** @var string URI of page containing login authentication form. */
    protected static string $login_uri='';

    /** @var bool Flag to allow overrides of login situations. */
	public bool $bypass_login=false;
	/** @var bool Flag indicating if the user is currently logged in on the site. */
	public bool $logged_in=false;
	/** @var StringInput URI to redirect to after successful login. */
	public StringInput $redirect_uri;

	/**
	 * LoginAuthenticator constructor.
	 * @param null|int $id Optional user account record id value.
	 */
	public function __construct(?int $id = null)
	{
		parent::__construct($id);
		$this->bypass_login = false;
		$this->logged_in = false;
		$this->redirect_uri = new StringInput('redirect uri', LittledGlobals::REFERER_KEY, false, '', 500);
	}

	/**
	 * Verifies login credentials using interal values of the login object.
	 * @param int $access_level (Optional) Token representing the level of access required to view the current page.
	 */
	public function authenticate(int $access_level=100)
	{
		if (!$this->uname->value || !$this->password->value || $this->access->value>$access_level)
		{
			$this->logged_in = false;
		}

		try {
            $this->collectRequestData();
            $this->validateOnDatabase($access_level);
        }
        catch (InvalidCredentialsException
            | ConfigurationUndefinedException
            | Exception $ex)
        {
            $this->addValidationError($ex->getMessage());
        }
	}

	/**
	 * Collects form data from login form.
	 * @param ?array $src (Optional) Collection of input data. If not specified, will read input from POST, GET,
     * Session vars.
	 */
	public function collectRequestData(?array $src=null ): void
	{
		$this->uname->collectRequestData();
		$this->password->collectRequestData();
        $this->redirect_uri->collectRequestData();
	}

    /**
     * Collects initial redirect uri value passed to the login page from the previous page. This is the URL to use to
     * redirect back to the source page after a successful login.
     */
	public function collectRedirectURI()
    {
        /* first test for value in query string */
        $this->redirect_uri->value = trim(filter_input(INPUT_GET, $this->redirect_uri->key, FILTER_SANITIZE_URL));
        /* then test for value in session data */
        if (!$this->redirect_uri->value) {
            $this->redirect_uri->value = isset($_SESSION[$this->redirect_uri->key]) && trim($_SESSION[$this->redirect_uri->key]) ? $_SESSION[$this->redirect_uri->key] : '';
        }
    }

	/**
	 * Login page URI getter.
	 * @return string Current login page URI value.
	 */
	public static function getLoginURI(): string
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
		$_SESSION[$this->contact_info->first_name->key] = $this->contact_info->first_name->value;
		$_SESSION[$this->contact_info->last_name->key] = $this->contact_info->last_name->value;
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
		unset($_SESSION[$this->contact_info->first_name->key]);
		unset($_SESSION[$this->contact_info->last_name->key]);
		$this->clearValues();
		$this->logged_in = false;
	}

	/**
	 * Checks to see if the user is currently signed in. If not, redirects to a login page.
	 * @param int $access_level (Optional) Token representing the level of access required to view the current page.
	 * @param string $msg (Optional) Message to be displayed with the login form.
	 * @throws ConfigurationUndefinedException
	 */
	public function requireLogin(int $access_level=100, string $msg='')
	{
		$login_uri = $this->getLoginURI();
		if ($login_uri === null | strlen($login_uri) < 1) {
			throw new ConfigurationUndefinedException("Login page URI not set.");
		}

		$this->validateOnSession($access_level);
		if (!$this->logged_in)
		{
			/* NB INPUT_SERVER is unreliable with filter_input() */
			$_SESSION[LittledGlobals::REFERER_KEY] = $_SERVER['PHP_SELF'].PageUtils::serializePageData();
			if ($msg) {
				$_SESSION[LittledGlobals::INFO_MESSAGE_KEY] = $msg;
			}
			header("Location: ".$this->getLoginURI()."\n\n");
			exit;
		}
	}

	/**
	 * Login page URI setter.
	 * @param string $uri Login page URI.
	 */
	public static function setLoginURI( string $uri )
	{
		static::$login_uri = $uri;
	}

	/**
	 * Validates form data submitted from login form.
	 * Throws exception if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @param string[] $exclude_properties (Optional) List of property names to exclude from validation.
	 * @throws ContentValidationException
	 */
	public function validateInput(array $exclude_properties=[])
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
	 * @param int $accessLevel Token representing the level of access required to view the current page.
	 * @throws Exception
     * @throws InvalidCredentialsException
     * @throws ConfigurationUndefinedException
	 */
	public function validateOnDatabase(int $accessLevel=100)
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
		$this->contact_info->first_name->value = $rs[0]->firstname;
		$this->contact_info->last_name->value = $rs[0]->lastname;
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
	public function validateOnSession(int $accessLevel=100)
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
