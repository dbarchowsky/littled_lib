<?php

namespace Littled\Tests\DataProvider\PageContent\Navigation;


class BreadcrumbsNodeTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			array(new BreadcrumbsNodeTestData('/<li class=\"page-title\">My Label<\/li>/', 'My Label')),
			array(new BreadcrumbsNodeTestData('/<li class=\"page-title\"><a href=\"htt'.'ps:\/\/localhost\/foo\">My Label<\/a><\/li>/', 'My Label', 'https://localhost/foo')),
			array(new BreadcrumbsNodeTestData('/<li class=\"page-title\" id=\"my-id\"><a.*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', 'my-id')),
			array(new BreadcrumbsNodeTestData('/<li class=\"my-special-class\" id=\"my-id\"><a.*>.*<\/a><\/li>/', 'My Label', 'https://localhost/foo', 'my-id', 'my-special-class')),
		);
	}
}