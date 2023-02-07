<?php
namespace Littled\Tests\DataProvider\PageContent\Navigation;

use Littled\PageContent\Navigation\NavigationMenuNode;


class NavigationMenuNodeTestData
{
	public string               $expected;
	public NavigationMenuNode   $node;

	function __construct(
		string $expected,
		string $label='',
		string $url='',
		string $dom_id='',
		string $css_class='',
		string $title='',
		string $target='',
		int $level=0,
		string $attributes='')
	{
		$this->expected = $expected;
		$this->node = new NavigationMenuNode($label, $url, $title, $target, $level, $dom_id, $attributes);
		$this->node->css_class = $css_class;
	}
}