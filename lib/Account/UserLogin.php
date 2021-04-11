<?php
namespace Littled\Account;


use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\PageContent\PageUtils;

class UserLogin extends UserAccount
{
    /** @var boolean Flag to allow overrides of login situations. */
    public $bypass_login;
    /** @var boolean Flag indicating that the user login is currently validated. */
    public $logged_in;

    /**
     * UserLogin constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->logged_in = false;
        $this->bypass_login = false;
    }

    /**
     * @param int $access_level Access level the login is requesting.
     */
    function authenticate(int $access_level)
    {
        try {
            $this->collectFromInput();
            $this->tryLogin($access_level);
            PageUtils::doRedirect(APP_HTTPS_ROOT_URI);
        } catch (ContentValidationException $ex) {
            $this->addValidationError($ex->getMessage());
        }
     }

    /**
     * Sets object and session state to indicate that the user is currently logged in.
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
        $this->clear();
        $this->logged_in = false;
    }

    /**
     * @param int $access_level Token representing access necessary to validate.
     * @throws ContentValidationException
     */
     function tryLogin(int $access_level)
     {
         if (!$this->uname->value || !$this->password->value || $this->access->value>$access_level)
         {
             $this->logged_in = false;
             throw new ContentValidationException("Invalid login.");
         }

         $this->validateLogin($access_level);
     }

    /**
     * @param int $access_level Token representing the access level the login is requesting.
     * @throws ContentValidationException
     */
     public function validateLogin(int $access_level)
     {
         $query = "SELECT l.id, c.firstname, c.lastname, c.email, l.access ".
             "FROM `site_user` l ".
             "INNER JOIN `address` c ON l.contact_id = c.id ".
             "WHERE (l.`login`=".$this->uname->escapeSQL($this->mysqli).") ".
             "AND (l.`password` = PASSWORD(".$this->password->escapeSQL($this->mysqli).")) ".
             "AND (l.access >= {$access_level}) ";
         try
         {
             $rs = $this->fetchRecords($query);
         }
         catch (InvalidQueryException $ex)
         {
             $this->logged_in = false;
             throw new ContentValidationException("Login error.");
         }

         if (count($rs) < 1)
         {
             /* invalid login */
             $this->logged_in = false;
             throw new ContentValidationException("Invalid login.");
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
     * @param int $access_level (Optional) Token representing the level of access required to view the current page.
     */
    public function validateOnSession($access_level=100 )
    {
        if (isset($_SESSION[$this->id->key]) &&
            ($_SESSION[$this->id->key]>0) &&
            isset($_SESSION[$this->uname->key]) &&
            (strlen($_SESSION[$this->uname->key])>0) &&
            isset($_SESSION[$this->password->key]) &&
            (strlen($_SESSION[$this->password->key])>0) &&
            ((isset($_SESSION[$this->access->key])) &&
            ($_SESSION[$this->access->key])>=$access_level)) {
            /* user islogged in for this session */
            $this->collectFromSession();
            $this->logged_in = true;
        }
    }
}