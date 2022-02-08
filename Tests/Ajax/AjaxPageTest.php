<?php
namespace Littled\Tests\Ajax;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Ajax\AjaxPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\SiteSection\SectionContent;
use PHPUnit\Framework\TestCase;

class AjaxPageTest extends TestCase
{
    /** @var int */
    public const TEST_CONTENT_TYPE_ID = 2;

    /**
     * @throws InvalidTypeException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        AjaxPage::setControllerClass('Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness');
        AjaxPage::setCacheClass('Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness');
    }

    function testGetContentTypeId()
    {
        $ap = new AjaxPage();
        $this->assertNull($ap->getContentTypeId());

        $ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->getContentTypeId());
        $this->assertEquals(self::TEST_CONTENT_TYPE_ID, $ap->content_properties->id->value);
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testGetContentObject()
    {
        $content = call_user_func_array([AjaxPage::getControllerClass(), 'getContentObject'], array(self::TEST_CONTENT_TYPE_ID));
        $this->assertInstanceOf(SectionContent::class, $content);
    }

    /**
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::setCacheClassTestProvider()
     * @param string $expected
     * @param string $class_name
     * @return void
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    function testSetCacheClass(string $expected, string $class_name)
    {
        if ($expected) {
            $this->expectException($expected);
        }
        AjaxPage::setCacheClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, AjaxPage::getCacheClass());
        }
    }

    /**
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::setControllerClassTestProvider()
     * @param string $expected
     * @param string $class_name
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     */
    function testSetControllerClass(string $expected, string $class_name)
    {
        if ($expected) {
            $this->expectException($expected);
        }
        AjaxPage::setControllerClass($class_name);
        if (!$expected) {
            $this->assertEquals($class_name, AjaxPage::getControllerClass());
        }
    }
}