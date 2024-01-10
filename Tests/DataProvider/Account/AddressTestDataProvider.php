<?php
namespace LittledTests\DataProvider\Account;


class AddressTestDataProvider
{
	public static function hasAddressDataTestProvider(): array
	{
		return array(
			[new AddressTestData(false)],
			[new AddressTestData(false, null, null, null, null, null, null, null, null, null, 'All values set to null')],
			[new AddressTestData(false, 0, '', '', '', '', '', 0, '', '', 'All values set to empty string')],
			[new AddressTestData(false, 0, 'damien', '', '', '', '', null, '', '', 'first name')],
			[new AddressTestData(false, 0, '', 'barchowsky', '', '', '', null, '', '', 'last name')],
			[new AddressTestData(true, 0, '', '', '123 main street', '', '', null, '', '', 'address line 1')],
			[new AddressTestData(true, 0, '', '', '', 'apartment 103', '', null, '', '', 'address line 2')],
			[new AddressTestData(true, 0, 'damien', '', '', '', 'Burbank', null, '', '', 'city')],
			[new AddressTestData(true, 0, '', '', '', '', '', 11, '', '', 'state id')],
			[new AddressTestData(true, 0, '', '', '', '', '', null, 'province', '', 'state')],
			[new AddressTestData(true, 0, '', '', '', '', '', null, '', '99199', 'zip')],
		);
	}
}