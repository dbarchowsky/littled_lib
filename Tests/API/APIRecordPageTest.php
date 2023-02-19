<?php
namespace Littled\Tests\API;

use Exception;
use Littled\API\APIPage;
use Littled\API\APIRecordPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\DataProvider\API\APIPageLoadTemplateContentTestData;
use Littled\Tests\TestHarness\API\APIPageTestHarness;
use Littled\Tests\TestHarness\API\APIRecordPageTestHarness;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableListingsPage;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;


class APIRecordPageTest extends APIPageTestBase
{
    function testConstructor()
    {
        $ap = new APIRecordPage();
        $this->assertEquals('', $ap->operation->value);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    function testCollectAndLoadJsonContent()
    {
	    $_POST = array(
		    LittledGlobals::CONTENT_TYPE_KEY => APIPageTestBase::TEST_CONTENT_TYPE_ID,
		    APIPage::TEMPLATE_TOKEN_KEY => 'delete',
            LittledGlobals::ID_KEY => APIPageTestBase::TEST_RECORD_ID
	    );

	    $ap = new APIRecordPage();
	    $ap->collectRequestData();

	    // inject record content into template
	    $ap->collectAndLoadJsonContent();
	    $this->assertMatchesRegularExpression('/^\s*<div class=\"dialog delete-confirmation\"/', $ap->json->content->value);

	    // restore state
	    $_POST = [];
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIRecordPageTestDataProvider::collectContentPropertiesTestProvider()
     * @param array $expected
     * @param string $expected_exception
     * @param array $post_data
     * @param string $ajax_stream
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws RecordNotFoundException|NotImplementedException
     */
    function testCollectContentProperties(
        array $expected,
        string $expected_exception='',
        array $post_data=[],
        string $ajax_stream='',
        string $msg='' )
    {
        $this->_testCollectContentProperties(new APIRecordPage(), $expected, $expected_exception, $post_data, $ajax_stream, $msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::collectPageActionTestProvider()
     * @param string $expected
     * @param array $post_data
     * @param string $ajax_stream
     * @param ?array $custom_data
     * @param string $msg
     * @return void
     */
    function testCollectPageAction(
		string $expected,
		array $post_data=[],
		string $ajax_stream='',
		?array $custom_data=null,
		string $msg='')
    {
		parent::_testCollectPageAction(new APIRecordPage(), $expected, $post_data, $ajax_stream, $custom_data, $msg);
    }

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 */
	function testFetchContentTemplate()
	{
		$post_data = array(
			LittledGlobals::CONTENT_TYPE_KEY => TestTableSerializedContentTestHarness::CONTENT_TYPE_ID,
			APIPage::TEMPLATE_TOKEN_KEY => 'edit');
		$_POST = $post_data;

		$o = new APIRecordPage();
		$o->collectRequestData();

		$this->assertMatchesRegularExpression('/.*edit-test-record\.php$/', $o->template->path->value);

		//restore state
		$_POST = [];
	}

    /**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	function testGetContentLabel()
	{
		$ap = new APIRecordPage();
		$this->assertEquals('', $ap->getContentLabel());

		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$this->assertEquals('', $ap->getContentLabel());

		$ap->getContentProperties()->read();
		$this->assertEquals('test', $ap->getContentLabel());
	}

    /**
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     */
    function testGetContentTypeId()
    {
        $ap = new APIRecordPage();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
    }

	/**
     * @dataProvider \Littled\Tests\DataProvider\API\APIPageTestDataProvider::loadTemplateContentTestProvider()
     * @param APIPageLoadTemplateContentTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws ResourceNotFoundException
     */
	function testLoadTemplateContent(APIPageLoadTemplateContentTestData $data)
	{
		$ap = new APIRecordPage();
		$ap->setContentTypeId($data->content_type_id);
		$ap->operation->setInputValue($data->operation);
		$ap->retrieveContentProperties();

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
	 * @param string $operation
	 * @param string $expected_route
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
    function testLookupRoute(string $operation, string $expected_route)
	{
        $ap = new APIRecordPage();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$this->_testLookupRoute($ap, $operation, $expected_route);
	}

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	function testLookupTemplate()
    {
        $ap = new APIRecordPage();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->_testLookupTemplate($ap);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     */
	function testRetrieveContentObjectAndData()
	{
        $_POST = array(
            LittledGlobals::CONTENT_TYPE_KEY => self::TEST_CONTENT_TYPE_ID,
            APIPage::TEMPLATE_TOKEN_KEY => 'edit',
            LittledGlobals::ID_KEY => self::TEST_RECORD_ID
        );

		$ap = new APIRecordPage();
		$ap->retrieveContentObjectAndData();
		/** @var TestTableSectionContentTestHarness $content */
		$content = $ap->content;
		$this->assertEquals(self::TEST_RECORD_NAME, $content->name->value);

        // restore state
        $_POST = [];
	}
}