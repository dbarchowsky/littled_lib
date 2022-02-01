<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\ContentFilter;
use Littled\Request\RequestInput;
use Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider;
use PHPUnit\Framework\TestCase;
use mysqli;
use Exception;

class ContentFilterTest extends TestCase
{
    /** @var mysqli */
    protected $mysqli;

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
     * @dataProvider \Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider::escapeSQLTestProvider()
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
	 * @dataProvider \Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider::saveInFormTestProvider()
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
     * @dataProvider \Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider::safeValueTestProvider()
     * @return void
     * @throws Exception
     */
    function testSafeValue(ContentFilterTestDataProvider $data)
    {
        $o = new ContentFilter(ContentFilterTestDataProvider::DEFAULT_LABEL, ContentFilterTestDataProvider::DEFAULT_KEY, $data->value, 50);
        $this->assertEquals($data->expected, $o->safeValue());
    }
}
