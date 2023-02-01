<?php
namespace Littled\Tests\PageContent;

use Littled\App\LittledGlobals;
use Littled\PageContent\Metadata\MetadataElement;
use Littled\PageContent\PageConfig;
use Littled\PageContent\Metadata\Preload;
use PHPUnit\Framework\TestCase;


class PageConfigTest extends TestCase
{
	function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		LittledGlobals::setLocalTemplatesPath(SHARED_CMS_TEMPLATE_DIR);
	}

	public function testAddUtilityLink()
    {
        $link1 = array('label' => 'label-one', 'url' => '/url-one');
        $link2 = array('label' => 'label-two', 'url' => '/url-two');
        PageConfig::addUtilityLink($link1['label'], $link1['url']);

        $menu = PageConfig::getUtilityLinks();
        $this->assertEquals(1, $menu->getNodeCount());

        $node = PageConfig::getUtilityLinks()->first;
        $this->assertEquals($link1['url'], $node->url);
        $this->assertEquals($link1['label'], $node->label);

        PageConfig::addUtilityLink($link2['label'], $link2['url']);

        $menu = PageConfig::getUtilityLinks();
        $this->assertEquals(2, $menu->getNodeCount());

        $node = PageConfig::getUtilityLinks()->first->next_node;
        $this->assertEquals($link2['url'], $node->url);
        $this->assertEquals($link2['label'], $node->label);
    }

    public function testClearPreloads()
    {
        PageConfig::registerPreload(new Preload('link', 'preload', 'https://url.com'));
        PageConfig::registerPreload(new Preload('link', 'preload', 'https://url.com'));
        $preloads = PageConfig::getPreloads();
        $this->assertCount(2, $preloads);

        PageConfig::clearPreloads();
        $preloads = PageConfig::getPreloads();
        $this->assertCount(0, $preloads);
    }

	public function testContentCSSClass()
	{
		$css_class = 'test-class';

		$this->assertEquals('', PageConfig::getContentCSSClass(), 'Default content css class');

		PageConfig::setContentCSSClass($css_class);
		$this->assertEquals($css_class, PageConfig::getContentCSSClass(), 'Content CSS class assignment');
	}

    public function testGetPageConfig()
    {
        $this->assertEquals('', PageConfig::getPageStatus());
    }

    public function testGetPreloads()
    {
        // test default state
        $preloads = PageConfig::getPreloads();
        $this->assertCount(0, $preloads);

        PageConfig::registerPreload(new Preload('link', 'preload', 'https://url.com'));
        $preloads = PageConfig::getPreloads();
        $this->assertCount(1, $preloads);
    }

	function testRegisterScript()
	{
		$local_path = '/path/to/my/app.js';
		$remote_url = 'https://google.com/somelibrary.js';

		// confirm initial state
		$this->assertCount(0, PageConfig::$scripts);

		// test add local script
		PageConfig::registerScript($local_path);
		$this->assertCount(1, PageConfig::$scripts);
		$this->assertEquals($local_path, PageConfig::$scripts[0]);

		// test add duplicate script
		PageConfig::registerScript($local_path);
		$this->assertCount(1, PageConfig::$scripts);
		$this->assertEquals($local_path, PageConfig::$scripts[0]);

		// test add remote script
		PageConfig::registerScript($remote_url);
		$this->assertCount(2, PageConfig::$scripts);
		$this->assertEquals($local_path, PageConfig::$scripts[0]);
		$this->assertEquals($remote_url, PageConfig::$scripts[1]);

		// cleanup
		PageConfig::$scripts = [];
	}

	function testRegisterStylesheet()
	{
		$local_path = '/path/to/local/app.css';
		$remote_url = 'https://google.com/somefont.css';

		// confirm initial state
		$this->assertCount(0, PageConfig::$stylesheets);

		// test add local script
		PageConfig::registerStylesheet($local_path);
		$this->assertCount(1, PageConfig::$stylesheets);
		$this->assertEquals($local_path, PageConfig::$stylesheets[0]);

		// test add duplicate script
		PageConfig::registerStylesheet($local_path);
		$this->assertCount(1, PageConfig::$stylesheets);
		$this->assertEquals($local_path, PageConfig::$stylesheets[0]);

		// test add remote script
		PageConfig::registerStylesheet($remote_url);
		$this->assertCount(2, PageConfig::$stylesheets);
		$this->assertEquals($local_path, PageConfig::$stylesheets[0]);
		$this->assertEquals($remote_url, PageConfig::$stylesheets[1]);

		// cleanup
		PageConfig::$stylesheets = [];
	}

	function testUpdateBreadcrumb()
	{
		$new_url = "https://localhost/newurl";

		// call the method with no breadcrumbs
		PageConfig::updateBreadcrumb('node_2', $new_url);
		$breadcrumbs = PageConfig::getBreadcrumbs();
		$this->assertNull($breadcrumbs);

		// set up some breadcrumbs
		PageConfig::addBreadcrumb('first node', 'https://localhost/firstnode');
		PageConfig::addBreadcrumb('node_2', 'https://localhost/second_node');
		PageConfig::addBreadcrumb('node 3', 'https://localhost/thirdnode');

		// confirm the initial value of the test node
		$breadcrumbs = PageConfig::getBreadcrumbs();
		$this->assertNotEquals($new_url, $breadcrumbs->find('node_2')->url);

		// confirm updated value of test node
		PageConfig::updateBreadcrumb('node_2', $new_url);
		$this->assertNotEquals($new_url, $breadcrumbs->find('first node')->url);
		$this->assertEquals($new_url, $breadcrumbs->find('node_2')->url);
		$this->assertNotEquals($new_url, $breadcrumbs->find('node 3')->url);

		// confirm calling method on non-existent node
		PageConfig::updateBreadcrumb('node_22', 'https://mybogusurl.com');
		$this->assertEquals($new_url, $breadcrumbs->find('node_2')->url);
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