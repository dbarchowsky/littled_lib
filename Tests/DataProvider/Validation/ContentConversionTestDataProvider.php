<?php

namespace Littled\Tests\DataProvider\Validation;


class ContentConversionTestDataProvider
{
	public static function formatIndexMarkupProvider(): array
	{
		return array(
			['', null],
			['[0]', 0],
			['[12]', 12],
			["['foo']", 'foo'],
		);
	}
}