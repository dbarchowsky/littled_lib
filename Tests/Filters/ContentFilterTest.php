<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Filters\ContentFilter;
use Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider;
use PHPUnit\Framework\TestCase;
use mysqli;
use Exception;

class ContentFilterTest extends TestCase
{
    /** @property string */
    public const DEFAULT_LABEL = 'Test Filter';
    /** @property string */
    public const DEFAULT_KEY = 'p_filter';
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
        $o = new ContentFilter(self::DEFAULT_LABEL, self::DEFAULT_KEY, $data->value, 50);
        $this->assertEquals($data->expected, $o->escapeSQL($this->mysqli));
	}

    /**
     * @dataProvider \Littled\Tests\Filters\DataProvider\ContentFilterTestDataProvider::safeValueTestProvider()
     * @return void
     * @throws Exception
     */
    function testSafeValue(ContentFilterTestDataProvider $data)
    {
        $o = new ContentFilter(self::DEFAULT_LABEL, self::DEFAULT_KEY, $data->value, 50);
        $this->assertEquals($data->expected, $o->safeValue());
    }
}
