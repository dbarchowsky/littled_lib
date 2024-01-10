<?php /** @noinspection PhpUndefinedConstantInspection */

namespace LittledTests\Filters;

use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\ContentFilter;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Filters\ContentFilterTestDataProvider;
use LittledTests\TestHarness\Filters\ContentFilterTestHarness;
use PHPUnit\Framework\TestCase;
use mysqli;
use Exception;

class ContentFilterTest extends TestCase
{
    /** @var mysqli */
    protected mysqli $mysqli;

    /**
     * @throws Exception
     */
    function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception("Database connection not set.");
        }

        $this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::collectRequestValueTestProvider()
     * @param mixed $expected
     * @param string $key
     * @param array $get_data
     * @param array $post_data
     * @param array|null $override_data
     * @param string $msg
     * @return void
     */
    function testCollectRequestValue(
        $expected,
        string $key,
        array $get_data=[],
        array $post_data=[],
        ?array $override_data=null,
        string $msg=''
    )
    {
        $f = new ContentFilterTestHarness('Test Filter', $key);
        $_GET = $get_data;
        $_POST = $post_data;

        $f->publicCollectRequestValue($override_data);
        $this->assertEquals($expected, $f->value, $msg);

        // restore state
        $_GET = $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::collectValueTestProvider()
     * @param $expected
     * @param string $key
     * @param bool $read_cookies
     * @param array $get_data
     * @param array $post_data
     * @param array|null $override_data
     * @param string $msg
     * @return void
     */
    function testCollectValue(
        $expected,
        string $key,
        bool $read_cookies=true,
        array $get_data=[],
        array $post_data=[],
        ?array $override_data=null,
        string $msg=''
    )
    {
        $f = new ContentFilter('Test Filter', $key);
        $_GET = $get_data;
        $_POST = $post_data;

        $f->collectValue($read_cookies, $override_data);
        $this->assertEquals($expected, $f->value, $msg);

        // restore state
        $_GET = $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::escapeSQLTestProvider
     * @return void
     * @throws Exception
     */
    public function testEscapeSQL(ContentFilterTestDataProvider $data)
    {
        $o = new ContentFilter(ContentFilterTestDataProvider::DEFAULT_LABEL, ContentFilterTestDataProvider::DEFAULT_KEY, $data->value, 50);
        // re-assign to make sure we're working with the raw value
        $o->value = $data->value;
        $this->assertEquals($data->expected, $o->escapeSQL($this->mysqli));
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::formatQueryStringTestProvider
     * @param string $expected
     * @param $value
     * @param string $filter_class
     * @return void
     */
    function testFormatQueryString(string $expected, $value, string $filter_class='')
    {
        $filter_class = $filter_class ?: ContentFilter::class;
        $o = new $filter_class('Label', 'key');
        $o->value = $value;
        $this->assertEquals($expected, $o->formatQueryString());
    }

	/**
	 * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::saveInFormTestProvider
	 * @param ContentFilterTestDataProvider $data
	 * @return void
	 * @throws ResourceNotFoundException
	 */
	function testSaveInForm(ContentFilterTestDataProvider $data)
	{
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
		$o = new ContentFilter(ContentFilterTestDataProvider::DEFAULT_LABEL, ContentFilterTestDataProvider::DEFAULT_KEY, $data->value, 50);
		$o->value = $data->value;
		$this->expectOutputRegex($data->expected);
		$o->saveInForm();
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\ContentFilterTestDataProvider::safeValueTestProvider
     * @return void
     * @throws Exception
     */
    function testSafeValue(ContentFilterTestDataProvider $data)
    {
        $o = new ContentFilter(ContentFilterTestDataProvider::DEFAULT_LABEL, ContentFilterTestDataProvider::DEFAULT_KEY, $data->value, 50);
        $this->assertEquals($data->expected, $o->safeValue());
    }
}
