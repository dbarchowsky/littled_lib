<?php
namespace Littled\Account;


use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextField;
use Littled\Request\StringPasswordField;

/**
 * Class UserAccount
 * @package Littled\Account
 */
class AjaxPage extends SerializedContent
{
    /** @var IntegerInput Account record id. */
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
    /** @var boolean Flag to allow overrides of login situations. */
    public $bypass_login;
    /** @var boolean Flag indicating if the user is currently logged in on the site. */
    public $logged_in;
    /** @var string Shortcut to the first and last name associated with the account. */
    public $fullname;
}