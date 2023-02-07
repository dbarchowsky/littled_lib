<?php

namespace Littled\Tests\DataProvider\PageContent\Navigation;


use Littled\PageContent\Navigation\BreadcrumbsNode;

class BreadcrumbsNodeTestData
{
	public string           $expected;
	public BreadcrumbsNode  $node;

	function __construct(string $expected, string $label='', string $url='', string $dom_id='', string $css_class='')
	{
		$this->expected = $expected;
		$this->node = new BreadcrumbsNode($label, $url, $dom_id, $css_class);
	}
}