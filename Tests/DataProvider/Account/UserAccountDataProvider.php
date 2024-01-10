<?php
namespace LittledTests\DataProvider\Account;


class UserAccountDataProvider
{
	public static function hasDataTestProvider(): array
	{
		return array(
			[new UserAccountTestData(false)],
			[new UserAccountTestData(false, null, null, null, null, 'All values set to null')],
			[new UserAccountTestData(false, 0, null, null, null, 'id value set to zero')],
			[new UserAccountTestData(true, 999999, '', '', '', 'id value set to non-zero')],
			[new UserAccountTestData(true, null, 'damien', '', '', 'username value set')],
			[new UserAccountTestData(true, null, '', 'secret', '', 'password value set')],
			[new UserAccountTestData(false, null, '', '', '123pswd', 'password confirm value set')],
			[new UserAccountTestData(true, 999999, 'damien', 'secret', '123pass', 'multiple values set')],
		);
	}
}