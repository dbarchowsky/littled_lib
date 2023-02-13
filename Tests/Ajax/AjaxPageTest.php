<?php
namespace Littled\Tests\Ajax;

use Exception;
use Littled\Ajax\AjaxPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\DataProvider\Ajax\AjaxPageLoadTemplateContentTestData;
use Littled\Tests\TestHarness\Ajax\AjaxPageTestHarness;
use Littled\Tests\TestHarness\PageContent\Cache\ContentCacheTestHarness;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTable;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableListingsPage;
use Littled\Utility\LittledUtility;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;

class AjaxPageTest extends TestCase
{
    /** @var int */
	public const        TEST_CONTENT_TYPE_ID = 6037; /* "Test Section" in `site_section` table from littledamien database */
    /** @var int */
    public const        TEST_TEMPLATE_CONTENT_TYPE_ID = 31;
    /** @var int */
    public const        TEST_RECORD_ID = 2023;
	/** @var string */
	public const        TEST_RECORD_NAME = 'fixed test record';
    protected const     AJAX_INPUT_SOURCE = APP_BASE_DIR."Tests/DataProvider/Ajax/AjaxPage_collectPageAction.dat";
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
        AjaxPage::setControllerClass(ContentControllerTestHarness::class);
        AjaxPage::setCacheClass(ContentCacheTestHarness::class);
    }

    function testConstructor()
    {
        $ap = new AjaxPage();
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
        $ap = new AjaxPage();

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
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::collectContentPropertiesTestProvider()
     * @param int|null $expected_content_id
     * @param string $expected_template_token
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
        ?int $expected_content_id,
        string $expected_template_token,
        array $post_data,
        string $ajax_stream='',
        string $msg='' )
    {
        // setup request data sources
        $_POST = $post_data;
        if ($ajax_stream) {
            Validation::setAjaxInputStream($ajax_stream);
        }

        $ap = new AjaxPage();
        if (null===$expected_content_id) {
            $this->expectException(ContentValidationException::class);
        }
        $ap->collectContentProperties();

        $this->assertEquals($expected_content_id, $ap->getContentTypeId(), $msg);
        $this->assertEquals($expected_template_token, $ap->operation->value, $msg);

        // restore state
        $_POST = [];
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::collectPageActionTestProvider()
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
        $o = new AjaxPage();

        switch($source) {
            case 'post':
                $_POST[$key] = $value;
                break;
            case 'ajax':
                AjaxPage::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
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
                AjaxPage::setAjaxInputStream('php://input');
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
		$o = new AjaxPageTestHarness();
		$o->setContentTypeId(TestTable::CONTENT_TYPE_ID);
		$o->fetchContentTemplate('listings');
		$this->assertEquals('listings.php', $o->template->path->value);
	}

    function testGetAjaxClientRequestData()
    {
        $expected = array("key1"=>"value1","keyTwo"=>"value two","jsonKey"=>"json value");
        Validation::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data.dat'));
        $this->assertEquals($expected, AjaxPageTestHarness::publicGetAjaxClientRequestData());

        Validation::setAjaxInputStream(LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/Validation/test-ajax-data-empty.dat'));
        $this->assertEquals(null, AjaxPageTestHarness::publicGetAjaxClientRequestData());

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
		$ap = new AjaxPage();
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
        $content = call_user_func_array([AjaxPage::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
        $this->assertInstanceOf(SerializedContent::class, $content);
    }

    function testGetContentTypeId()
    {
        $ap = new AjaxPage();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->content_properties->id->value);
    }

	/**
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::loadTemplateContentTestProvider()
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	function testLoadTemplateContent(AjaxPageLoadTemplateContentTestData $data)
	{
		$ap = new AjaxPage();
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
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::lookupRouteTestProvider()
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    function testLookupRoute(string $operation, string $expected_route)
	{
		$ap = new AjaxPage();
		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$ap->content_properties->read();
		$this->assertGreaterThan(0, count($ap->content_properties->routes));

		$ap->operation->value = $operation;
		$ap->lookupRoute();
		$this->assertEquals($operation, $ap->route->operation->value);
		$this->assertMatchesRegularExpression($expected_route, $ap->route->url->value);
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
        $ap = new AjaxPage();
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
        $ap = new AjaxPageTestHarness();
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
		$ap = new AjaxPage();
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
	 * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::sendTextResponseTestProvider()
	 * @param string $expected
	 * @param string $response
	 * @param string $override_response
	 * @return void
	 */
	function testSendTextResponse(string $expected, string $response, string $override_response='')
	{
		$ap = new AjaxPage();
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
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::setCacheClassTestProvider()
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
                AjaxPage::setCacheClass($cache_class);
                $this->assertEquals(false, true, "Expected exception \"$exception_class\" not thrown.");
            }
            catch(Exception $e) {
                $this->assertInstanceOf($exception_class, $e);
            }
        }
        else {
            AjaxPage::setCacheClass($cache_class);
            $this->assertEquals($cache_class, AjaxPage::getCacheClass(), $msg);
        }
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Ajax\AjaxPageTestDataProvider::setControllerClassTestProvider()
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
        AjaxPage::setControllerClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, AjaxPage::getControllerClass());
        }
    }

    function testSetDefaultTemplateName()
    {
        $this->assertEquals('', AjaxPage::getDefaultTemplateName());

        AjaxPage::setDefaultTemplateName('listings');
        $this->assertEquals('listings', AjaxPage::getDefaultTemplateName());

        // return to its original state
        AjaxPage::setDefaultTemplateName('');
    }
}