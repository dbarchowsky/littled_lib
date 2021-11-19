<?php
namespace Littled\Tests\PageContent;

use Littled\PageContent\Metadata\MetadataElement;
use Littled\PageContent\PageConfig;
use PHPUnit\Framework\TestCase;


class PageConfigTest extends TestCase
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
		$this->assertCount(1, PageConfig::$stylesheets, "Count after 1 stylesheet");
		PageConfig::registerStylesheet("/path/to/b.css");
		PageConfig::registerStylesheet("/path/to/c.css");
		$this->assertCount(3, PageConfig::$stylesheets, "Count after 3 stylesheets");
		PageConfig::unregisterStylesheet("/path/to/b.css");
		$this->assertCount(2, PageConfig::$stylesheets, "Count after unregistering a stylesheet");
		PageConfig::unregisterStylesheet("/path/to/not_included.css");
		$this->assertCount(2, PageConfig::$stylesheets, "Count after unregistering an unknown stylesheet");
		PageConfig::registerStylesheet("/path/to/d.css");
		PageConfig::registerStylesheet("/path/to/b.css");
		$this->assertCount(4, PageConfig::$stylesheets, "Count after 2 additional stylesheets");
		$this->assertEquals("/path/to/b.css", PageConfig::$stylesheets[3], "Last added is last in list");
	}

	public function testUnregisterScripts()
	{
		PageConfig::registerScript("/path/to/a.js");
		$this->assertCount(1, PageConfig::$scripts, "Count after 1 script");
		PageConfig::registerScript("/path/to/b.js");
		PageConfig::registerScript("/path/to/c.js");
		$this->assertCount(3, PageConfig::$scripts, "Count after 3 scripts");
		PageConfig::unregisterScript("/path/to/b.js");
		$this->assertCount(2, PageConfig::$scripts, "Count after unregistering a script");
		PageConfig::unregisterScript("/path/to/not_included.js");
		$this->assertCount(2, PageConfig::$scripts, "Count after unregistering an unknown script");
		PageConfig::registerScript("/path/to/d.js");
		PageConfig::registerScript("/path/to/b.js");
		$this->assertCount(4, PageConfig::$scripts, "Count after 2 additional scripts");
		$this->assertEquals("/path/to/b.js", PageConfig::$scripts[3], "Last added is last in list");
	}

    public function testAddPageMetadata()
    {
        PageConfig::addPageMetadata('name', 'test', 'test value');
        $md = PageConfig::getPageMetadata();
        $this->assertIsArray($md);
        $this->assertCount(1, $md);
        /** @var MetadataElement $md[0] */
        $this->assertEquals('name', $md[0]->getType());
        $this->assertEquals('test', $md[0]->getName());
        $this->assertEquals('test value', $md[0]->getContent());
    }
}