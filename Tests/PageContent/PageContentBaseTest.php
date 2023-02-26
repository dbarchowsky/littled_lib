<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\InvalidTypeException;
use Littled\PageContent\PageContentBase;
use Littled\Tests\TestHarness\PageContent\PageContentBaseTestHarness;
use Littled\Tests\TestHarness\PageContent\PageContentChild;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;


class PageContentBaseTest extends TestCase
{
    function testGetBaseRoute()
    {
        $this->assertEquals('', PageContentBase::getBaseRoute());
        $this->assertEquals('route-base', PageContentBaseTestHarness::getBaseRoute());
    }

    function testGetTemplatePath()
    {
        $pcb = new PageContentBaseTestHarness();
        $this->assertEquals('', $pcb->getTemplatePath());
    }

    /**
     * @throws InvalidTypeException
     */
    function testSetBaseRoute()
    {
        $original_route = static::getOriginalRoute(PageContentBase::class);

        $new_route = 'route-boy';
        PageContentBase::setBaseRoute($new_route);
        $this->assertEquals($new_route, PageContentBase::getBaseRoute());
        $this->assertEquals('', PageContentBase::getSubRoute());

        PageContentBase::setRouteParts($original_route);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::setRoutePartsTestProvider()
     * @param array $expected
     * @param array $route_parts
     * @return void
     * @throws InvalidTypeException
     */
    function testSetRouteParts(array $expected, array $route_parts)
    {
        $original_route = static::getOriginalRoute(PageContentBase::class);

        PageContentBase::setRouteParts($route_parts);
        foreach($expected as $i => $value) {
            $this->assertEquals($value, PageContentBase::getSubRoute($i));
        }

        PageContentBase::setRouteParts($original_route);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::setSubRouteTestProvider()
     * @throws InvalidTypeException
     */
    function testSetSubRoute(array $expected, $value, int $index=1, ?array $start_route=null)
    {
        $original_route = static::getOriginalRoute(PageContentBase::class);

        if(is_array($start_route)) {
            PageContentBase::setRouteParts($start_route);
        }

        PageContentBase::setSubRoute($value, $index);
        foreach($expected as $i => $value) {
            $this->assertEquals($value, PageContentBase::getSubRoute($i));
        }

        PageContentBase::setRouteParts($original_route);
    }

    function testSetTemplatePath()
    {
        $path1 = '/first/path/to/templates/';
        $path2 = '/second/path/to/templates/';

        // test base class setter
        $pcb = new PageContentBaseTestHarness();
        $pcb->setTemplatePath($path1);
        $this->assertEquals($path1, $pcb->getTemplatePath());

        $pc = new PageContentChild();
        $pc->setTemplatePath($path2);
        $this->assertEquals($path2, $pc->getTemplatePath());
        $this->assertEquals($path1, $pcb->getTemplatePath());
    }

    /**
     * Get the current original route as a list of its parts from an APIRoute class.
     * @param string $api_route_class
     * @return array
     * @throws InvalidTypeException
     */
    protected static function getOriginalRoute(string $api_route_class): array
    {
        if (!Validation::isSubclass($api_route_class, PageContentBase::class)) {
            throw new InvalidTypeException('Must be APIRoute class');
        }

        $original_route = [];
        $i = 0;
        do {
            $part = call_user_func([$api_route_class, 'getSubRoute'], $i);
            if ($part=='') {
                break;
            }
            $original_route[] = $part;
            $i++;
        } while (1);
        return $original_route;
    }
}