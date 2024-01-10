<?php
namespace API;

use Exception;
use Littled\API\APIListingsRoute;
use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentTemplate;
use LittledTests\API\APIRouteTestBase;
use LittledTests\TestHarness\API\APIListingsRouteTestHarness;
use LittledTests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;


class APIListingsRouteTest extends APIRouteTestBase
{
    function testConstructor()
    {
        $ap = new APIListingsRoute();
        $this->assertEquals('', $ap->operation->value);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    function testCollectFiltersRequestData()
    {
        $_POST = array(
            LittledGlobals::CONTENT_TYPE_KEY => TestTableSectionContentTestHarness::getContentTypeId(),
            'name' => 'foo',
            'dateAfter' => '2023-02-20'
        );

        $cf = new APIListingsRouteTestHarness();
        $cf->collectContentProperties();
        $cf->collectFiltersRequestData();

        /** @var TestTableContentFiltersTestHarness $filters */
        $filters = $cf->filters;
        $this->assertEquals(TestTableSectionContentTestHarness::getContentTypeId(), $cf->getContentTypeId());
        $this->assertEquals('foo', $filters->name_filter->value);
        $this->assertEquals('02/20/2023', $filters->date_after->value);

        // restore state
        $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\API\APIListingsRouteTestDataProvider::collectContentPropertiesTestProvider()
     * @param array $expected
     * @param string $expected_exception
     * @param array $post_data
     * @param string $ajax_stream
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    function testCollectContentProperties(
        array $expected,
        string $expected_exception='',
        array $post_data=[],
        string $ajax_stream='',
        string $msg='' )
    {
        $this->_testCollectContentProperties(new APIListingsRoute(), $expected, $expected_exception, $post_data, $ajax_stream, $msg);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\API\APIListingsRouteTestDataProvider::collectRequestDataTestProvider()
     * @param array $expected
     * @param string $expected_exception
     * @param array $get_data
     * @param array $post_data
     * @param string $ajax_stream
     * @param string $msg
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     */
    function testCollectRequestData(
        array $expected,
        string $expected_exception='',
        array $get_data=[],
        array $post_data=[],
        string $ajax_stream='',
        string $msg=''
    )
    {
        $_GET = $get_data;
        $_POST = $post_data;

        $ap = new APIListingsRouteTestHarness();
        $ajax_data = null;
        if ($ajax_stream) {
            AppBase::setAjaxInputStream($ajax_stream);
            $ajax_data = AppBase::getAjaxRequestData();
        }

        if ($expected_exception) {
            try {
                $ap->collectRequestData($ajax_data);
                $this->fail('Expected exception not thrown. $msg');
            }
            catch(Exception $e) {
                $this->assertInstanceOf($expected_exception, $e, $msg);
                static::restoreInputState();
            }
        }
        else {
            $ap->collectRequestData($ajax_data);

            if (count($expected) == 0) {
                $this->assertEquals(null, $ap->getContentTypeId(), $msg);
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
        }

        // restore state
        static::restoreInputState();
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
		parent::_testCollectPageAction(new APIListingsRoute(), $expected, $post_data, $ajax_stream, $custom_data, $msg);
	}

    /**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     */
	function testFetchContentTemplate()
	{
		$o = new APIListingsRouteTestHarness();
		$o->setContentTypeId(TestTableSerializedContentTestHarness::CONTENT_TYPE_ID);
		$o->fetchContentTemplate('listings');
		$this->assertEquals('listings.php', $o->template->path->value);
	}

    /**
     * @throws ConfigurationUndefinedException
     */
    function testFetchInvalidContentTemplate()
    {
        $o = new APIListingsRouteTestHarness();
        $o->setContentTypeId(TestTableSerializedContentTestHarness::CONTENT_TYPE_ID);
        try {
            $o->fetchContentTemplate('bogus-token');
            $this->fail('Expected exception not thrown.');
        }
        catch(Exception $e) {
            $this->assertInstanceOf(RecordNotFoundException::class, $e);
            $this->assertMatchesRegularExpression('/content template .*not found/i', $e->getMessage());
        }
    }

    /**
     * @return void
     */
	function testGetContentLabel()
	{
        $this->_testGetContentLabel(new APIListingsRoute());
	}

    function testGetContentTypeId()
    {
        $this->_testGetContentTypeId(new APIListingsRoute());
    }

    /**
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     */
    function testInitializeFiltersObject()
    {
        $r = new APIListingsRouteTestHarness();
        $r->initializeFiltersObject(APIRouteTestBase::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(APIRouteTestBase::TEST_CONTENT_TYPE_ID, $r->filters->content_properties->id->value);
        $this->assertEquals(APIRouteTestBase::TEST_CONTENT_TYPE_ID, $r->filters::getContentTypeId());
        $this->assertEquals(APIRouteTestBase::TEST_CONTENT_TYPE_ID, $r->getContentProperties()->getRecordId());
    }

	/**
     * @dataProvider \LittledTests\DataProvider\API\APIListingsRouteTestDataProvider::loadTemplateContentTestProvider()
     * @param string $expected
     * @param array $post_data
     * @param string $template_path
     * @param array|null $template_context
     * @param string $msg
     * @return void
     * @throws ResourceNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     */
	function testLoadTemplateContent(
        string  $expected,
        array   $post_data,
        string  $template_path,
        ?array  $template_context=null,
        string  $msg=''
    )
	{
        $_POST = $post_data;

		$ap = new APIListingsRoute();
        $ap->collectRequestData();

        if ($template_path) {
			$ap->template = new ContentTemplate();
			$ap->template->location->value = ContentTemplate::getLocalPathToken();
            $ap->template->path->value = $template_path;
        }
		$ap->loadTemplateContent($template_context);
		self::assertMatchesRegularExpression($expected, $ap->json->content->value, $msg);

        $_POST = [];
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
        $ap = new APIListingsRouteTestHarness();
        $ap->initializeFiltersObject(self::TEST_CONTENT_TYPE_ID);
        $this->_testLookupRoute($ap, $operation, $expected_route);
    }

    /**
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     */
    function testLookupTemplate()
    {
        $ap = new APIListingsRouteTestHarness();
        $ap->initializeFiltersObject(self::TEST_CONTENT_TYPE_ID);
        $this->_testLookupTemplate($ap);
    }
}