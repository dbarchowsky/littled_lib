<?php
namespace API;

use Exception;
use Littled\API\APIListingsPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\API\APIPageTestBase;
use Littled\Tests\TestHarness\API\APIListingsPageTestHarness;
use Littled\Tests\TestHarness\API\APIPageTestHarness;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use Littled\Validation\Validation;


class APIListingsPageTest extends APIPageTestBase
{
    function testConstructor()
    {
        $ap = new APIListingsPage();
        $this->assertEquals('', $ap->operation->value);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIListingsPageTestDataProvider::collectContentPropertiesTestProvider()
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
        $this->_testCollectContentProperties(new APIListingsPage(), $expected, $expected_exception, $post_data, $ajax_stream, $msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\API\APIListingsPageTestDataProvider::collectRequestDataTestProvider()
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

        $ap = new APIListingsPageTestHarness();
        $ajax_data = null;
        if ($ajax_stream) {
            Validation::setAjaxInputStream($ajax_stream);
            $ajax_data = APIPageTestHarness::publicGetAjaxClientRequestData();
        }

        if ($expected_exception) {
            try {
                $ap->collectRequestData($ajax_data);
                $this->assertEquals(false, true, 'Expected exception not thrown. $msg');
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
            } else {
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
		parent::_testCollectPageAction(new APIListingsPage(), $expected, $post_data, $ajax_stream, $custom_data, $msg);
	}

    /**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     */
	function testFetchContentTemplate()
	{
		$o = new APIListingsPageTestHarness();
		$o->setContentTypeId(TestTableSerializedContentTestHarness::CONTENT_TYPE_ID);
		$o->fetchContentTemplate('listings');
		$this->assertEquals('listings.php', $o->template->path->value);
	}

    /**
     * @throws ConfigurationUndefinedException
     */
    function testFetchInvalidContentTemplate()
    {
        $o = new APIListingsPageTestHarness();
        $o->setContentTypeId(TestTableSerializedContentTestHarness::CONTENT_TYPE_ID);
        try {
            $o->fetchContentTemplate('bogus-token');
            $this->assertEquals(false, true, 'Expected exception not thrown.');
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
        $this->_testGetContentLabel(new APIListingsPage());
	}

    function testGetContentTypeId()
    {
        $this->_testGetContentTypeId(new APIListingsPage());
    }

	/**
     * @dataProvider \Littled\Tests\DataProvider\API\APIListingsPageTestDataProvider::loadTemplateContentTestProvider()
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

		$ap = new APIListingsPage();
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
        $ap = new APIListingsPageTestHarness();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $ap->initializeFiltersObject();
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
        $ap = new APIListingsPageTestHarness();
        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $ap->initializeFiltersObject();
        $this->_testLookupTemplate($ap);
    }
}