<?php
namespace Tests\PageContent;

use Littled\PageContent\PageConfig;


class PageConfigTest extends \PHPUnit_Framework_TestCase
{
	public function testContentCSSClass()
	{
		$css_class = 'test-class';
		PageConfig::setContentCSSClass($css_class);
		$this->assertEquals($css_class, PageConfig::getContentCSSClass(), 'Content CSS class assignment');
	}

	public function testUnregisterStylesheets()
	{
		PageConfig::registerStylesheet("/path/to/a.css");
		$this->assertEquals(1, count(PageConfig::$stylesheets), "Count after 1 stylesheet");
		PageConfig::registerStylesheet("/path/to/b.css");
		PageConfig::registerStylesheet("/path/to/c.css");
		$this->assertEquals(3, count(PageConfig::$stylesheets), "Count after 3 stylesheets");
		PageConfig::unregisterStylesheet("/path/to/b.css");
		$this->assertEquals(2, count(PageConfig::$stylesheets), "Count after unregistering a stylesheet");
		PageConfig::unregisterStylesheet("/path/to/notincluded.css");
		$this->assertEquals(2, count(PageConfig::$stylesheets), "Count after unregistering an unknown stylesheet");
		PageConfig::registerStylesheet("/path/to/d.css");
		PageConfig::registerStylesheet("/path/to/b.css");
		$this->assertEquals(4, count(PageConfig::$stylesheets), "Count after 2 additional stylesheets");
		$this->assertEquals("/path/to/b.css", PageConfig::$stylesheets[3], "Last added is last in list");
	}

	public function testUnregisterScripts()
	{
		PageConfig::registerScript("/path/to/a.js");
		$this->assertEquals(1, count(PageConfig::$scripts), "Count after 1 script");
		PageConfig::registerScript("/path/to/b.js");
		PageConfig::registerScript("/path/to/c.js");
		$this->assertEquals(3, count(PageConfig::$scripts), "Count after 3 scripts");
		PageConfig::unregisterScript("/path/to/b.js");
		$this->assertEquals(2, count(PageConfig::$scripts), "Count after unregistering a script");
		PageConfig::unregisterScript("/path/to/notincluded.js");
		$this->assertEquals(2, count(PageConfig::$scripts), "Count after unregistering an unknown script");
		PageConfig::registerScript("/path/to/d.js");
		PageConfig::registerScript("/path/to/b.js");
		$this->assertEquals(4, count(PageConfig::$scripts), "Count after 2 additional scripts");
		$this->assertEquals("/path/to/b.js", PageConfig::$scripts[3], "Last added is last in list");
	}
}