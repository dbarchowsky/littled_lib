<?php
namespace Littled\Tests\API;

use Exception;
use Littled\API\APIPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\DataProvider\API\APIPageLoadTemplateContentTestData;
use Littled\Tests\TestHarness\API\APIPageTestHarness;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\PageContent\Cache\ContentCacheTestHarness;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTable;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableListingsPage;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;


class APIPageTest extends TestCase
{
    /** @var int */
	public const        TEST_CONTENT_TYPE_ID = 6037; /* "Test Section" in `site_section` table from littledamien database */
    /** @var int */
    public const        TEST_TEMPLATE_CONTENT_TYPE_ID = 31;
    /** @var int */
    public const        TEST_RECORD_ID = 2023;
	/** @var string */
	public const        TEST_RECORD_NAME = 'fixed test record';
    protected const     AJAX_INPUT_SOURCE = APP_BASE_DIR."Tests/DataProvider/API/APIPage_collectPageAction.dat";
    public const        LISTINGS_OPERATION_TOKEN = 'listings';

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        LittledGlobals::setLocalTemplatesPath(TEST_TEMPLATES_PATH);
	    LittledGlobals::setSharedTemplatesPath(TEST_TEMPLATES_PATH);
        APIPage::setControllerClass(ContentControllerTestHarness::class);
        APIPage::setCacheClass(ContentCacheTestHarness::class);
    }

    function testConstructor()
    {
        $ap = new APIPage();
        $this->assertEquals('', $ap->operation->value);
    }

    /**
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    function testCollectAndLoadJsonContent()
    {
        $ap = new APIPage();

        // retrieve content properties
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $ap->operation->setInputValue('delete');
        $ap->retrieveContentProperties();

        // retrieve record content from database
        $ap->content = call_user_func_array([$ap::getControllerClass(), 'getContentObject'], array($ap->getContentTypeId()));
        $ap->content->id->setInputValue(self::TEST_RECORD_ID);

        // inject record content into template
        $ap->collectAndLoadJsonContent();
        $this->assertMatchesRegularExpression('/^\s*<div class=\"dialog delete-confirmation\"/', $ap->json->content->value);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::collectContentPropertiesTestProvider()
     * @param array $expected
     * @param array $post_data
     * @param string $ajax_stream
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    function testCollectContentProperties(
        array $expected,
        array $post_data,
        string $ajax_stream='',
        string $msg='' )
    {
        // setup request data sources
        $_POST = $post_data;
        if ($ajax_stream) {
            Validation::setAjaxInputStream($ajax_stream);
        }

        $ap = new APIPage();
        if (count($expected)==0) {
            $this->expectException(ContentValidationException::class);
        }
        $ap->collectContentProperties();

        foreach ($expected as $property => $value) {
            if ($property=='content_type_id') {
                $this->assertEquals($value, $ap->getContentTypeId(), $msg);
            }
            else {
                $p = $ap->$property;
                $this->assertEquals($value, $p->value, $msg);
            }
        }

        // restore state
        $_POST = [];
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::collectFiltersRequestDataTestProvider()
     * @param array $expected
     * @param array $get_data
     * @param array $post_data
     * @param string $ajax_stream
     * @param string $msg
     * @return void
     * @throws NotImplementedException
     */
    function testCollectFiltersRequestData(
        array $expected,
        array $get_data=[],
        array $post_data=[],
        string $ajax_stream='',
        string $msg=''
    )
    {
        $_GET = $get_data;
        $_POST = $post_data;

        $ap = new APIPageTestHarness();
        $ap->filters = new TestTableContentFiltersTestHarness();
        $ajax_data = null;
        if ($ajax_stream) {
            Validation::setAjaxInputStream($ajax_stream);
            $ajax_data = APIPageTestHarness::publicGetAjaxClientRequestData();
        }

        $ap->collectFiltersRequestData($ajax_data);

        if (count($expected) == 0) {
            $this->assertEquals(null, $ap->getContentTypeId());
        }
        else {
            foreach ($expected as $property => $value) {
                if ($property == 'content_type_id') {
                    $this->assertEquals($value, $ap->getContentTypeId(), $msg);
                } else {
                    $p = $ap->filters->$property;
                    $this->assertEquals($value, $p->value, $msg);
                }
            }
        }

        // restore state
        $_GET = $_POST = [];
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::collectPageActionTestProvider()
     * @param string $expected
     * @param string $source
     * @param string $key
     * @param $value
     * @param ?array $data
     * @param string $msg
     * @return void
     */
    function testCollectPageAction(string $expected, string $source, string $key='', $value=null, ?array $data=null, string $msg='')
    {
        $o = new APIPage();

        switch($source) {
            case 'post':
                $_POST[$key] = $value;
                break;
            case 'ajax':
                APIPage::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
                break;
            default:
                break;
        }

        $o->collectPageAction($data);
        $this->assertEquals($expected, $o->action, $msg);

        switch($source) {
            case 'post':
                unset($_POST[$key]);
                break;
            case 'ajax':
                APIPage::setAjaxInputStream('php://input');
                break;
            default:
                break;
        }
    }

	/**
	 * @throws RecordNotFoundException
	 */
	function testFetchContentTemplate()
	{
		$o = new APIPageTestHarness();
		$o->setContentTypeId(TestTable::CONTENT_TYPE_ID);
		$o->fetchContentTemplate('listings');
		$this->assertEquals('listings.php', $o->template->path->value);
	}

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
	 * @throws RecordNotFoundException
	 * @throws ContentValidationException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentLabel()
	{
		$ap = new APIPage();
		$this->assertEquals('', $ap->getContentLabel());

		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$this->assertEquals('', $ap->getContentLabel());

		$ap->content_properties->read();
		$this->assertEquals('Test Section', $ap->getContentLabel());
	}

    /**
     * @throws ConfigurationUndefinedException
     */
    function testGetContentObject()
    {
        $content = call_user_func_array([APIPage::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
        $this->assertInstanceOf(SerializedContent::class, $content);
    }

    function testGetContentTypeId()
    {
        $ap = new APIPage();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->content_properties->id->value);
    }

	/**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::loadTemplateContentTestProvider()
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	function testLoadTemplateContent(APIPageLoadTemplateContentTestData $data)
	{
		$ap = new APIPage();
		$ap->setContentTypeId($data->content_type_id);
		$ap->operation->setInputValue($data->operation);
		$ap->retrieveContentProperties();

		// retrieve record content from database
		$ap->content = call_user_func_array([$ap::getControllerClass(), 'getContentObject'], array($ap->getContentTypeId()));
		$ap->content->id->setInputValue(self::TEST_RECORD_ID);

        if (isset($data->template) && $data->template) {
			$ap->template = new ContentTemplate();
			$ap->template->location->value = ContentTemplate::getLocalPathToken();
            $ap->template->path->value = $data->template;
        }
		ob_start();
		$ap->loadTemplateContent($data->context);
		ob_end_clean();
		self::assertMatchesRegularExpression($data->pattern, $ap->json->content->value, $data->msg);
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::lookupRouteTestProvider()
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    function testLookupRoute(string $operation, string $expected_route)
	{
		$ap = new APIPage();
		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$ap->content_properties->read();
		$this->assertGreaterThan(0, count($ap->content_properties->routes));

		$ap->operation->value = $operation;
		$ap->lookupRoute();
		$this->assertEquals($operation, $ap->route->operation->value);
		$this->assertMatchesRegularExpression($expected_route, $ap->route->api_route->value);
	}

	/**
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
	 */
	function testLookupTemplate()
    {
        $ap = new APIPage();
        $ap->setContentTypeId(self::TEST_TEMPLATE_CONTENT_TYPE_ID);
        $ap->content_properties->read();
        $this->assertGreaterThan(0, count($ap->content_properties->templates));

        $ap->operation->value = 'details';
        $ap->lookupTemplate();
        $this->assertEquals('details', $ap->template->name->value);

        $ap->lookupTemplate('delete');
        $this->assertEquals('delete', $ap->template->name->value);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws InvalidQueryException
     */
    function testNewPageContentTemplateInstance()
    {
        $ap = new APIPageTestHarness();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $ap->operation->value = self::LISTINGS_OPERATION_TOKEN;
        $c = $ap->publicNewRoutedPageContentTemplateInstance();
        $this->assertInstanceOf(TestTableListingsPage::class, $c);
    }

	/**
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
	 */
	function testRetrieveContentObjectAndData()
	{
		$ap = new APIPage();
		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$ap->retrieveContentProperties();
		$ap->record_id->setInputValue(self::TEST_RECORD_ID);
		$ap->retrieveContentObjectAndData();
		/** @var TestTable $content */
		$content = $ap->content;
		$this->assertEquals(self::TEST_RECORD_NAME, $content->name->value);
	}

	/**
	 * @runInSeparateProcess
	 * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::sendTextResponseTestProvider()
	 * @param string $expected
	 * @param string $response
	 * @param string $override_response
	 * @return void
	 */
	function testSendTextResponse(string $expected, string $response, string $override_response='')
	{
		$ap = new APIPage();
		$ap->json->content->value = $response;
		ob_start();
		$ap->sendTextResponse($override_response);
		$response = ob_get_contents();
		ob_end_flush();

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
            try {
                APIPage::setCacheClass($cache_class);
                $this->assertEquals(false, true, "Expected exception \"$exception_class\" not thrown.");
            }
            catch(Exception $e) {
                $this->assertInstanceOf($exception_class, $e);
            }
        }
        else {
            APIPage::setCacheClass($cache_class);
            $this->assertEquals($cache_class, APIPage::getCacheClass(), $msg);
        }
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