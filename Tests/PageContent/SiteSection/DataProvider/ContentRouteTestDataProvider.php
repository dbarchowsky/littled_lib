<?php

namespace Littled\Tests\PageContent\SiteSection\DataProvider;


class ContentRouteTestDataProvider
{
	public static function hasDataTestProvider(): array
	{
		return array(
			[false, null, null, '', ''],
			[false, null, 6037, '', ''],
			[true, 99, null, '', ''],
			[true, null, null, 'test operation', ''],
			[true, null, null, '', 'https://localhost'],
			[true, null, 6037, '', 'https://localhost'],
			[true, 99, 6037, '', 'https://localhost'],
			[true, 99, 6037, 'foobar', 'https://localhost'],
		);
	}
}