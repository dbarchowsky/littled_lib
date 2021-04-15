<?php
namespace Littled\Account;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Social\Mailer;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\StringTextField;
use Exception;

/**
 * Class UserAccount
 * @package Littled\Account
 */
class UserAccount extends SerializedContent
{
	/** @var int Disabled value. */
	const DISABLED = 0;
	/** @var string Name of variable holding record id value. */
	const ID_PARAM = "suid";
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
    public $id;
    /** @var StringTextField User name/login. */
    public $uname;
    /** @var StringTextField Pointer to username/login property. */
    public $username;
    /** @var Address Address information for the user account: name, street address, phone, etc. */
    public $contact_info;
    /** @var BooleanCheckbox Flag allowing user account to opt in or out of email contact. */
    public $email_opt_in;
    /** @var BooleanCheckbox Flag allowing user accout to opt in or out of postal contact. */
    public $postal_opt_in;
    /** @var IntegerInput Pointer to the record id of the contact information record linked to this user account. */
    public $contact_id;
    /** @var string Shortcut to the first and last name associated with the account. */
    public $fullname;
	/** @var string Name of sender for password reset emails. */
	protected $sender_name;

	/**
	 * UserAccount constructor.
	 * @param int|null $id (Optional) Record id value.
	 */
    public function __construct($id = null)
    {
	    parent::__construct($id);
	    $this->id = new IntegerInput("Announcement id", self::ID_PARAM, false);
	    $this->contact_info = new Address();
	    $this->email_opt_in = new BooleanCheckbox("Email Opt-In", "sueo", false, false);
	    $this->postal_opt_in = new BooleanCheckbox("Snail Mail Opt-In", "suso", false, false);

	    $this->contact_id = &$this->contact_info->id;
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
	 * @return string Name of table holding user account records.
	 */
	public static function TABLE_NAME(): string
	{
		return (self::TABLE_NAME);
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
	public static function getAccountActivationURI(): string
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
	public static function getContactEmail(): string
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
	public static function getRegistrationNoticeEmailTemplate(): string
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
	public function getSenderName(): string
	{
		return ($this->sender_name);
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool  Returns true if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData ( ): bool
	{
		return ($this->id->value!==null);
	}

	/**
	 * Sends notification email to contact within company to alert them that the registration has been submitted.
     * @throws ConfigurationUndefinedException
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
		$mail = new Mailer(
			$this->sender_name,
			self::getContactEmail(),
			self::getContactEmail(),
			self::getContactEmail(),
			"",
			$subject,
			$body);
		$mail->send();
		unset($mail);
	}

	/**
	 * Setter for account activation uri.
	 * @param string $uri Account activation uri.
	 */
	public static function setAccountActivationURI(string $uri)
	{
		static::$accountActivationURI = $uri;
	}

	/**
	 * Sets the contact email address for all instances of UserAccount class.
	 * @param string $email New contact email address.
	 */
	public function setContactEmail(string $email)
	{
		static::$contactEmail = $email;
	}

	/**
	 * Setter for registration notice email template path.
	 * @param string $path Registration notice email template path.
	 * @throws ResourceNotFoundException
	 */
	public static function setRegistrationNoticeEmailTemplate(string $path)
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
	public function setSenderName( string $name )
	{
		$this->sender_name = $name;
	}

	/**
	 * Validates form data submitted from registration form.
	 * Password is not entered during registration. It is assigned after the person has been approved.
	 * Throws ContentValidationException if the form data is not valid, with the specific errors returned in the Exception's getMessage method.
	 * @param array $exclude_properties Optional array of properties to exclude from validation.
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
}