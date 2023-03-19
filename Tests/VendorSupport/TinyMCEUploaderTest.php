<?php
namespace Littled\Tests\VendorSupport;

use Littled\Exception\InvalidValueException;
use Littled\Tests\TestHarness\VendorSupport\TinyMCEUploaderTestHarness;
use PHPUnit\Framework\TestCase;


/**
 * To debug any methods in this class, first disable the "@runInSeparateProcess" option, then add "--stderr" to the
 * test's run options to prevent headers from being sent to STDOUT.
 */
class TinyMCEUploaderTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    function testCheckCrossOriginWhenNotSet()
    {
        $o = new TinyMCEUploaderTestHarness();
        $this->assertTrue($o->checkCrossOrigin());
    }

    /**
     * @runInSeparateProcess
     */
    function testCheckCrossOriginWithInvalidOrigin()
    {
        $o = new TinyMCEUploaderTestHarness();
        $_SERVER['HTTP_ORIGIN'] = 'https://www.someothersite.com';
        $this->assertFalse($o->checkCrossOrigin());
        $this->assertNotContains('Access-Control-Allow-Origin: https://www.someothersite.com', xdebug_get_headers());
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * @runInSeparateProcess
     */
    function testCheckCrossOriginWithValidOrigin()
    {
        $o = new TinyMCEUploaderTestHarness();
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost';
        $this->assertTrue($o->checkCrossOrigin());
        $this->assertContains('Access-Control-Allow-Origin: http://localhost', xdebug_get_headers());
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * @runInSeparateProcess
     */
    function testFilterOptionsRequestsWithMethodNotSet()
    {
        $this->assertFalse(TinyMCEUploaderTestHarness::filterOptionsRequests());
    }

    /**
     * @runInSeparateProcess
     */
    function testFilterOptionsRequestsWithInvalidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertFalse(TinyMCEUploaderTestHarness::filterOptionsRequests());
        $this->assertContains('Access-Control-Allow-Methods: POST, OPTIONS', xdebug_get_headers());
        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @runInSeparateProcess
     */
    function testFilterOptionsRequestsWithValidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(TinyMCEUploaderTestHarness::filterOptionsRequests());
        $this->assertNotContains('Access-Control-Allow-Methods: POST, OPTIONS', xdebug_get_headers());
        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\VendorSupport\TinyMCEUploaderTestDataProvider::formatTargetPathTestProvider()
     * @param string $expected
     * @param string $expected_exception
     * @param string $upload_path
     * @param string $image_base_path
     * @return void
     * @throws InvalidValueException
     */
    function testFormatTargetPath(
        string $expected,
        string $expected_exception,
        string $upload_path,
        string $image_base_path)
    {
        $o = new TinyMCEUploaderTestHarness();
        $o->setImageBasePath($image_base_path);
        if ($expected_exception) {
            $this->expectException($expected_exception);
        }
        $this->assertEquals($expected, $o->formatTargetPath($upload_path));
    }

    function testFormatUploadPath()
    {
        $o = new TinyMCEUploaderTestHarness();
        $o->setOrganizeByDate(false);
        $this->assertEquals('/var/www/html/images/', $o->formatUploadPath());

        $o->setUploadPath('/var/www/html/images/new_dir');
        $this->assertEquals('/var/www/html/images/new_dir/', $o->formatUploadPath());
    }

    function testFormatUploadPathWithDates()
    {
        $o = new TinyMCEUploaderTestHarness();
        $o->setOrganizeByDate(true);

        $year = date('Y');
        $month = date('m');
        $this->assertEquals("/var/www/html/images/$year/$month/", $o->formatUploadPath());
    }

    /**
     * @runInSeparateProcess
     */
    function testValidateRequestWithDefaultEnvironment()
    {
        $o = new TinyMCEUploaderTestHarness();
        $this->assertFalse($o->validateRequest());
        $this->assertContains('Access-Control-Allow-Methods: POST, OPTIONS', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    function testValidateRequestWithInvalidOrigin()
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://invalid.com';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $o = new TinyMCEUploaderTestHarness();
        $this->assertFalse($o->validateRequest());
        $this->assertNotContains('Access-Control-Allow-Origin: https://invalid.com', xdebug_get_headers());

        unset($_SERVER['HTTP_ORIGIN']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * runInSeparateProcess
     */
    function testValidateUploadName()
    {
        $this->assertTrue(TinyMCEUploaderTestHarness::validateUploadName('image.jpg'));
        $this->assertFalse(TinyMCEUploaderTestHarness::validateUploadName('image.psd'));
        $this->assertTrue(TinyMCEUploaderTestHarness::validateUploadName('my-image.webp'));
        $this->assertTrue(TinyMCEUploaderTestHarness::validateUploadName('my image.png'));
    }

    /**
     * @runInSeparateProcess
     */
    function testValidateRequestWithInvalidMethod()
    {
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost';
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $o = new TinyMCEUploaderTestHarness();
        $this->assertFalse($o->validateRequest());
        $this->assertContains('Access-Control-Allow-Methods: POST, OPTIONS', xdebug_get_headers());

        unset($_SERVER['HTTP_ORIGIN']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @runInSeparateProcess
     */
    function testValidateRequestWithValidRequest()
    {
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $o = new TinyMCEUploaderTestHarness();
        $this->assertTrue($o->validateRequest());
        $this->assertContains('Access-Control-Allow-Origin: http://localhost', xdebug_get_headers());
        $this->assertNotContains('Access-Control-Allow-Methods: POST, OPTIONS', xdebug_get_headers());

        unset($_SERVER['HTTP_ORIGIN']);
        unset($_SERVER['REQUEST_METHOD']);
    }
}