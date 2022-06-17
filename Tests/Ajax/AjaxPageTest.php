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
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\Ajax\DataProvider\AjaxPageLoadTemplateContentTestData;
use Littled\Tests\PageContent\Serialized\TestHarness\TestTable;
use PHPUnit\Framework\TestCase;

class AjaxPageTest extends TestCase
{
    /** @var int */
	public const TEST_CONTENT_TYPE_ID = 6037; /* "Test Section" in littledamien database */
    /** @var int */
    public const TEST_TEMPLATE_CONTENT_TYPE_ID = 31;
    /** @var int */
    public const TEST_RECORD_ID = 2023;
	/** @var string */
	public const TEST_RECORD_NAME = 'fixed test record';
    protected const AJAX_INPUT_SOURCE = APP_BASE_DIR."Tests/Ajax/DataProvider/AjaxPage_collectPageAction.dat";

    /**
     * @throws InvalidTypeException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        LittledGlobals::setLocalTemplatesPath(TEST_TEMPLATES_PATH);
	    LittledGlobals::setSharedTemplatesPath(TEST_TEMPLATES_PATH);
        AjaxPage::setControllerClass('Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness');
        AjaxPage::setCacheClass('Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness');
    }

    function testConstructor()
    {
        $ap = new AjaxPage();
        $this->assertEquals('', $ap->operation->value);
    }

	/**
	 * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::collectContentPropertiesTestProvider()
	 * @param int|null $expected_content_id
	 * @param string $expected_template_token
	 * @param array $post_data
	 * @param string $msg
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 */
    function testCollectContentProperties(?int $expected_content_id, string $expected_template_token, array $post_data, string $msg='')
    {
        $_POST = $post_data;
        $ap = new AjaxPage();
        if (null===$expected_content_id) {
            $this->expectException(ContentValidationException::class);
        }
        $ap->collectContentProperties();
        $this->assertEquals($expected_content_id, $ap->getContentTypeId(), $msg);
        $this->assertEquals($expected_template_token, $ap->operation->value, $msg);
    }

	/**
	 * @throws RecordNotFoundException
	 * @throws ContentValidationException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
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

    function testGetContentTypeId()
    {
        $ap = new AjaxPage();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->content_properties->id->value);
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testGetContentObject()
    {
        $content = call_user_func_array([AjaxPage::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
        $this->assertInstanceOf(SerializedContent::class, $content);
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
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::loadTemplateContentTestProvider()
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
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    function testLookupRoute()
	{
		$ap = new AjaxPage();
		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$ap->content_properties->read();
		$this->assertGreaterThan(0, count($ap->content_properties->routes));

		$ap->operation->value = 'listings';
		$ap->lookupRoute();
		$this->assertEquals('listings', $ap->route->operation->value);
		$this->assertEquals('/vendor/dbarchowsky/littled_cms/ajax/utils/listings.php', $ap->route->url->value);

		$ap->lookupRoute('delete');
		$this->assertEquals('delete', $ap->route->operation->value);
		$this->assertEquals('/vendor/dbarchowsky/littled_cms/ajax/utils/delete-record.php', $ap->route->url->value);
	}

	/**
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
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
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
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
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::setCacheClassTestProvider()
     * @param string $expected
     * @param string $class_name
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     */
    function testSetCacheClass(string $expected, string $class_name, string $msg='')
    {
        if ($expected) {
            $this->expectException($expected);
        }
        AjaxPage::setCacheClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, AjaxPage::getCacheClass(), $msg);
        }
    }

    /**
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::collectPageActionTestProvider()
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
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::setControllerClassTestProvider()
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