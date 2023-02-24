<?php
namespace Littled\Tests\API;

use Littled\Tests\DataProvider\API\APIRouteTestExpectations;
use Littled\API\APIRoute;
use Littled\App\AppBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Tests\TestHarness\API\APIRouteTestHarness;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;


class APIRouteTest extends APIRouteTestBase
{
	function testGetAjaxRequestData()
	{
		$expected = array(
            "key1" => "value1",
            "keyTwo" => "value two",
            "jsonKey" => "json value");

        AppBase::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data.dat'));
		$this->assertEquals($expected, AppBase::getAjaxRequestData());

        AppBase::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data-empty.dat'));
		$this->assertEquals(null, AppBase::getAjaxRequestData());

        AppBase::setAjaxInputStream('php://input');
	}

	function testGetBaseRoute()
	{
		$this->assertEquals('', APIRoute::getBaseRoute());
	}

	/**
	 * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::collectFiltersRequestDataTestProvider()
	 * @param APIRouteTestExpectations $expected
	 * @param array|null $post_data
	 * @param array|null $get_data
	 * @param string $ajax_stream
	 * @param string $msg
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws NotImplementedException
	 */
	function testCollectFiltersRequestData(
		APIRouteTestExpectations $expected,
		?array $get_data=[],
		?array $post_data=[],
		string $ajax_stream='',
		string $msg='')
	{
		$_POST = $post_data;
		$_GET = $get_data;
		if ($ajax_stream) {
			AppBase::setAjaxInputStream($ajax_stream);
		}

		$r = new APIRouteTestHarness();
		if ($expected->exception_class) {
			$this->expectException($expected->exception_class);
		}
		$r->filters = new TestTableContentFiltersTestHarness();
		$r->collectFiltersRequestData();
		$this->assertEquals($expected->name_filter, $r->filters->name_filter->value, $msg);
		$this->assertEquals($expected->int_filter, $r->filters->int_filter->value, $msg);
		$this->assertEquals($expected->bool_filter, $r->filters->bool_filter->value, $msg);

		$_GET = $_POST = [];
		AppBase::setAjaxInputStream('php://input');
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentObject()
	{
		$content = call_user_func_array([APIRoute::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
		$this->assertInstanceOf(SerializedContent::class, $content);
	}

	function testGetSubRoute()
	{
		$this->assertEquals('', APIRouteTestHarness::getSubRoute());
		$this->assertEquals('', APIRouteTestHarness::getSubRoute(4));
	}

    /**
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     */
    function testInitializeFiltersObject()
    {
        $r = new APIRouteTestHarness();
        $r->initializeFiltersObject(APIRouteTestBase::TEST_CONTENT_TYPE_ID);
        $this->assertInstanceOf(ContentFilters::class, $r->filters);
        $this->assertEquals(APIRouteTestBase::TEST_CONTENT_TYPE_ID, $r->filters->content_properties->id->value);
        $this->assertEquals(APIRouteTestBase::TEST_CONTENT_TYPE_ID, $r->filters::getContentTypeId());
        // null because APIRoute::getContentProperties() doesn't check the $filters property like APIListingsRoute::getContentProperties() would
        $this->assertEquals(null, $r->getContentProperties()->getRecordId());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::sendTextResponseTestProvider()
     * @param string $expected
     * @param string $response
     * @param string $override_response
     * @return void
     */
    function testSendTextResponse(string $expected, string $response, string $override_response='')
    {
        $ap = new APIRouteTestHarness();
        $ap->json->content->value = $response;
        ob_start();
        $ap->sendTextResponse($override_response);
        $response = ob_get_contents();
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertMatchesRegularExpression('/^Content-type: text\/plain/', $headers[0]);
        $this->assertMatchesRegularExpression($expected, $response);
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testSetBaseRoute()
	{
		$original_route = APIRouteTest::getOriginalRoute(APIRouteTestHarness::class);

		$new_route = 'route-boy';
		APIRoute::setBaseRoute($new_route);
		$this->assertEquals($new_route, APIRoute::getBaseRoute());
		$this->assertEquals('', APIRouteTestHarness::getSubRoute());

		APIRouteTestHarness::setRouteParts($original_route);
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::setCacheClassTestProvider()
     * @param string $cache_class
     * @param string $exception_class
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     */
    function testSetCacheClass(string $cache_class, string $exception_class='', string $msg='')
    {
        if ($exception_class) {
            $this->expectException($exception_class);
        }

        APIRoute::setCacheClass($cache_class);
        $this->assertEquals($cache_class, APIRoute::getCacheClass(), $msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::setControllerClassTestProvider()
     * @param string $expected
     * @param string $class_name
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     */
    function testSetControllerClass(string $expected, string $class_name)
    {
        if ($expected) {
            $this->expectException($expected);
        }
        APIRoute::setControllerClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, APIRoute::getControllerClass());
        }
    }

    function testSetDefaultTemplateName()
    {
        $this->assertEquals('', APIRoute::getDefaultTemplateName());

        APIRoute::setDefaultTemplateName('listings');
        $this->assertEquals('listings', APIRoute::getDefaultTemplateName());

        // return to its original state
        APIRoute::setDefaultTemplateName('');
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
		$original_route = static::getOriginalRoute(APIRouteTestHarness::class);

		APIRouteTestHarness::setRouteParts($route_parts);
		foreach($expected as $i => $value) {
			$this->assertEquals($value, APIRouteTestHarness::getSubRoute($i));
		}

		APIRouteTestHarness::setRouteParts($original_route);
	}

	/**
	 * @dataProvider \Littled\Tests\DataProvider\API\APIRouteTestDataProvider::setSubRouteTestProvider()
	 * @throws InvalidTypeException
	 */
	function testSetSubRoute(array $expected, $value, int $index=1, ?array $start_route=null)
	{
		$original_route = static::getOriginalRoute(APIRouteTestHarness::class);

		if(is_array($start_route)) {
			APIRoute::setRouteParts($start_route);
		}

		APIRouteTestHarness::setSubRoute($value, $index);
		foreach($expected as $i => $value) {
			$this->assertEquals($value, APIRouteTestHarness::getSubRoute($i));
		}

		APIRouteTestHarness::setRouteParts($original_route);
	}


	/**
	 * Get the current original route as a list of its parts from an APIRoute class.
	 * @param string $api_route_class
	 * @return array
	 * @throws InvalidTypeException
	 */
	protected static function getOriginalRoute(string $api_route_class): array
	{
		if (!Validation::isSubclass($api_route_class, APIRoute::class)) {
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