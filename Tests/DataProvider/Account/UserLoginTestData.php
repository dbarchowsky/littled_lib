<?php
namespace Littled\Tests\DataProvider\Account;

use Littled\Account\UserLogin;


class UserLoginTestData
{
	public bool $expected;
	public string $msg;
	public UserLogin $obj;

	public function __construct(
		bool $expected,
		?int $id=null,
		?string $username='',
		?string $password='',
		?string $password_confirm='',
		string $msg='')
	{
		$this->expected = $expected;
		$this->msg = $msg;
		$this->obj = new UserLogin();
		$this->obj->id->value = $id;
		$this->obj->username->value = $username;
		$this->obj->password->value = $password;
		$this->obj->password_confirm->value = $password_confirm;
	}
}