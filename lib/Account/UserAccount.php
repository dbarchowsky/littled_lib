<?php

namespace Littled\Account;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\PrimaryKeyInput;
use Littled\Request\StringPasswordField;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\StringTextField;
use Littled\Utility\Mailer;
use Exception;

/**
 * Class UserAccount
 * @package Littled\Account
 */
abstract class UserAccount extends SerializedContent
{
    /** Type of site content represented by user account records, as found in the site_content table. */
    const int                       SITE_SECTION_ID = 10;
    protected static string         $table_name = 'site_user';
    /** AES key used to encrypt passwords */
    protected static string         $aes_key = '';
    /** Name of variable holding record id value. */
    const string                    ID_KEY = 'suid';
    const string                    USERNAME_KEY = 'uaUsername';
    /** Name of variable holding password value for authentication purposes. */
    const string                    PASSWORD_KEY = 'supw';
    /** Name of variable holding requested access value. */
    const string                    ACCESS_KEY = 'suac';
    /** Account activation URI. */
    protected static string         $account_activation_uri = '';
    /** Email address to display for support issues. */
    protected static string         $contact_email = '';
    /** Registration notice email template path. */
    protected static string         $registration_notice_email_template = '';

    /** @var StringTextField Username/login. */
    public StringTextField          $uname;
    /** @var StringTextField Pointer to username/login property. */
    public StringTextField          $username;
    public StringPasswordField      $password;
    public StringPasswordField      $password_confirm;
    public Address                  $contact_info;
    public UserAccess               $access;
    public BooleanCheckbox          $email_opt_in;
    public BooleanCheckbox          $postal_opt_in;
    public IntegerInput             $contact_id;
    public string                   $fullname;
    protected string                $sender_name;

    /**
     * UserAccount constructor.
     * @param int|null $id (Optional) Record id value.
     */
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->id = new PrimaryKeyInput('Announcement id', self::ID_KEY, false);
        $this->uname = new StringTextField('User name', self::USERNAME_KEY, true, '', 50);
        $this->username = &$this->uname;
        $this->contact_info = (new Address())->withConnection($this);
        $this->email_opt_in = new BooleanCheckbox('Email Opt-In', 'sueo', false, false);
        $this->postal_opt_in = new BooleanCheckbox('Snail Mail Opt-In', 'suso', false, false);
        $this->password = new StringPasswordField('Password', self::PASSWORD_KEY, true, '', 256);
        $this->password_confirm = new StringPasswordField('Confirm password', 'uaPwdConfirm', false, '', 256);
        $this->password_confirm->is_database_field = false;
        $this->access = (new UserAccess())
            ->withConnection($this)
            ->setRecordId(UserAccess::NO_AUTHENTICATION);

        $this->contact_id = &$this->contact_info->id;
        $this->contact_info->first_name->required = false;
        $this->contact_info->last_name->required = false;
        $this->contact_info->organization->required = false;
        $this->contact_info->email->required = true;
        $this->contact_info->address1->required = false;
        $this->contact_info->city->required = false;
        $this->contact_info->zip->required = false;

