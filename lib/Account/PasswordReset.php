<?php

namespace Littled\Account;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageUtils;
use Exception;
use Littled\Request\StringPasswordField;
use Littled\Utility\Mailer;

class PasswordReset extends UserAccount
{
    /** @var string Modify account URI */
    protected static string $modifyAccountURI = '';
    /** @var string Path to the template for reset password email content. */
    protected static string $resetPasswordEmailTemplate = '';
    /** @var StringPasswordField New password. */
    public StringPasswordField $new_password;

    /**
     * {@inheritDoc}
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->new_password = new StringPasswordField('New Password', 'sunp', false, '', 256);
        $this->new_password->is_database_field = false;
    }

    /**
     * {@inheritDoc}
     */
    public function collectRequestData($src = null): void
    {
        $this->contact_info->email->collectRequestData();
    }

    /**
     * Getter for modified account uri.
     * @return string Modify account uri.
     * @throws ConfigurationUndefinedException
     */
    public static function getModifyAccountURI(): string
    {
        if (static::$modifyAccountURI == '') {
            throw new ConfigurationUndefinedException('Modify account URI not configured.');
        }
        return (static::$modifyAccountURI);
    }

    /**
     * Getter for a reset password email template path.
     * @return string Reset password email template path.
     * @throws ConfigurationUndefinedException
     */
    public static function getResetPasswordEmailTemplate(): string
    {
        if (static::$resetPasswordEmailTemplate == '') {
            throw new ConfigurationUndefinedException('Reset password email template path not configured.');
        }
        return (static::$resetPasswordEmailTemplate);
    }

    /**
     * Resets password to a string of random characters.
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function resetPassword(): void
    {
        if ($this->id->value === null || $this->id->value < 1) {
            return;
        }

        $this->password->value = PageUtils::generateRandomFilename(12, false);
        $query = 'UPDATE ' . static::getTableName() . ' SET ' .
            "`password` = PASSWORD('{$this->password->value}') " .
            "WHERE id = {$this->id->value}";
        $this->query($query);

        $this->sendPasswordResetNotificationEmail();
    }

    /**
     * Sends email to user with new password.
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function sendPasswordResetNotificationEmail(): void
    {
        if (!$this->sender_name) {
            throw new ConfigurationUndefinedException('Password reset sender name is not specified.');
        }

        /* retrieve email template */
        $path = $this->getResetPasswordEmailTemplate();
        $f = fopen($path, 'r');

        /* email subject line. first line of email template */
        $subject = fgets($f);
        $subject = preg_replace('/\[\[subject:(.*)]]/i', '$1', $subject);

        $body = fread($f, filesize($path));
        fclose($f);

        /* update email template with login data */
        if ($this->contact_info->first_name->value) {
            $body = str_replace('[[greeting]]', "Dear {$this->contact_info->first_name->value},", $body);
        } else {
            $body = str_replace('[[greeting]]', 'Hello,', $body);
        }
        $body = str_replace('[[username]]', $this->uname->value, $body);
        $body = str_replace('[[password]]', $this->password->value, $body);
        $body = str_replace('[[account url]]', self::getAccountActivationuri(), $body);

        (new Mailer())
            ->setRecipient($this->contact_info->email->value, $this->contact_info->formatContactName())
            ->setSenderName($this->sender_name)
            ->setSubject($subject)
            ->setBody($body)
            ->send();

        /* update the login object with the encrypted password */
        $query = 'SELECT `password` FROM ' . static::getTableName() . ' WHERE id = ?';
        $rs = $this->fetchRecords($query, 'i', $this->id->value);
        if (count($rs) > 0) {
            list($this->password->value) = $rs[0];
        } else {
            throw new Exception('Temporary password could not be retrieved.');
        }
    }

    /**
     * Setter for modified account uri
     * @param string $uri Modify account uri
     */
    public static function setModifyAccountURI(string $uri): void
    {
        static::$modifyAccountURI = $uri;
    }

    /**
     * Setter for reset password email template.
     * @param string $path Path to reset password email template.
     * @throws ResourceNotFoundException
     */
    public static function setResetPasswordEmailTemplate(string $path): void
    {
        if (!file_exists($path)) {
            throw new ResourceNotFoundException('Reset password email template not found.');
        }
        static::$resetPasswordEmailTemplate = $path;
    }

    /**
     * Validates form data submitted from the reset password form.
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws Exception
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        $this->connectToDatabase();

        try {
            $this->contact_info->email->validate();
        } catch (ContentValidationException $e) {
            $this->addValidationError($e->getMessage());
            throw new ContentValidationException('Errors found in password reset information.');
        }

        $query = 'SELECT l.id, l.`login`, c.firstname, c.lastname ' .
            'FROM `site_user` l ' .
            'INNER JOIN `address` c ON l.contact_id = c.id ' .
            'WHERE c.email = ?';
        $rs = $this->fetchRecords($query, 's', $this->contact_info->email->value);
        if (count($rs) > 0) {
            $this->id->value = $rs[0]->id;
            $this->uname->value = $rs[0]->login;
            $this->contact_info->first_name->value = $rs[0]->firstname;
            $this->contact_info->last_name->value = $rs[0]->lastname;
            $this->contact_info->fullname = $rs[0]->firstname . ' ' . $rs[0]->lastname;
        } else {
            $this->contact_info->email->error = true;
            $this->addValidationError('The mail address does not match an existing account');
        }

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException('Errors found in password reset information.');
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