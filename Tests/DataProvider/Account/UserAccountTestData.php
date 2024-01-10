<?php
namespace LittledTests\DataProvider\Account;

use LittledTests\TestHarness\Account\UserAccountTestHarness;
use Littled\Account\UserAccount;


class UserAccountTestData
{
	public bool $expected;
	public string $msg;
	public UserAccount $obj;

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
		$this->obj = new UserAccountTestHarness();
		$this->obj->id->value = $id;
		$this->obj->username->value = $username;
		$this->obj->password->value = $password;
		$this->obj->password_confirm->value = $password_confirm;
	}
}