        $this->fullname = '';
    }

    /**
     * Fills object properties from data stored in the current session.
     * @return void
     */
    public function collectFromSession(): void
    {
        if (isset($_SESSION[$this->id->key])) {
            $this->id->value = $_SESSION[$this->id->key];
        }
        if (isset($_SESSION[$this->contact_info->first_name->key])) {
            $this->contact_info->first_name->value = $_SESSION[$this->contact_info->first_name->key];
        }
        if (isset($_SESSION[$this->contact_info->last_name->key])) {
            $this->contact_info->last_name->value = $_SESSION[$this->contact_info->last_name->key];
        }
        if (isset($_SESSION[$this->contact_info->email->key])) {
            $this->contact_info->email->value = $_SESSION[$this->contact_info->email->key];
        }
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function formatCommitQuery(): array
    {
        $key = static::getAESKey();
        return array(
            'userAccountUpdate(@insert_id,?,?,?,?,?,?,?)',
            'ssiiiis',
            $this->username->value,
            $this->password->value,
            $this->contact_info->id->value,
            $this->access->id->value,
            $this->email_opt_in->value,
            $this->postal_opt_in->value,
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
        if (static::$account_activation_uri === '') {
            throw new ConfigurationUndefinedException('Account activation URI not configured.');
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
        if (static::$aes_key === '') {
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
        if (static::$account_activation_uri === '') {
            throw new ConfigurationUndefinedException('Contact email not configured.');
        }
        return (static::$contact_email);
    }

    /**
     * Getter for the registration notice email template path.
     * @returns string Registration notice email template path.
     * @throws ConfigurationUndefinedException
     */
    public static function getRegistrationNoticeEmailTemplate(): string
    {
        if (static::$registration_notice_email_template === '') {
            throw new ConfigurationUndefinedException('Registration notice email template path not configured.');
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
    public function hasRecordData(): bool
    {
        return $this->username->hasData() || $this->password->hasData();
    }

    /**
     * Sends a notification email to contact within the company to alert them that the registration has been submitted.
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function sendRegistrationNotificationEmail(): void
    {
        /* retrieve email template */
        $template_path = static::getRegistrationNoticeEmailTemplate();
        $f = fopen($template_path, 'r');

        /* email subject line. first line of email template */
        $subject = fgets($f);
        $subject = preg_replace("/\\[\\[subject:(.*)]]/i", '$1', $subject);

        $body = fread($f, filesize($template_path));
        fclose($f);

        $cms_uri = static::getAccountActivationURI() . '?' . static::ID_KEY . "={$this->id->value}";

        /* update email template with login data */
        $body = str_replace('[[username]]', $this->uname->value, $body);
        $body = str_replace('[[site_domain]]', LittledGlobals::getAppDomain(), $body);
        $body = str_replace('[[activate_url]]', $cms_uri, $body);

        (new Mailer())
            ->setSenderName($this->sender_name)
            ->setRecipient(static::getContactEmail())
            ->setSubject($subject)
            ->setBody($body)
            ->send();
    }

    /**
     * Setter for account activation uri.
     * @param string $uri Account activation uri.
     */
    public static function setAccountActivationURI(string $uri): void
    {
        static::$account_activation_uri = $uri;
    }

    /**
     * AES key setter.
     * @param string $key
     * @return void
     */
    public static function setAESKey(string $key): void
    {
        static::$aes_key = $key;
    }

    /**
     * Sets the contact email address for all instances of UserAccount class.
     * @param string $email New contact email address.
     */
    public static function setContactEmail(string $email): void
    {
        static::$contact_email = $email;
    }

    /**
     * Setter for a registration notice email template path.
     * @param string $path Registration notice email template path.
     * @throws ResourceNotFoundException
     */
    public static function setRegistrationNoticeEmailTemplate(string $path): void
    {
        if (!file_exists($path)) {
            throw new ResourceNotFoundException('Registration notice email template not found.');
        }
        static::$registration_notice_email_template = $path;
    }

    /**
     * Sender name setter.
     * @param string $name Name of password reset email sender.
     */
    public function setSenderName(string $name): void
    {
        $this->sender_name = $name;
    }

    /**
     * Validates form data submitted from the registration form.
     * Password is not entered during registration. It is assigned after the person has been approved.
     * Throws ContentValidationException if the form data is not valid, with the specific errors returned to the Exception's getMessage method.
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @throws ContentValidationException
     * @throws FailedQueryException
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        try {
            parent::validateInput($exclude_properties, $clear_existing);
        } catch (ContentValidationException) {
            /* continue */
        }
        if (!$this->contact_info->first_name->value &&
            !$this->contact_info->last_name->value &&
            !$this->contact_info->organization->value) {
            $this->addValidationError('Either first name and last name or company must be entered.');
        }
        if (!$this->contact_info->email->error) {
            $this->contact_info->validateUniqueEmail();
        }

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException('Error validating registration.');
        }
    }

    public abstract function validateUsername(): void;
}