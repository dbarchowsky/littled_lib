<?php
namespace LittledTests\API;

use Exception;
use Littled\API\APIRoute;
use Littled\API\APIRecordRoute;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentTemplate;
use LittledTests\DataProvider\API\APIRouteLoadTemplateContentTestData;
use LittledTests\TestHarness\API\APIRecordRouteTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;


class APIRecordRouteTest extends APIRouteTestBase
{
    function testConstructor()
    {
        $ap = new APIRecordRoute();
        $this->assertEquals('', $ap->operation->value);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\API\APIRecordRouteTestDataProvider::collectContentPropertiesTestProvider()
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
        $this->_testCollectContentProperties(new APIRecordRoute(), $expected, $expected_exception, $post_data, $ajax_stream, $msg);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\API\APIRouteTestDataProvider::collectPageActionTestProvider()
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
		parent::_testCollectPageAction(new APIRecordRoute(), $expected, $post_data, $ajax_stream, $custom_data, $msg);
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
			APIRoute::TEMPLATE_TOKEN_KEY => 'edit');
		$_POST = $post_data;

		$o = new APIRecordRoute();
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
		$ap = new APIRecordRoute();
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
        $ap = new APIRecordRoute();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
    }

	/**
     * @dataProvider \LittledTests\DataProvider\API\APIRouteTestDataProvider::loadTemplateContentTestProvider()
     * @param APIRouteLoadTemplateContentTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws ResourceNotFoundException
     */
	function testLoadTemplateContent(APIRouteLoadTemplateContentTestData $data)
	{
		$ap = new APIRecordRoute();
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
     * @dataProvider \LittledTests\DataProvider\API\APIRouteTestDataProvider::lookupRouteTestProvider()
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
        $ap = new APIRecordRoute();
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
        $ap = new APIRecordRoute();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->_testLookupTemplate($ap);
    }

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws Exception
	 */
	function testProcessRequest()
	{
		$_POST = array(
			LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
			APIRoute::TEMPLATE_TOKEN_KEY => 'delete',
			LittledGlobals::ID_KEY => APIRouteTestBase::TEST_RECORD_ID
		);

		$ap = new APIRecordRouteTestHarness();
		$ap->collectRequestData();

		// inject record content into template
		$ap->processRequest();
		$this->assertMatchesRegularExpression('/^\s*<div class=\"dialog delete-confirmation\"/', $ap->json->content->value);

		// restore state
		$_POST = [];
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
            APIRoute::TEMPLATE_TOKEN_KEY => 'edit',
            LittledGlobals::ID_KEY => self::TEST_RECORD_ID
        );

		$ap = new APIRecordRoute();
		$ap->retrieveContentObjectAndData();
		/** @var TestTableSectionContentTestHarness $content */
		$content = $ap->content;
		$this->assertEquals(self::TEST_RECORD_NAME, $content->name->value);

        // restore state
        $_POST = [];
	}
}