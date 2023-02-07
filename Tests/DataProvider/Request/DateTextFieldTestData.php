<?php
namespace Littled\Tests\DataProvider\Request;

use Littled\Request\DateTextField;

class DateTextFieldTestData
{
    const TEST_INPUT_LABEL = 'Date Text Field Test';
    const TEST_INPUT_KEY = 'dtfKey';

    public string $expected='';
    public string $msg='';
    public DateTextField $obj;
    public string $css_override='';
    public string $label_override='';

    public function __construct(
        string $expected='',
        string $msg='',
        string $value='',
        bool $required=false,
        ?int $index=null,
        string $input_css_class='',
        string $container_css_class='',
        string $css_override='',
        string $label_override=''
    )
    {
        $this->expected = $expected;
        $this->msg = $msg;
        $this->label_override = $label_override;
        $this->css_override = $css_override;
        $this->obj = new DateTextField(self::TEST_INPUT_LABEL, self::TEST_INPUT_KEY, $required, $value);
        $this->obj->index = $index;
        $this->obj->setContainerCSSClass($container_css_class);
        if ($input_css_class) {
            $this->obj->setInputCSSClass($input_css_class);
        }
    }
}