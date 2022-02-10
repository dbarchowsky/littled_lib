<?php
namespace Littled\Tests\Ajax;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Ajax\AjaxPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\SectionContent;
use PHPUnit\Framework\TestCase;

class AjaxPageTest extends TestCase
{
    /** @var int */
	public const TEST_CONTENT_TYPE_ID = 6037; /* "Test Section" in littledamien database */
    /** @var int */
    public const TEST_TEMPLATE_CONTENT_TYPE_ID = 31;

    /**
     * @throws InvalidTypeException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        AjaxPage::setControllerClass('Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness');
        AjaxPage::setCacheClass('Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness');
    }

    function testConstructor()
    {
        $ap = new AjaxPage();
        $this->assertEquals('', $ap->template_token->value);
    }

    /**
     * @dataProvider \Littled\Tests\Ajax\DataProvider\AjaxPageTestDataProvider::collectContentPropertiesTestProvider()
     * @param int|null $expected_content_id
     * @param string $expected_template_token
     * @param array $post_data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    function testCollectContentProperties(?int $expected_content_id, string $expected_template_token, array $post_data, string $msg='')
    {
        $_POST = $post_data;
        $ap = new AjaxPage();
        if (null===$expected_content_id) {
            $this->expectException(ContentValidationException::class);
        }
        $ap->collectContentProperties();
        $this->assertEquals($expected_content_id, $ap->getContentTypeId(), $msg);
        $this->assertEquals($expected_template_token, $ap->template_token->value, $msg);
    }

	/**
	 * @throws RecordNotFoundException
	 * @throws ContentValidationException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentLabel()
	{
		$ap = new AjaxPage();
		$this->assertEquals('', $ap->getContentLabel());

		$ap->setContentTypeId(self::TEST_CONTENT_TYPE_ID);
		$this->assertEquals('', $ap->getContentLabel());

		$ap->content_properties->read();
		$this->assertEquals('Test Section', $ap->getContentLabel());
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

    function testLookupTemplate()
    {
        $ap = new AjaxPage();
        $ap->setContentTypeId(self::TEST_TEMPLATE_CONTENT_TYPE_ID);
        $ap->content_properties->read();
        $this->assertGreaterThan(0, count($ap->content_properties->templates));

        $ap->template_token->value = 'details';
        $ap->lookupTemplate();
        $this->assertEquals('details', $ap->template->name->value);

        $ap->lookupTemplate('delete');
        $this->assertEquals('delete', $ap->template->name->value);
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

    function testSetDefaultTemplateName()
    {
        $this->assertEquals('', AjaxPage::getDefaultTemplateName());

        AjaxPage::setDefaultTemplateName('listings');
        $this->assertEquals('listings', AjaxPage::getDefaultTemplateName());

        // return to its original state
        AjaxPage::setDefaultTemplateName('');
    }
}