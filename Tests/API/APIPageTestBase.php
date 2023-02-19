<?php
namespace Littled\Tests\API;

use Littled\API\APIPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Tests\TestHarness\PageContent\Cache\ContentCacheTestHarness;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;


class APIPageTestBase extends TestCase
{
    /** @var int */
	public const        TEST_CONTENT_TYPE_ID = 6037; /* "Test Section" in `site_section` table from littledamien database */
    /** @var int */
    public const        TEST_TEMPLATE_CONTENT_TYPE_ID = 31;
    /** @var int */
    public const        TEST_RECORD_ID = 2023;
	/** @var string */
	public const        TEST_RECORD_NAME = 'fixed test record';
    public const        AJAX_INPUT_SOURCE = APP_BASE_DIR."Tests/DataProvider/API/APIPage_collectPageAction.dat";
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

    protected static function restoreInputState()
    {
        $_GET = $_POST = [];
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @param APIPage $ap
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
    function _testCollectContentProperties(
        APIPage $ap,
        array $expected,
        string $expected_exception='',
        array $post_data=[],
        string $ajax_stream='',
        string $msg='' )
    {
        // setup request data sources
        $_POST = $post_data;
        if ($ajax_stream) {
            Validation::setAjaxInputStream($ajax_stream);
        }

        if ($expected_exception) {
            $this->expectException($expected_exception);
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
	 * @param APIPage $o,
	 * @param string $expected
	 * @param array $post_data
	 * @param string $ajax_stream
	 * @param ?array $custom_data
	 * @param string $msg
	 * @return void
	 */
	protected function _testCollectPageAction(
		APIPage $o,
		string $expected,
		array $post_data=[],
		string $ajax_stream='',
		?array $custom_data=null,
		string $msg='')
	{
		$_POST = $post_data;
		if ($ajax_stream) {
			APIPage::setAjaxInputStream($ajax_stream);
		}

		$o->collectPageAction($custom_data);
		$this->assertEquals($expected, $o->action, $msg);

		// restore state
		$_POST = [];
		APIPage::setAjaxInputStream('php://input');
	}

    /**
     * @param APIPage $ap
     * @return void
     */
    function _testGetContentLabel(APIPage $ap)
    {
        $this->assertEquals('', $ap->getContentLabel());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals('test', $ap->getContentLabel());
    }

    function _testGetContentTypeId(APIPage $ap)
    {
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
    }

    /**
     * @param APIPage $ap
     * @param string $operation
     * @param string $expected_route
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    function _testLookupRoute(APIPage $ap, string $operation, string $expected_route)
    {
        $ap->getContentProperties()->read();
        $this->assertGreaterThan(0, count($ap->getContentProperties()->routes));

        $ap->operation->value = $operation;
        $ap->lookupRoute();
        $this->assertEquals($operation, $ap->route->operation->value);
        $this->assertMatchesRegularExpression($expected_route, $ap->route->api_route->value);
    }

    /**
     * @param APIPage $ap
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    function _testLookupTemplate(APIPage $ap)
    {
        $ap->getContentProperties()->read();
        $this->assertGreaterThan(0, count($ap->getContentProperties()->templates));

        $ap->operation->value = 'details';
        $ap->lookupTemplate();
        $this->assertEquals('details', $ap->template->name->value);

        $ap->lookupTemplate('delete');
        $this->assertEquals('delete', $ap->template->name->value);
    }
}