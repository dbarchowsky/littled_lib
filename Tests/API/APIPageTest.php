<?php
namespace Littled\Tests\API;

use Littled\API\APIPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Tests\TestHarness\API\APIPageTestHarness;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;


class APIPageTest extends APIPageTestBase
{
	function testGetAjaxClientRequestData()
	{
		$expected = array("key1"=>"value1","keyTwo"=>"value two","jsonKey"=>"json value");
		Validation::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data.dat'));
		$this->assertEquals($expected, APIPageTestHarness::publicGetAjaxClientRequestData());

		Validation::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data-empty.dat'));
		$this->assertEquals(null, APIPageTestHarness::publicGetAjaxClientRequestData());

		// restore state
		Validation::setAjaxInputStream('php://input');
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentObject()
	{
		$content = call_user_func_array([APIPage::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
		$this->assertInstanceOf(SerializedContent::class, $content);
	}

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::sendTextResponseTestProvider()
     * @param string $expected
     * @param string $response
     * @param string $override_response
     * @return void
     */
    function testSendTextResponse(string $expected, string $response, string $override_response='')
    {
        $ap = new APIPageTestHarness();
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
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::setCacheClassTestProvider()
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

        APIPage::setCacheClass($cache_class);
        $this->assertEquals($cache_class, APIPage::getCacheClass(), $msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::setControllerClassTestProvider()
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
        APIPage::setControllerClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, APIPage::getControllerClass());
        }
    }

    function testSetDefaultTemplateName()
    {
        $this->assertEquals('', APIPage::getDefaultTemplateName());

        APIPage::setDefaultTemplateName('listings');
        $this->assertEquals('listings', APIPage::getDefaultTemplateName());

        // return to its original state
        APIPage::setDefaultTemplateName('');
    }
}