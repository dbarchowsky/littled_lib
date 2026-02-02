<?php

namespace Littled\Account;


class AdminAuthenticator extends LoginAuthenticator
{
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->access->id->value = UserAccess::ADMIN_AUTHENTICATION;
    }
}