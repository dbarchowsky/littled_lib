<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Navigation\NavigationMenu;
use Littled\Tests\PageContent\Navigation\DataProvider\NavigationMenuTestData;
use PHPUnit\Framework\TestCase;

class NavigationMenuTest extends TestCase
{
    /** @var NavigationMenu */
    protected $obj;
    /** @var string */
    protected const MENU_TEMPLATE_PATH = LITTLED_TEMPLATE_DIR.'framework/navigation/navigation-menu.php';
    /** @var string */
    protected const NODE_TEMPLATE_PATH = LITTLED_TEMPLATE_DIR.'framework/navigation/navigation-menu-node.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new NavigationMenu();
		LittledGlobals::setLocalTemplatePath(LITTLED_TEMPLATE_DIR);
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
		$original = NavigationMenu::getMenuTemplatePath();

        // test default value
        $this->assertEquals('', NavigationMenu::getMenuTemplatePath());

        // test assigned template value
        NavigationMenu::setMenuTemplatePath(self::MENU_TEMPLATE_PATH);
        $this->assertEquals(self::MENU_TEMPLATE_PATH, NavigationMenu::getMenuTemplatePath());

		NavigationMenu::setMenuTemplatePath($original);
    }

	/**
	 * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\NavigationMenuTestDataProvider::renderTestProvider()
	 * @return void
	 * @throws ResourceNotFoundException
	 */
    function testRender(NavigationMenuTestData $data)
    {
		NavigationMenu::setMenuTemplatePath(self::MENU_TEMPLATE_PATH);
		$this->expectOutputRegex($data->expected);
		$data->menu->render();
    }
}