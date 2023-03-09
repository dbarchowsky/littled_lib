<?php
namespace Littled\Tests\Request;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Keyword\Keyword;
use Littled\Request\CategorySelect;
use Littled\Request\RequestInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
use Littled\Tests\DataProvider\Request\StringSelect\ValidateTestData;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use Littled\Tests\TestHarness\Request\CategorySelectTestHarness;
use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;


class CategorySelectTest extends TestCase
{
    function testConstructor()
    {
        $o = new CategorySelect();
        $this->assertInstanceOf(StringSelect::class, $o->category_input);
        $this->assertInstanceOf(StringTextField::class, $o->new_category);
        $this->assertEquals('Category', $o->category_input->label);
        $this->assertTrue($o->category_input->allow_multiple);
        $this->assertEquals('New category', $o->new_category->label);
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testCollectRequestData()
    {
        $_POST = array(
            'catTerm' => array('a' => 'a', 'b' => 'b'),
            'catNew' => 'bash'
        );

        $o = new CategorySelectTestHarness();
        $o->collectRequestData();
        $this->assertEquals(array('a' => 'a', 'b' => 'b'), $o->category_input->value);
        $this->assertEquals('bash', $o->new_category->value);
        $this->assertContains('bash', $o->getCategoryTermList());

        // restore state
        $_POST = [];
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testGetCategoryTermOptions()
    {
        $options = CategorySelectTestHarness::retrieveCategoryOptions();
        $this->assertArrayHasKey('tests', $options);        // << term shared by multiple parent records
        $this->assertArrayHasKey('development', $options);  // << term in use by one parent record
        $this->assertArrayHasKey('cooking', $options);      // << term in use by a different parent record
        $this->assertEquals('tests', $options['tests']);
        $this->assertEquals('development', $options['development']);
        $this->assertEquals('cooking', $options['cooking']);
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testGetCategoryTermList()
    {
        $o = new CategorySelectTestHarness();
        $this->assertEquals([], $o->getCategoryTermList());

        $o->categories[] = new Keyword('a', $o->getParentId(), CategorySelectTestHarness::getContentTypeId());
        $o->categories[] = new Keyword('c', $o->getParentId(), CategorySelectTestHarness::getContentTypeId());
        $o->categories[] = new Keyword('g', $o->getParentId(), CategorySelectTestHarness::getContentTypeId());
        $o->categories[] = new Keyword('b', $o->getParentId(), CategorySelectTestHarness::getContentTypeId());
        $this->assertEquals(['a', 'c', 'g', 'b'], $o->getCategoryTermList());
    }

    function testGetContainerTemplatePath()
    {
        $expected = LittledUtility::joinPaths(RequestInput::getTemplateBasePath(), CategorySelect::getContainerTemplateFilename());
        $this->assertEquals($expected, CategorySelect::getContainerTemplatePath());
    }

    function testGetContentTypeId()
    {
        $o = new CategorySelect();
        $this->expectException(ConfigurationUndefinedException::class);
        $o->getContentTypeId();

        $th = new CategorySelectTestHarness();
        $this->assertEquals(TestTableSerializedContentTestHarness::CONTENT_TYPE_ID, $th->getContentTypeId());
    }

    function testHasValidParent()
    {
        $o = new CategorySelect();
        $this->assertFalse($o->hasValidParent());

        $th = new CategorySelectTestHarness();
        $this->assertTrue($th->hasValidParent());
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    function testRead()
    {
        $o = new CategorySelectTestHarness();
        $o->read();
        $this->assertGreaterThan(0, $o->categories);
        $this->assertContains('development', $o->getCategoryTermList());
    }

    function testSetContainerCSSClass()
    {
        $new_class = 'custom-class';
        $o = new CategorySelectTestHarness();
        $original = $o->category_input->getContainerCssClass();
        $return = $o->setContainerCSSClass($new_class);
        $this->assertNotEquals($original, $o->category_input->getContainerCssClass());
        $this->assertNotEquals($original, $o->new_category->getContainerCssClass());
        $this->assertEquals($return, $o);
    }

    function testSetContainerTemplateFilename()
    {
        $new_template = 'new-template.php';
        $original = CategorySelect::getContainerTemplateFilename();

        CategorySelect::setContainerTemplateFilename($new_template);
        $this->assertNotEquals($original, CategorySelect::getContainerTemplateFilename());
        $this->assertEquals($new_template, CategorySelect::getContainerTemplateFilename());

        CategorySelect::setContainerTemplateFilename($original);
    }

    function testSetListInputCSSClass()
    {
        $new_class = 'custom-input-class';
        $o = new CategorySelectTestHarness();
        $original = $o->category_input->getInputCssClass();
        $return = $o->setListInputCSSClass($new_class);
        $this->assertNotEquals($original, $o->category_input->getInputCssClass());
        $this->assertEquals($return, $o);
    }

    function testSetRequired()
    {
        $o = new CategorySelect();

        $o->setRequired();
        $this->assertTrue($o->category_input->required);

        $o->setRequired(false);
        $this->assertFalse($o->category_input->required);

        $o->setRequired(true);
        $this->assertTrue($o->category_input->required);
    }

    function testSetTextInputCSSClass()
    {
        $new_class = 'custom-input-class';
        $o = new CategorySelectTestHarness();
        $original = $o->category_input->getInputCssClass();
        $return = $o->setTextInputCSSClass($new_class);
        $this->assertNotEquals($original, $o->new_category->getInputCssClass());
        $this->assertEquals($return, $o);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\CategorySelectTestDataProvider::validateInputTestProvider()
     * @param ValidateTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     */
    function testValidateInput( ValidateTestData $data )
    {
        $_POST = $data->post_data;

        $o = new CategorySelectTestHarness();
        $o->category_input->required = $data->required;
        $o->allowMultiple($data->allow_multiple);
        $o->collectRequestData();

        try {
            $o->validateInput();
            if ($data->expected->exception) {
                $this->assertEquals(false, true, "Expected {$data->expected->exception} not thrown.");
            }
            $this->assertCount($data->expected->count, $o->category_input->value);
        }
        catch(ContentValidationException $e) {
            if ($data->expected->exception) {
                $this->assertInstanceOf($data->expected->exception, $e);
                $this->assertMatchesRegularExpression($data->expected->exception_msg, $e->getMessage());
            }
        }

        // restore state
        $_POST = [];
    }
}