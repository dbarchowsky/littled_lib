<?php

namespace Littled\Tests\PageContent\Navigation\DataProvider;


use Littled\PageContent\Navigation\BreadcrumbsNode;

class BreadcrumbsNodeTestData
{
	/** @var string */
	public $expected;
	/** @var BreadcrumbsNode */
	public $node;

	function __construct(string $expected, string $label='', string $url='', string $dom_id='', string $css_class='')
	{
		$this->expected = $expected;
		$this->node = new BreadcrumbsNode($label, $url, $dom_id, $css_class);
	}
}