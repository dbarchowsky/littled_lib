<?php

namespace Littled\Tests\PageContent\Navigation\DataProvider;


use Littled\PageContent\Navigation\BreadcrumbsNode;
use Littled\PageContent\Navigation\NavigationMenuNode;

class NavigationMenuNodeTestData
{
	/** @var string */
	public $expected;
	/** @var BreadcrumbsNode */
	public $node;

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