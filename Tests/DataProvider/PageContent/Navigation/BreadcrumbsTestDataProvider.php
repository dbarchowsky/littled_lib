<?php

namespace Littled\Tests\DataProvider\PageContent\Navigation;


class BreadcrumbsTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			array(new BreadcrumbsTestData('/<ul.*>\n*<\/ul>/')),
			array(new BreadcrumbsTestData('/<ul.*>\s*<li.*>node 01<\/li>\s*<\/ul>/', array(array('node 01', '')))),
			array(new BreadcrumbsTestData(
				'/<ul.*>\s*<li.*><a href=\"https:\/\/localhost\".*>node 01<\/a><\/li>\s*<\/ul>/',
				array(array('node 01', 'https://localhost')))
			),
			array(new BreadcrumbsTestData(
				'/<ul.*>\s*'.
				'<li.*><a href=\"https:\/\/localhost\".*>node 01<\/a><\/li>\s*'.
				'<li.*><a href=\"https:\/\/foo\.bar\".*>node two<\/a><\/li>\s*'.
				'<li.*>end node<\/li>\s*'.
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