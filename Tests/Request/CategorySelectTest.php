<?php
namespace Littled\Tests\Request;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Keyword\Keyword;
use Littled\Request\CategorySelect;
use Littled\Request\RequestInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;
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

    function testGetCategoryTermOptions()
    {
        $options = CategorySelectTestHarness::retrieveCategoryOptions();
        $this->assertContains('tests', $options);        // << term shared by multiple parent records
        $this->assertContains('development', $options);  // << term in use by one parent record
        $this->assertContains('cooking', $options);      // << term in use by a different parent record
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
        $expected = LittledUtility::joinPaths(RequestInput::getTemplateBasePath(), CategorySelect::getContainerTemplatePath());
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

    function testSetContainerTemplateFilename()
    {
        $new_template = 'new-template.php';
        $original = CategorySelect::getContainerTemplateFilename();

        CategorySelect::setContainerTemplateFilename($new_template);
        $this->assertNotEquals($original, CategorySelect::getContainerTemplateFilename());
        $this->assertEquals($new_template, CategorySelect::getContainerTemplateFilename());

        CategorySelect::setContainerTemplateFilename($original);
    }
}