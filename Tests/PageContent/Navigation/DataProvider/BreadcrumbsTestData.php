<?php

namespace Littled\Tests\PageContent\Navigation\DataProvider;


use Littled\PageContent\Navigation\Breadcrumbs;

class BreadcrumbsTestData
{
	/** @var string */
	public $expected;
	/** @var Breadcrumbs */
	public $menu;

	function __construct(string $expected, array $node_data=[])
	{
		$this->expected = $expected;
		$this->menu = new Breadcrumbs();
		foreach($node_data as $data) {
			$this->menu->addNode($data[0], $data[1]);
		}
	}
}