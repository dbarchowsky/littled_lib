<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Navigation\Breadcrumbs;
use Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsTestData;
use PHPUnit\Framework\TestCase;

class BreadcrumbsTest extends TestCase
{
	/** @var string */
	public const TEMPLATE_PATH = 'framework/navigation/breadcrumbs-menu.php';
    /** @var Breadcrumbs */
    protected Breadcrumbs $obj;

    protected function setUp(): void
    {
        parent::setUp();
        LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
		LittledGlobals::setLocalTemplatesPath(LITTLED_TEMPLATE_DIR);
        $this->obj = new Breadcrumbs();
    }

    /**
     * Run this test first to insure default template path value.
     * @throws ConfigurationUndefinedException
     */
    function testSetBreadcrumbsTemplatePath()
    {
        $original = Breadcrumbs::getBreadcrumbsTemplatePath();

        // test default value
        $this->assertEquals('', Breadcrumbs::getBreadcrumbsTemplatePath());

        // test assigned template value
        Breadcrumbs::setBreadcrumbsTemplatePath(LittledGlobals::getLocalTemplatesPath().self::TEMPLATE_PATH);
        $this->assertEquals(LittledGlobals::getLocalTemplatesPath().self::TEMPLATE_PATH, Breadcrumbs::getBreadcrumbsTemplatePath());
        $this->assertEquals(LittledGlobals::getLocalTemplatesPath().self::TEMPLATE_PATH, Breadcrumbs::getMenuTemplatePath());

        Breadcrumbs::setBreadcrumbsTemplatePath($original);
    }

    function testClearNodes()
    {
        $o = new Breadcrumbs();

        // call with no breadcrumb nodes
        $o->clearNodes();
        $this->assertEquals(0, $o->getNodeCount());
        $this->assertFalse(isset($o->first));
        $this->assertFalse(isset($o->last));

        // call with one node
        $o->addNode('test 1a');
        $o->clearNodes();
        $this->assertEquals(0, $o->getNodeCount());
        $this->assertFalse(isset($o->first));
        $this->assertFalse(isset($o->last));

        // call with multiple nodes
        $o->addNode('test 1b');
        $o->addNode('test 2b');
        $o->addNode('test 2c');
        $o->clearNodes();
        $this->assertEquals(0, $o->getNodeCount());
        $this->assertFalse(isset($o->first));
        $this->assertFalse(isset($o->last));
    }

	function testFind()
	{
		$first_url = 'https://localhost/myurl';
		$b = new Breadcrumbs();

		$this->assertNull($b->find('node'));

		$b->addNode('test_01', $first_url);
		$this->assertNull($b->find('node'));
		$this->assertEquals($first_url, $b->find('test_01')->url);

		$b->addNode('test 02', 'https://another.url');
		$this->assertEquals($first_url, $b->find('test_01')->url);
		$this->assertEquals('https://another.url', $b->find('test 02')->url);
		$this->assertNull($b->find('bogus label'));
	}

    function testGetNodeCount()
    {
        $o = new Breadcrumbs();
        $this->assertEquals(0, $o->getNodeCount());

        $o->addNode('test 1');
        $this->assertEquals(1, $o->getNodeCount());

        $o->addNode('test 2');
        $this->assertEquals(2, $o->getNodeCount());
    }

    function testPopNode()
    {
        $o = new Breadcrumbs();

        // call popNode() with no nodes
        $o->popNode();
        $this->assertEquals(0, $o->getNodeCount());

        // pop last node with one node in breadcrumb list
        $o->addNode('test 1');
        $o->popNode();
        $this->assertEquals(0, $o->getNodeCount());

        // pop last node with two nodes in breadcrumb list
        $o->addNode('test 1a');
        $o->addNode('test 2a');
        $o->popNode();
        $this->assertEquals(1, $o->getNodeCount());

        $this->assertEquals('test 1a', $o->first->label);
        $this->assertEquals('test 1a', $o->last->label);
        $this->assertFalse(isset($o->first->next_node));
        $this->assertFalse(isset($o->last->prev_node));

        // pop last node with 2+ nodes in breadcrumb list
        $o->addNode('test 2b');
        $o->addNode('test 3b');
        $o->addNode('test 4b');
        $o->popNode();
        $this->assertEquals(3, $o->getNodeCount());

        $this->assertEquals('test 3b', $o->last->label);
        $this->assertEquals('test 2b', $o->last->prev_node->label);
    }

    function testPopNodes()
    {
        $o = new Breadcrumbs();

        // call pop nodes with no nodes in breadcrumb list
        $o->popNodes(2);
        $this->assertEquals(0, $o->getNodeCount());

        // call pop nodes with 1 node in breadcrumb list
        $o->addNode('test 1a');
        $o->popNodes(2);
        $this->assertEquals(0, $o->getNodeCount());

        // pop 2 nodes with 2 nodes in breadcrumb list
        $o->addNode('test 1b');
        $o->addNode('test 2b');
        $o->popNodes(2);
        $this->assertEquals(0, $o->getNodeCount());

        // pop 2 nodes with 3 nodes in breadcrumb list
        $o->addNode('test 1c');
        $o->addNode('test 2c');
        $o->addNode('test 3c');
        $o->popNodes(2);
        $this->assertEquals(1, $o->getNodeCount());

        $this->assertEquals('test 1c', $o->first->label);
        $this->assertEquals('test 1c', $o->last->label);
        $this->assertFalse(isset($o->first->next_node));
        $this->assertFalse(isset($o->last->prev_node));

        // pop 2 nodes with 3+ nodes in breadcrumb list
        $o->addNode('test 2d');
        $o->addNode('test 3d');
        $o->addNode('test 4d');
        $o->addNode('test 5d');
        $o->popNodes(2);
        $this->assertEquals(3, $o->getNodeCount());

        $this->assertEquals('test 1c', $o->first->label);
        $this->assertEquals('test 3d', $o->last->label);
        $this->assertEquals('test 2d', $o->first->next_node->label);
        $this->assertEquals('test 2d', $o->last->prev_node->label);
    }

    /**
     * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsTestDataProvider::renderTestProvider()
     * @param BreadcrumbsTestData $data
     * @return void
     * @throws ResourceNotFoundException|ConfigurationUndefinedException
     */
    function testRender(BreadcrumbsTestData $data)
    {
	    Breadcrumbs::setBreadcrumbsTemplatePath(LittledGlobals::getLocalTemplatesPath().self::TEMPLATE_PATH);
        $this->expectOutputRegex($data->expected);
        $data->menu->render();
    }
}