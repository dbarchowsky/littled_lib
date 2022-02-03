<?php

namespace Littled\Tests\PageContent\Navigation\DataProvider;


class NavigationMenuNodeTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			array(new NavigationMenuNodeTestData('/<li><a href=\"#\" rel=\"nofollow\">My Label<\/a><\/li>/', 'My Label')),
			array(new NavigationMenuNodeTestData('/<li><a href=\"htt'.'ps:\/\/localhost\/foo\">My Label<\/a><\/li>/', 'My Label', 'https://localhost/foo')),
			array(new NavigationMenuNodeTestData('/<li id=\"my-id\"><a.*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', 'my-id')),
			array(new NavigationMenuNodeTestData('/<li id=\"my-id\" class="my-class"><a.*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', 'my-id', 'my-class')),
			array(new NavigationMenuNodeTestData('/<li class="my-class"><a.*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', '', 'my-class')),
			array(new NavigationMenuNodeTestData('/<li><a.* title=\"my title\".*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', '', '', 'my title')),
			array(new NavigationMenuNodeTestData('/<li><a.* target=\"theTarget\".*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', '', '', '', 'theTarget')),
			array(new NavigationMenuNodeTestData('/<li><a href="https:\/\/localhost">.*<\/a><\/li>/', 'My Label', 'https://localhost', '', '', '', '', 2)),
			array(new NavigationMenuNodeTestData('/<li><a.* data-aid=\"88\" data-bid=\"bash\">.*<\/a><\/li>/', 'My Label', 'https://localhost', '', '', '', '', 0, 'data-aid="88" data-bid="bash"')),
		);
	}
}