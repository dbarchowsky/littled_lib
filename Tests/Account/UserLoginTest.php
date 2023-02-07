<?php
namespace Littled\Tests\Account;

use Littled\Account\UserAccount;
use Littled\Account\UserLogin;
use Littled\Exception\InvalidCredentialsException;
use Littled\Tests\DataProvider\Account\UserLoginTestDataProvider;
use PHPUnit\Framework\TestCase;


class UserLoginTest extends TestCase
{
	/**
	 * @dataProvider \Littled\Tests\DataProvider\Account\UserLoginTestDataProvider::requiresLoginTestProvider()
	 * @param string $expected_exception
	 * @param string $expected_pattern
	 * @param string $username
	 * @param string $password
	 * @param int|null $access
	 * @param int|null $requested_access
	 * @return void
	 * @throws InvalidCredentialsException
	 */
	function testRequiresLogin(string $expected_exception='', string $expected_pattern='', string $username='', string $password='', ?int $access=null, ?int $requested_access=null)
	{
		$o = new UserLogin();
		if ($username) {
			$_SESSION[UserAccount::USERNAME_KEY] = $username;
		}
		if ($password) {
			$_SESSION[UserAccount::PASSWORD_KEY] = $password;
		}
		if ($access !== null) {
			$_SESSION[UserAccount::ACCESS_KEY] = $access;
		}

		if ($expected_exception) {
			try {
				$o->requiresLogin($requested_access);
			}
			catch(InvalidCredentialsException $e) {
				$this->assertMatchesRegularExpression($expected_pattern, $e->getMessage(), UserLoginTestDataProvider::formatRequiresLoginMsg($username, $password, $access, $requested_access));
			}
		}
		else {
			$o->requiresLogin($requested_access);
			$this->assertLessThanOrEqual($o->access->value, $requested_access, UserLoginTestDataProvider::formatRequiresLoginMsg($username, $password, $access, $requested_access));
		}
	}
}