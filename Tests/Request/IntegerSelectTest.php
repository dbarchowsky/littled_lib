<?php
namespace LittledTests\Request;

use Exception;
use Littled\Request\IntegerSelect;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Request\IntegerSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package LittledTests\Request
 */
class IntegerSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    function doesAllowMultiple()
    {
        $o = new IntegerSelect('Test Input', 'intKey');

        $o->setAllowMultiple();
        $this->assertTrue($o->allowMultiple());

        $o->setAllowMultiple(false);
        $this->assertFalse($o->allowMultiple());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::collectRequestDataTestProvider()
     * @param null|int|int[] $expected
     * @param string $method
     * @param bool $allow_multiple
     * @param array $input
     * @return void
     * @throws Exception
     */
    public function testCollectRequestData($expected, string $method, bool $allow_multiple, array $input)
    {
        // save state
        $post = $_POST;
        $get = $_GET;

        $src = null;
        switch($method) {
            case 'POST':
                $_POST = array_merge($_POST, $input);
                break;
            case 'GET':
                $_GET = array_merge($_GET, $input);
                break;
            case 'manual':
                $src = $input;
                break;
            default:
                throw new Exception('Bad source value.');
        }
        $o = (new IntegerSelect('Label', IntegerSelectTestData::TEST_KEY))
            ->setAllowMultiple($allow_multiple);
        $o->collectRequestData($src);
        if ($o->allowMultiple()) {
            self::assertEquals($expected, $o->value);
        }
        else {
            self::assertEqualsCanonicalizing($expected, $o->value);
        }

        // restore
        $_POST = $post;
        $_GET = $get;
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerInputTestDataProvider::hasDataTestProvider()
     * @param bool $expected
     * @param $value
     * @return void
     */
    public function testHasValue(bool $expected, $value)
    {
        if (!is_array($value)) {
            $o = new IntegerSelect('Label', 'key');
            $o->value = $value;
            self::assertEquals($expected, $o->hasData());
        }
        else {
            self::assertTrue(true);
        }
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::hasDataAsArrayTestProvider()
     * @param bool $expected
     * @param $value
     * @return void
     */
    public function testHasValueAsArray(bool $expected, $value)
    {
        $o = new IntegerSelect('Label','key');
        $o->value = $value;
        self::assertEquals($expected, $o->hasData());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::lookupValueInSelectedValuesTestProvider()
     * @param bool $expected
     * @param int|int[]|null $selected_values
     * @param bool $allow_multiple
     * @param ?int $value
     * @return void
     */
    function testLookupValueInSelectedValues(bool $expected, $selected_values, bool $allow_multiple, ?int $value)
    {
        $o = new IntegerSelect('Test Input', 'testInput');
        $o->allow_multiple = $allow_multiple;
        $o->value = $selected_values;
        $this->assertSame($expected, $o->lookupValueInSelectedValues($value));
    }

    /**
	 * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::renderTestProvider()
	 * @param IntegerSelectTestData $data
	 * @return void
	 */
	function testRender(IntegerSelectTestData $data)
	{
        $this->_testRender($data);
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Request\IntegerSelectTestDataProvider::renderUsingProcedureTestProvider()
     * @param IntegerSelectTestData $data
     * @return void
     */
    function testRenderUsingProcedure(IntegerSelectTestData $data)
    {
        $this->_testRender($data);
    }

    /**
     * @param IntegerSelectTestData $data
     * @return void
     */
    protected function _testRender(IntegerSelectTestData $data)
    {
        $this->expectOutputRegex($data->expected);
        $data->input->value = $data->selected;
        $data->input
            ->setOptions($data->options)
            ->render($data->override_label, $data->css_class);
    }
}