<?php

namespace Littled\Tests\DataProvider\Request;

use Littled\Request\BooleanSelect;

class BooleanSelectTestData
{
    public string           $expected;
    public BooleanSelect    $input;
    public array            $options;
    public string           $label_override;
    public string           $class_override;

    /**
     * @param string $expected
     * @param null|bool|string $value
     * @param array $options
     * @param string $label_override
     * @param string $class_override
     */
    function __construct(string $expected='', $value=null, array $options=[], string $label_override='', string $class_override='')
    {
        $this->expected = $expected;
        $this->input = new BooleanSelect(BooleanInputTestData::DEFAULT_LABEL, BooleanInputTestData::DEFAULT_KEY);
        if ($value!=='[use default]') {
            $this->input->setInputValue($value);
        }
        $this->options = $options;
        $this->class_override = $class_override;
        $this->label_override = $label_override;
    }
}