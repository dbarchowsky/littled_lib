<?php

namespace Littled\Tests\Request\DataProvider;

use Littled\Request\BooleanSelect;

class BooleanSelectTestData
{
    /** @var string */
    public $expected;
    /** @var BooleanSelect */
    public $input;
    /** @var array */
    public $options;
    /** @var string */
    public $label_override;
    /** @var string */
    public $class_override;

    function __construct(string $expected='', ?bool $value=null, array $options=[], string $label_override='', string $class_override='')
    {
        $this->expected = $expected;
        $this->input = new BooleanSelect(BooleanInputTestData::DEFAULT_LABEL, BooleanInputTestData::DEFAULT_KEY);
        $this->input->setInputValue($value);
        $this->options = $options;
        $this->class_override = $class_override;
        $this->label_override = $label_override;
    }
}