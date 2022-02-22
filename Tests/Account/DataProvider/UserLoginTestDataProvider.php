<?php

namespace Littled\Tests\Account\DataProvider;


use Littled\Account\UserAccount;

class UserLoginTestDataProvider
{
	public static function formatRequiresLoginMsg(string $username, string $password, ?int $access, ?int $requested_access)
	{
		$msg = 'username: '.($username?:'EMPTY').'; ';
		$msg .= 'password: '.($password?:'EMPTY').'; ';
		$msg .= 'access: '.(($access===null)?('NULL'):($access)).'; ';
		$msg .= 'requested access: '.(($requested_access===null)?('NULL'):($requested_access)).'; ';
		return $msg;
	}

	public static function requiresLoginTestProvider(): array
	{
		return array(
			['InvalidCredentialsException', '/not logged in/', '', '', null, UserAccount::DISABLED],
			['InvalidCredentialsException', '/not logged in/', 'v8', '', null, UserAccount::DISABLED],
			['InvalidCredentialsException', '/does not have access/', 'v8', 'v8pass', null, UserAccount::DISABLED],
			['InvalidCredentialsException', '/does not have access/', 'v8', 'v8pass', UserAccount::DISABLED, UserAccount::DISABLED],
			['InvalidCredentialsException', '/does not have access/', 'v8', 'v8pass', UserAccount::DISABLED, UserAccount::BASIC_AUTHENTICATION],
			['', '', 'v8', 'v8pass', UserAccount::BASIC_AUTHENTICATION, UserAccount::BASIC_AUTHENTICATION],
			['InvalidCredentialsException', '/does not have access/', 'v8', 'v8pass', UserAccount::BASIC_AUTHENTICATION, UserAccount::ADMIN_AUTHENTICATION],
			['', '', 'v8', 'v8pass', UserAccount::ADMIN_AUTHENTICATION, UserAccount::ADMIN_AUTHENTICATION],
			['', '', 'v8', 'v8pass', UserAccount::ADMIN_AUTHENTICATION, UserAccount::BASIC_AUTHENTICATION],
		);
	}
}