<?php
namespace Littled\Tests\PageContent;

use Littled\PageContent\PageConfig;


class PageConfigTest extends \PHPUnit_Framework_TestCase
{
	public function testContentCSSClass()
	{
		$css_class = 'test-class';
		PageConfig::setContentCSSClass($css_class);
		$this->assertEquals($css_class, PageConfig::getContentCSSClass(), 'Content CSS class assignment');
	}
}