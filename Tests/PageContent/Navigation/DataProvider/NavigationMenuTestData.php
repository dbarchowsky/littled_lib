<?php

namespace Littled\Tests\PageContent\Navigation\DataProvider;


use Littled\PageContent\Navigation\NavigationMenu;

class NavigationMenuTestData
{
	/** @var string */
	public $expected;
	/** @var NavigationMenu */
	public $menu;

	function __construct(string $expected, array $node_data=[])
	{
		$this->expected = $expected;
		$this->menu = new NavigationMenu();
		foreach($node_data as $data) {
			$this->menu->addNode($data[0], $data[1]);
		}
	}
}