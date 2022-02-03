<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Navigation\Breadcrumbs;
use Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsTestData;
use PHPUnit\Framework\TestCase;

class BreadcrumbsTest extends TestCase
{
	/** @var string */
	public const TEMPLATE_PATH = 'framework/navigation/breadcrumbs-menu.php';
    /** @var Breadcrumbs */
    protected $obj;

    protected function setUp(): void
    {
        parent::setUp();
		LittledGlobals::setTemplatePath(LITTLED_TEMPLATE_DIR);
        $this->obj = new Breadcrumbs();
    }

    function testSetBreadcrumbsTemplatePath()
    {
		$original = Breadcrumbs::getBreadcrumbsTemplatePath();

        // test default value
        $this->assertEquals('', Breadcrumbs::getBreadcrumbsTemplatePath());

        // test assigned template value
        Breadcrumbs::setBreadcrumbsTemplatePath(LittledGlobals::getTemplatePath().self::TEMPLATE_PATH);
        $this->assertEquals(LittledGlobals::getTemplatePath().self::TEMPLATE_PATH, Breadcrumbs::getBreadcrumbsTemplatePath());
	    $this->assertEquals(LittledGlobals::getTemplatePath().self::TEMPLATE_PATH, Breadcrumbs::getMenuTemplatePath());

		Breadcrumbs::setBreadcrumbsTemplatePath($original);
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

    /**
     * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsTestDataProvider::renderTestProvider()
     * @param BreadcrumbsTestData $data
     * @return void
     * @throws ResourceNotFoundException
     */
    function testRender(BreadcrumbsTestData $data)
    {
	    Breadcrumbs::setBreadcrumbsTemplatePath(LittledGlobals::getTemplatePath().self::TEMPLATE_PATH);
        $this->expectOutputRegex($data->expected);
        $data->menu->render();
    }
}