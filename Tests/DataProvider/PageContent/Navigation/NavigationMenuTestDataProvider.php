<?php

namespace Littled\Tests\DataProvider\PageContent\Navigation;


class NavigationMenuTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			array(new NavigationMenuTestData('/<ul.*>\n*<\/ul>/')),
			array(new NavigationMenuTestData('/<ul.*>\s*<li.*><a href=\"#\" rel=\"nofollow\">node 01<\/a><\/li>\s*<\/ul>/', array(array('node 01', '')))),
			array(new NavigationMenuTestData(
				'/<ul.*>\s*<li.*><a href=\"https:\/\/localhost\".*>node 01<\/a><\/li>\s*<\/ul>/',
				array(array('node 01', 'https://localhost')))
			),
			array(new NavigationMenuTestData(
				'/<ul.*>\s*'.
				'<li.*><a href=\"https:\/\/localhost\".*>node 01<\/a><\/li>\s*'.
				'<li.*><a href=\"https:\/\/foo\.bar\".*>node two<\/a><\/li>\s*'.
				'<li.*><a href=\"#\".*>end node<\/a><\/li>\s*'.
				'<\/ul>/',
				array(
					array('node 01', 'https://localhost'),
					array('node two', 'https://foo.bar'),
					array('end node', ''),
					)
				)
			),
		);
	}
}