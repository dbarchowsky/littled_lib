<?php
namespace Littled\Tests\App;

use Littled\App\AppBase;
use Littled\Tests\TestHarness\App\AppBaseTestHarness;
use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;
use Exception;


class AppBaseTest extends TestCase
{
    protected const AJAX_INPUT_SOURCE = APP_BASE_DIR.'Tests/DataProvider/Validation/test-ajax-data.dat';

    /**
     * @return void
     * @throws Exception
     */
    public function testGenerateRequestId()
    {
        $id_1 = AppBase::generateUniqueToken(30);
        $this->assertEquals(30, strlen($id_1));

        $id_2 = AppBase::generateUniqueToken(29);
        $this->assertEquals(29, strlen($id_2));

        $id_3 = AppBase::generateUniqueToken(14);
        $this->assertEquals(14, strlen($id_3));

        $id_4 = AppBase::generateUniqueToken(30);
        $this->assertNotEquals($id_1, $id_4);
    }

    function testGetAjaxInputStream()
    {
        $this->assertEquals('php://input', AppBaseTestHarness::getAjaxInputStream());
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\App\AppBaseTestDataProvider::getAjaxRequestDataTestProvider()
     * @param array|null $expected
     * @param string $ajax_stream
     * @param array $post_data
     * @return void
     */
    function testGetAjaxRequestData(?array $expected, string $ajax_stream='', array $post_data=[])
    {
        if ($ajax_stream) {
            AppBase::setAjaxInputStream($ajax_stream);
        }
        $_POST = $post_data;

        $this->assertEquals($expected, AppBase::getAjaxRequestData());

        // restore state
        AppBase::setAjaxInputStream('php://input');
        $_POST = [];
    }

	function testGetErrorPageURL()
	{
		$this->assertMatchesRegularExpression('/^.*\/error\.php$/', AppBase::getErrorPageURL());
		$this->assertMatchesRegularExpression('/^\/subclass\/error\/route$/', AppBaseTestHarness::getErrorPageURL());
	}

	function testGetErrorKey()
	{
		$this->assertEquals('err', AppBase::getErrorKey());
		$this->assertEquals('subErr', AppBaseTestHarness::getErrorKey());
	}

	function testGetRequestDataWithGet()
    {
        $this->assertCount(0, $_GET, 'REQUEST data is empty');
        $this->assertCount(0, AppBase::getRequestData(), 'getRequestData() returns nothing');

        $_GET = ['getKey1' => 'value one', 'getKey2' => 'value two'];
        $this->assertArrayHasKey('getKey2', AppBase::getRequestData());

        $this->assertCount(0, AppBase::getRequestData($_POST), 'ignoring POST data');
        $_GET = [];
    }

    function testGetRequestDataWithJSON()
    {
        // confirm default conditions
        $this->assertCount(0, AppBase::getRequestData(), 'default data');

        AppBase::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
        $this->assertArrayHasKey('jsonKey', AppBase::getRequestData(), 'JSON request data');

        $_POST = ['firstKey' => 'first value', 'postKey' => 'post value', 'foo' => 'bar'];
        $this->assertArrayHasKey('postKey', AppBase::getRequestData(), 'POST data overrides JSON data');

        // cleanup
        AppBase::setAjaxInputStream('php://input');
        $_POST = [];
    }

    function testGetRequestDataWithPost()
    {
        $this->assertCount(0, $_POST, 'POST data is empty');
        $this->assertCount(0, AppBase::getRequestData(), 'getRequestData() returns nothing');

        $_POST = ['k1' => 'value one', 'k2' => 'value two'];
        $this->assertArrayHasKey('k2', AppBase::getRequestData());

        $this->assertCount(0, AppBase::getRequestData($_GET), 'ignoring GET data');
        $_POST = [];
    }

	/**
	 * @runInSeparateProcess
	 * @return void
	 */
	public function testRedirectToErrorPage()
	{
		AppBase::redirectToErrorPage('Test error message.');
		$headers = xdebug_get_headers();
		$this->assertMatchesRegularExpression('/^location:.*error\.php.*test\+error\+message/i', $headers[0]);

		AppBaseTestHarness::redirectToErrorPage('Test error message.');
		$headers = xdebug_get_headers();
		$this->assertMatchesRegularExpression('/^location:.*subclass\/error\/route.*test\+error\+message/i', $headers[0]);
	}

    public function testSetErrorKey()
    {
        $default_key = 'err';
        $new_key = 'new_test';
        $this->assertEquals($default_key, AppBase::getErrorKey());
        AppBase::setErrorKey($new_key);
        $this->assertEquals($new_key, AppBase::getErrorKey());
    }

    function testSetAjaxInputStream()
    {
        $stream = LittledUtility::joinPaths(APP_BASE_DIR, self::AJAX_INPUT_SOURCE);

        // default stream value
        $this->assertEquals('php://input', AppBaseTestHarness::getAjaxInputStream());

        // custom stream
        AppBaseTestHarness::setAjaxInputStream($stream);
        $this->assertEquals($stream, AppBaseTestHarness::getAjaxInputStream());
    }

    public function testSetErrorPageURL()
    {
        $default_url = '/error.php';
        $new_url = '/new-error.php';
        $this->assertEquals($default_url, AppBase::getErrorPageURL());
        AppBase::setErrorPageURL($new_url);
        $this->assertEquals($new_url, AppBase::getErrorPageURL());
    }
}