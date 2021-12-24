<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Navigation\Breadcrumbs;
use PHPUnit\Framework\TestCase;

class BreadcrumbsTest extends TestCase
{
    /** @var Breadcrumbs */
    protected $obj;

    protected const TEMPLATE_PATH = '../../assets/templates/breadcrumbs.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new Breadcrumbs();
    }

    function testSetBreadcrumbsTemplatePath()
    {
        // test default value
        $this->assertEquals('', Breadcrumbs::getBreadcrumbsTemplatePath());

        // test assigned template value
        Breadcrumbs::setBreadcrumbsTemplatePath(self::TEMPLATE_PATH);
        $this->assertEquals(self::TEMPLATE_PATH, Breadcrumbs::getBreadcrumbsTemplatePath());
    }

    /**
     * @return void
     * @throws ResourceNotFoundException
     */
    function testRender()
    {
        $label1 = 'BC1';
        $url1 = '/url1';
        $pattern = '/^<ul>\W*<li><a href=\"'.str_replace('/', '\/', $url1).'\">'.$label1.'<\/a><\/li>(.|\n)*<\/ul>/';

        // set up menu
        Breadcrumbs::setBreadcrumbsTemplatePath(SHARED_CMS_TEMPLATE_DIR.'framework/navigation/breadcrumbs.php');
        $this->obj->addNode($label1, $url1);
        $this->obj->addNode('BC 2', '/url-2');

        $this->expectOutputRegex($pattern);
        $this->obj->render();
    }
}