<?php
namespace Littled\Account;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerSelect;
use Littled\Request\StringPasswordField;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\StringTextField;
use Exception;
use Littled\Utility\Mailer;

/**
 * Class UserAccount
 * @package Littled\Account
 */
abstract class UserAccount extends SerializedContent
{
	/** @var int Type of site content represented by user account records, as found in the site_content table. */
	const SITE_SECTION_ID        = 10;
	/** @var string */
	protected static string $table_name = 'site_user';
	/** @var string AES key used to encrypt passwords */
	protected static string $aes_key='';
	/** @var int Disabled value. */
	const AUTHENTICATION_UNRESTRICTED = 0;
	/** @var int Basic credentials token value. */
	const BASIC_AUTHENTICATION  = 1;
	/** @var int Admin credentials token value. */
	const ADMIN_AUTHENTICATION  = 2;
	/** @var string Name of variable holding record id value. */
	const ID_KEY                = "suid";
	const USERNAME_KEY          = "uaUsername";
	/** @var string Name of variable holding password value for authentication purposes. */
	const PASSWORD_KEY          = "supw";
	/** @var string Name of variable holding requested access value. */
	const ACCESS_KEY            = "suac";

	/** @var string Account activation URI. */
	protected static string $account_activation_uri  = '';
	/** @var string Email address to display for support issues. */
	protected static string $contact_email = '';
	/** @var string Registration notice email template path. */
	protected static string $registration_notice_email_template = '';

	/** @var IntegerInput Account record id. */
    public IntegerInput $id;
    /** @var StringTextField Username/login. */
    public StringTextField $uname;
    /** @var StringTextField Pointer to username/login property. */
    public StringTextField $username;
	public StringPasswordField $password;
	public StringPasswordField $password_confirm;
	public Address $contact_info;
	public IntegerSelect $access;
	/** @var BooleanCheckbox Flag allowing user account to opt in or out of email contact. */
    public BooleanCheckbox $email_opt_in;
    /** @var BooleanCheckbox Flag allowing user account to opt in or out of postal contact. */
    public BooleanCheckbox $postal_opt_in;
    /** @var IntegerInput Pointer to the record id of the contact information record linked to this user account. */
    public IntegerInput $contact_id;
    /** @var string Shortcut to the first and last name associated with the account. */
    public string $fullname;
	/** @var string Name of sender for password reset emails. */
	protected string $sender_name;

	/**
	 * UserAccount constructor.
	 * @param int|null $id (Optional) Record id value.
	 */
    public function __construct($id = null)
    {
	    parent::__construct($id);
	    $this->id = new IntegerInput("Announcement id", self::ID_KEY, false);
		$this->uname = new StringTextField("User name", self::USERNAME_KEY, true, '', 50);
		$this->username = &$this->uname;
	    $this->contact_info = new Address();
	    $this->email_opt_in = new BooleanCheckbox("Email Opt-In", "sueo", false, false);
	    $this->postal_opt_in = new BooleanCheckbox("Snail Mail Opt-In", "suso", false, false);
	    $this->password = new StringPasswordField("Password", self::PASSWORD_KEY, true, "", 256);
	    $this->password_confirm = new StringPasswordField("Confirm password", "uaPwdConfirm", false, "", 256);
		$this->password_confirm->is_database_field = false;
	    $this->access = new IntegerSelect("Access", self::ACCESS_KEY, true, self::BASIC_AUTHENTICATION);

	    $this->contact_id = &$this->contact_info->id;
	    $this->contact_info->first_name->required = false;
	    $this->contact_info->last_name->required = false;
	    $this->contact_info->company->required = false;
	    $this->contact_info->email->required = true;
	    $this->contact_info->address1->required = false;
	    $this->contact_info->city->required = false;
	    $this->contact_info->state_id->required = false;
	    $this->contact_info->zip->required = false;

	    $this->fullname = "";
    }

