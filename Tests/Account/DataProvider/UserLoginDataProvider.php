<?php
namespace Littled\Tests\Account\DataProvider;


class UserLoginDataProvider
{
	public static function hasDataTestProvider(): array
	{
		return array(
			[new UserLoginTestData(false)],
			[new UserLoginTestData(false, null, null, null, null, 'All values set to null')],
			[new UserLoginTestData(false, 0, null, null, null, 'id value set to zero')],
			[new UserLoginTestData(true, 999999, '', '', '', 'id value set to non-zero')],
			[new UserLoginTestData(true, null, 'damien', '', '', 'username value set')],
			[new UserLoginTestData(true, null, '', 'secret', '', 'password value set')],
			[new UserLoginTestData(false, null, '', '', '123pswd', 'password confirm value set')],
			[new UserLoginTestData(true, 999999, 'damien', 'secret', '123pass', 'multiple values set')],
		);
	}
}