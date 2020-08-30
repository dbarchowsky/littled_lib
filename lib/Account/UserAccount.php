<?php
namespace Littled\Account;


<<<<<<< HEAD
=======
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ResourceNotFoundException;
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Littled\Request\StringPasswordField;
<<<<<<< HEAD
=======
use \Exception;
use \mail_class;
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe

/**
 * Class UserAccount
 * @package Littled\Account
 */
<<<<<<< HEAD
class AjaxPage extends SerializedContent
{
    /** @var IntegerInput Account record id. */
=======
class UserAccount extends SerializedContent
{
	/** @var int Disabled value. */
	const DISABLED = 0;
	/** @var int Basic credentials token value. */
	const BASIC_AUTHENTICATION = 1;
	/** @var int Admin credentials token value. */
	const ADMIN_AUTHENTICATION = 2;
	/** @var string Name of variable holding record id value. */
	const ID_PARAM = "suid";
	/** @var string Name of variable holding user name value for authentication purposes. */
	const USERNAME_PARAM = "sulg";
	/** @var string Name of variable holding password value for authentication purposes. */
	const PASSWORD_PARAM = "supw";
	/** @var string Name of variable holding requested access value. */
	const ACCESS_PARAM = "suac";
	/** @var int Type of site content represented by user account records, as found in the site_content table. */
	const SECTION_ID = 10;
	/** @var string Name of table holding user account records. */
	const TABLE_NAME = "site_user";

	/** @var string Account activation URI. */
	protected static $accountActivationURI  = '';
	/** @var string Email address to display for support issues. */
	protected static $contactEmail = '';
	/** @var string Registration notice email template path. */
	protected static $registrationNoticeEmailTemplate = '';


	/** @var IntegerInput Account record id. */
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
    public $id;
    /** @var StringTextField User name/login. */
    public $uname;
    /** @var StringTextField Pointer to username/login property. */
    public $username;
    /** @var StringTextField Pointer to username/login property. */
    public $login;
    /** @var StringPasswordField Account password. */
    public $password;
    /** @var StringPasswordField Password confirmation for registration and account updates. */
    public $password_confirm;
    /** @var Address Address information for the user account: name, street address, phone, etc. */
    public $contact_info;
    /** @var IntegerSelect Access level of this user account. */
    public $access;
    /** @var BooleanCheckbox Flag allowing user account to opt in or out of email contact. */
    public $email_opt_in;
    /** @var BooleanCheckbox Flag allowing user accout to opt in or out of postal contact. */
    public $postal_opt_in;
    /** @var IntegerInput Pointer to the record id of the contact information record linked to this user account. */
    public $contact_id;
<<<<<<< HEAD
    /** @var boolean Flag to allow overrides of login situations. */
    public $bypass_login;
    /** @var boolean Flag indicating if the user is currently logged in on the site. */
    public $logged_in;
    /** @var string Shortcut to the first and last name associated with the account. */
    public $fullname;
=======
    /** @var string Shortcut to the first and last name associated with the account. */
    public $fullname;
	/** @var string Name of sender for password reset emails. */
	protected $sender_name;

	/**
	 * UserAccount constructor.
	 * @param integer[optional] $id Record id value.
	 */
    public function __construct($id = null)
    {
	    parent::__construct($id);
	    $this->id = new IntegerInput("Announcement id", self::ID_PARAM, false);
	    $this->uname = new StringTextField("Username", self::USERNAME_PARAM, true, "", 50);
	    $this->password = new StringPasswordField("Password", self::PASSWORD_PARAM, true, "", 256);
	    $this->password_confirm = new StringPasswordField("Confirm Password", "sucp", true, "", 256);
	    $this->contact_info = new Address();
	    $this->access = new IntegerSelect("Access", self::ACCESS_PARAM, true, self::BASIC_AUTHENTICATION);
	    $this->email_opt_in = new BooleanCheckbox("Email Opt-In", "sueo", false, false);
	    $this->postal_opt_in = new BooleanCheckbox("Snail Mail Opt-In", "suso", false, false);

	    $this->contact_id = &$this->contact_info->id;
	    $this->username = &$this->uname;
	    $this->login = &$this->uname;

	    $this->password_confirm->isDatabaseField = false;
	    $this->contact_info->firstname->required = false;
	    $this->contact_info->lastname->required = false;
	    $this->contact_info->company->required = false;
	    $this->contact_info->email->required = true;
	    $this->contact_info->address1->required = false;
	    $this->contact_info->city->required = false;
	    $this->contact_info->state_id->required = false;
	    $this->contact_info->zip->required = false;

	    $this->fullname = "";
    }

	/**
	 * Static function returning the name of the table holding user account records.
	 * @return string|void Name of table holding user account records.
	 */
	public static function TABLE_NAME ()
	{
		return (self::TABLE_NAME);
	}

