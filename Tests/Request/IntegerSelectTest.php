<?php
namespace Littled\Tests\Request;

use Littled\Request\RequestInput;
use Littled\Tests\DataProvider\Request\IntegerSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package Littled\Tests\Request
 */
class IntegerSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    /**
	 * @dataProvider \Littled\Tests\DataProvider\Request\IntegerSelectTestDataProvider::renderTestProvider()
	 * @param IntegerSelectTestData $data
	 * @return void
	 */
	function testRender(IntegerSelectTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->input->render($data->override_label, $data->css_class, $data->options);
	}
}