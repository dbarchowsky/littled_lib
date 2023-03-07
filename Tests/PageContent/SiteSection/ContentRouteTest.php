<?php
namespace Littled\Tests\PageContent\SiteSection;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\Tests\TestHarness\PageContent\SiteSection\ContentRouteTestHarness;
use PHPUnit\Framework\TestCase;


class ContentRouteTest extends TestCase
{
    const TEST_CONTENT_TYPE_ID = 34;

	function testConstructorAssignedPropertyValues()
	{
		$cr = new ContentRoute(99, 6037, 'foo', 'my-route', 'https://localhost');
		$this->assertEquals(99, $cr->id->value);
		$this->assertEquals(6037, $cr->site_section_id->value);
		$this->assertEquals('foo', $cr->operation->value);
        $this->assertEquals('my-route', $cr->route->value);
        $this->assertEquals('https://localhost', $cr->api_route->value);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testConstructorDefaultPropertyValues()
	{
		$cr = new ContentRoute();
		$this->assertNull($cr->id->value);
		$this->assertNull($cr->site_section_id->value);
		$this->assertEmpty($cr->api_route->value);
        $this->assertEmpty($cr->operation->value);
        $this->assertEmpty($cr->route->value);

		$this->assertEquals(self::TEST_CONTENT_TYPE_ID, ContentRoute::getContentTypeId());
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\SiteSection\ContentRouteTestDataProvider::explodeRouteStringTestProvider()
     * @param array $expected
     * @param string $route
     * @param string $msg
     * @return void
     */
    function testExplodeRouteString(array $expected, string $route, string $msg='')
    {
        $this->assertEquals($expected, ContentRouteTestHarness::explodeRouteString($route));
    }

    /**
     * @param array $expected
     * @param string $route
     * @param string $msg
     * @return void
     */
    function testExplodeAPIRoute()
    {
        $r = new ContentRoute();
        $r->api_route->value = '/api/route';
        $this->assertEquals(['api', 'route'], $r->explodeAPIRoute());
    }

    /**
     * @param array $expected
     * @param string $route
     * @param string $msg
     * @return void
     */
    function testExplodeRoute()
    {
        $r = new ContentRoute();
        $r->route->value = '/path/to/page';
        $this->assertEquals(['path', 'to', 'page'], $r->explodeRoute());
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\SiteSection\ContentRouteTestDataProvider::fetchRecordTestProvider()
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    function testFetchRecord(
        ContentRoute $o,
        int $record_id,
        ?int $expected_site_section_id,
        string $expected_operation,
        ?string $expected_route='',
        ?string $expected_url='',
        ?string $msg=''
    )
    {
        $o->setRecordId($record_id);
        $o->read();
        $this->assertEquals($expected_site_section_id, $o->site_section_id->value, $msg);
        $this->assertEquals($expected_operation, $o->operation->value, $msg);
        $this->assertMatchesRegularExpression($expected_route, $o->route->value, $msg);
        $this->assertMatchesRegularExpression($expected_url, $o->api_route->value, $msg);
    }

    /**
     * @throws InvalidValueException
     */
    function testGetPropertyValue()
    {
        $o = new ContentRoute(99, 1011, 'listings', '/my-route', '/api/route');
        $this->assertEquals('listings', $o->getPropertyValue(ContentRoute::PROPERTY_TOKEN_OPERATION));
        $this->assertEquals('/my-route', $o->getPropertyValue(ContentRoute::PROPERTY_TOKEN_ROUTE));
        $this->assertEquals('/api/route', $o->getPropertyValue(ContentRoute::PROPERTY_TOKEN_API_ROUTE));

        $o->route->setInputValue('/path/to/route');
        $o->api_route->setInputValue('/api/route/path');

        $route_parts = $o->getPropertyValue(ContentRoute::PROPERTY_TOKEN_ROUTE_AS_ARRAY);
        $this->assertCount(3, $route_parts);
        $this->assertEquals('path', $route_parts[0]);
        $this->assertEquals('to', $route_parts[1]);
        $this->assertEquals('route', $route_parts[2]);

        $route_parts = $o->getPropertyValue(ContentRoute::PROPERTY_TOKEN_API_ROUTE_AS_ARRAY);
        $this->assertCount(3, $route_parts);
        $this->assertEquals('api', $route_parts[0]);
        $this->assertEquals('route', $route_parts[1]);
        $this->assertEquals('path', $route_parts[2]);

    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\SiteSection\ContentRouteTestDataProvider::hasDataTestProvider()
     * @param bool $expected
     * @param ContentRoute $o
     * @param string $msg
     * @return void
     */
	function testHasData(bool $expected, ContentRoute $o, string $msg='')
	{
		if (true===$expected) {
			$this->assertTrue($o->hasData(), $msg);
		}
		else {
			$this->assertFalse($o->hasData(), $msg);
		}
	}
}