	/**
	 * Fills object properties from data stored in the current session.
	 * @return void
	 */
	public function collectFromSession ( )
	{
		if (isset($_SESSION[$this->id->key])) {
			$this->id->value=$_SESSION[$this->id->key];
		}
		if (isset($_SESSION[$this->contact_info->first_name->key])) {
			$this->contact_info->first_name->value=$_SESSION[$this->contact_info->first_name->key];
		}
		if (isset($_SESSION[$this->contact_info->last_name->key])) {
			$this->contact_info->last_name->value=$_SESSION[$this->contact_info->last_name->key];
		}
		if (isset($_SESSION[$this->contact_info->email->key])) {
			$this->contact_info->email->value=$_SESSION[$this->contact_info->email->key];
		}
	}

	/**
	 * @inheritDoc
	 * @throws ConfigurationUndefinedException
	 */
	public function generateUpdateQuery(): ?array
	{
		$key = static::getAESKey();
		return array(
			'userAccountUpdate(@record_id,?,?,?,?,?,?,?)',
			'ssiiiis',
			&$this->username->value,
			&$this->password->value,
			&$this->contact_info->id->value,
			&$this->access->value,
			&$this->email_opt_in->value,
			&$this->postal_opt_in->value,
			$key
		);
	}

	/**
	 * Getter for account activation uri.
	 * @returns string Account activation uri.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAccountActivationURI(): string
	{
		if (static::$account_activation_uri==='') {
			throw new ConfigurationUndefinedException("Account activation URI not configured.");
		}
		return (static::$account_activation_uri);
	}

	/**
	 * AES key getter.
	 * @return string
	 * @throws ConfigurationUndefinedException
	 */
	public static function getAESKey(): string
	{
		if (static::$aes_key==='') {
			throw new ConfigurationUndefinedException('Key not set.');
		}
		return static::$aes_key;
	}

	/**
	 * Gets the current contact email address.
	 * @return string Current contact email address.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getContactEmail(): string
	{
		if (static::$account_activation_uri==='')
		{
			throw new ConfigurationUndefinedException("Contact email not configured.");
		}
		return (static::$contact_email);
	}

	/**
	 * Getter for registration notice email template path.
	 * @returns string Registration notice email template path.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getRegistrationNoticeEmailTemplate(): string
	{
		if (static::$registration_notice_email_template==='')
		{
			throw new ConfigurationUndefinedException("Registration notice email template path not configured.");
		}
		return (static::$registration_notice_email_template);
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
	 * @inheritDoc
	 */
	public function hasData ( ): bool
	{
		return (($this->id->value!==null && $this->id->value>0) ||
			strlen(''.$this->username->value)>0 ||
			strlen(''.$this->password->value)>0);
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

		$cms_uri = self::getAccountActivationURI()."?".self::ID_KEY."={$this->id->value}";

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
		static::$account_activation_uri = $uri;
	}

	/**
	 * AES key setter.
	 * @param string $key
	 * @return void
	 */
	public static function setAESKey(string $key)
	{
		static::$aes_key = $key;
	}

	/**
	 * Sets the contact email address for all instances of UserAccount class.
	 * @param string $email New contact email address.
	 */
	public static function setContactEmail(string $email)
	{
		static::$contact_email = $email;
	}

	/**
	 * Setter for registration notice email template path.
	 * @param string $path Registration notice email template path.
	 * @throws ResourceNotFoundException
	 */
	public static function setRegistrationNoticeEmailTemplate(string $path)
	{
		if (!file_exists($path)) {
			throw new ResourceNotFoundException("Registration notice email template not found.");
		}
		static::$registration_notice_email_template = $path;
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
	 * Throws ContentValidationException if the form data is not valid, with the specific errors returned to the Exception's getMessage method.
	 * @param array $exclude_properties Optional array of properties to exclude from validation.
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
		if (!$this->contact_info->first_name->value &&
			!$this->contact_info->last_name->value &&
			!$this->contact_info->company->value) {
			$this->addValidationError("Either first name and last name or company must be entered.");
		}
		if (!$this->contact_info->email->error) {
			$this->contact_info->validateUniqueEmail();
		}

		if ($this->hasValidationErrors()) {
			throw new ContentValidationException("Error validating registration.");
		}
	}

	public abstract function validateUsername(): void;
}