	/**
	 * Overrides parent routine to copy email value into user name field.
	 * @param array[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput($src=null)
	{
		parent::collectFromInput();
		$this->uname->value = $this->contact_info->email->value;
	}

	/**
	 * Fills object properties from data stored in the current session.
	 * @return void
	 */
	public function collectFromSession ( )
	{
		if (isset($_SESSION[$this->id->key]))
		{
			$this->id->value=$_SESSION[$this->id->key];
		}
		if (isset($_SESSION[$this->uname->key]))
		{
			$this->uname->value=$_SESSION[$this->uname->key];
		}
		if (isset($_SESSION[$this->password->key]))
		{
			$this->password->value=$_SESSION[$this->password->key];
		}
		if (isset($_SESSION[$this->access->key]))
		{
			$this->access->value=$_SESSION[$this->access->key];
		}
		if (isset($_SESSION[$this->contact_info->firstname->key]))
		{
			$this->contact_info->firstname->value=$_SESSION[$this->contact_info->firstname->key];
		}
		if (isset($_SESSION[$this->contact_info->lastname->key]))
		{
			$this->contact_info->lastname->value=$_SESSION[$this->contact_info->lastname->key];
		}
		if (isset($_SESSION[$this->contact_info->email->key]))
		{
			$this->contact_info->email->value=$_SESSION[$this->contact_info->email->key];
		}
	}

	/**
	 * Getter for account activation uri.
	 * @returns string Account activation uri.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAccountActivationURI()
	{
		if (static::$accountActivationURI == '')
		{
			throw new ConfigurationUndefinedException("Account activation URI not configured.");
		}
		return (static::$accountActivationURI);
	}

	/**
	 * Gets the current contact email address.
	 * @return string Current contact email address.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getContactEmail()
	{
		if (static::$accountActivationURI == '')
		{
			throw new ConfigurationUndefinedException("Contact email not configured.");
		}
		return (static::$contactEmail);
	}

	/**
	 * Getter for registration notice email template path.
	 * @returns string Registration notice email template path.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getRegistrationNoticeEmailTemplate()
	{
		if (static::$registrationNoticeEmailTemplate == '')
		{
			throw new ConfigurationUndefinedException("Registration notice email template path not configured.");
		}
		return (static::$registrationNoticeEmailTemplate);
	}

	/**
	 * Sender name getter.
	 * @return string Password reset email sender name.
	 */
	public function getSenderName()
	{
		return ($this->sender_name);
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return boolean Returns true if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData ( )
	{
		return (
			$this->id->value!==null ||
			strlen($this->uname->value)>0 ||
			strlen($this->password->value)>0
		);
	}

	/**
	 * Sends notification email to contact within company to alert them that the registration has been submitted.
	 * @throws Exception
	 */
	public function sendRegistrationNotificationEmail()
	{
		/* retrieve email template */
		$template_path = self::getRegistrationNoticeEmailTemplate();
		$f = fopen($template_path, "r");

		/* email subject line. first line of email template */
		$subject = fgets($f);
		$subject = preg_replace("/\\[\\[subject:(.*)]]/i", "$1", $subject);

		$body = fread($f, filesize($template_path));
		fclose($f);

		$cms_uri = self::getAccountActivationURI()."?".self::ID_PARAM."={$this->id->value}";

		/* update email template with login data */
		$body = str_replace("[[username]]", $this->uname->value, $body);
		$body = str_replace("[[site_domain]]", LittledGlobals::getAppDomain(), $body);
		$body = str_replace("[[activate_url]]", $cms_uri, $body);

		/* send out notification email */
		$mail = new mail_class(
			$this->sender_name,
			self::getContactEmail(),
			self::getContactEmail(),
			self::getContactEmail(),
			"",
			$subject,
			$body);
		$mail->send();
	}

	/**
	 * Setter for account activation uri.
	 * @param string $uri Account activation uri.
	 */
	public static function setAccountActivationURI($uri)
	{
		static::$accountActivationURI = $uri;
	}

	/**
	 * Sets the contact email address for all instances of UserAccount class.
	 * @param string $email New contact email address.
	 */
	public function setContactEmail($email)
	{
		static::$contactEmail = $email;
	}

	/**
	 * Setter for registration notice email template path.
	 * @param string $path Registration notice email template path.
	 * @throws ResourceNotFoundException
	 */
	public static function setRegistrationNoticeEmailTemplate($path)
	{
		if (!file_exists($path))
		{
			throw new ResourceNotFoundException("Registration notice email template not found.");
		}
		static::$registrationNoticeEmailTemplate = $path;
	}

	/**
	 * Sender name setter.
	 * @param string $name Name of password reset email sender.
	 */
	public function setSenderName( $name )
	{
		$this->sender_name = $name;
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
		$this->password->required = false;
		$this->password_confirm->required = false;

		try {
			parent::validateInput();
		} catch (ContentValidationException $ex) {
			/* continue */
		}
		try {
			$this->validateUserName();
		} catch (ContentValidationException $ex) {
			$this->addValidationError($ex->getMessage());
			/* continue validating other properties */
		}
		if (!$this->contact_info->firstname->value &&
			!$this->contact_info->lastname->value &&
			!$this->contact_info->company->value) {
			$this->addValidationError("Either first name and last name or company must be entered.");
		}
		if (!$this->contact_info->email->error)
		{
			$this->contact_info->validateUniqueEmail();
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
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
}