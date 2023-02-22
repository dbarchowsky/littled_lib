<?php
namespace Littled\Tests\API;

use Littled\API\APIRoute;
use Littled\App\AppBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Tests\TestHarness\API\APIRouteTestHarness;
use Littled\Utility\LittledUtility;


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
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentObject()
	{
		$content = call_user_func_array([APIRoute::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
		$this->assertInstanceOf(SerializedContent::class, $content);
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


	function testSetBaseRoute()
	{
		$original_route = APIRoute::getBaseRoute();
		$new_route = 'route-boy';

		APIRoute::setBaseRoute($new_route);
		$this->assertEquals($new_route, APIRoute::getBaseRoute());

		APIRoute::setBaseRoute($original_route);
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
}