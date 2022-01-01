<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Navigation\NavigationMenu;
use Littled\PageContent\Navigation\NavigationMenuNode;
use PHPUnit\Framework\TestCase;

class NavigationMenuTest extends TestCase
{
    /** @var NavigationMenu */
    protected $obj;
    /** @var string */
    protected const MENU_TEMPLATE_PATH = LITTLED_TEMPLATE_DIR.'framework/navigation/navmenu.php';
    /** @var string */
    protected const NODE_TEMPLATE_PATH = LITTLED_TEMPLATE_DIR.'framework/navigation/navmenu_node.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new NavigationMenu();
    }

    function testGetNodeCount()
    {
        $this->assertEquals(0, $this->obj->getNodeCount());
        $this->obj->addNode('l1', 'url');
        $this->assertEquals(1, $this->obj->getNodeCount());
        $this->obj->addNode('l2', 'url');
        $this->obj->addNode('l3', 'url');
        $this->assertEquals(3, $this->obj->getNodeCount());
    }

    function testSetMenuTemplatePath()
    {
        // test default value
        $this->assertEquals('', NavigationMenu::getMenuTemplatePath());

        // test assigned template value
        NavigationMenu::setMenuTemplatePath(self::MENU_TEMPLATE_PATH);
        $this->assertEquals(self::MENU_TEMPLATE_PATH, NavigationMenu::getMenuTemplatePath());
    }

    /**
     * @return void
     * @throws ResourceNotFoundException
     */
    function testRender()
    {
        $label1 = 'NMN1';
        $url1 = '/nav-url1';
        $pattern = '/^<ul>\W*<li><a href=\"'.str_replace('/', '\/', $url1).'\">'.$label1.'<\/a><\/li>(.|\n)*<\/ul>/';

        // set up menu
        NavigationMenu::setMenuTemplatePath(self::MENU_TEMPLATE_PATH);
        NavigationMenuNode::setNodeTemplatePath(self::NODE_TEMPLATE_PATH);
        $this->obj->addNode($label1, $url1);
        $this->obj->addNode('BC 2', '/url-2');

        $this->expectOutputRegex($pattern);
        $this->obj->render();
    }
}