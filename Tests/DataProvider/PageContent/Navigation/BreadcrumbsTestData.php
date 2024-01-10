<?php

namespace LittledTests\DataProvider\PageContent\Navigation;


use Littled\PageContent\Navigation\Breadcrumbs;

class BreadcrumbsTestData
{
	public string       $expected;
	public Breadcrumbs  $menu;

	function __construct(string $expected, array $node_data=[])
	{
		$this->expected = $expected;
		$this->menu = new Breadcrumbs();
		foreach($node_data as $data) {
			$this->menu->addNode($data[0], $data[1]);
		}
	}
}