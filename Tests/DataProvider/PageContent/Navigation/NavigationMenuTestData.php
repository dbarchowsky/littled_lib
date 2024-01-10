<?php

namespace LittledTests\DataProvider\PageContent\Navigation;


use Littled\PageContent\Navigation\NavigationMenu;

class NavigationMenuTestData
{
	public string           $expected;
	public NavigationMenu   $menu;

	function __construct(string $expected, array $node_data=[])
	{
		$this->expected = $expected;
		$this->menu = new NavigationMenu();
		foreach($node_data as $data) {
			$this->menu->addNode($data[0], $data[1]);
		}
	}
}